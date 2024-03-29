<?php

namespace App\Models;

use App\Helpers\Base;
use App\Helpers\GithubHelper;
use Carbon\Carbon;
use App\Jobs\ProcessPackageSatis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;

class Package extends Model
{
    use HasFactory;

    public const CLONE_STATUS_WAITING = 'waiting';
    public const CLONE_STATUS_QUEUED = 'queued';
    public const CLONE_STATUS_RUNNING = 'running';
    public const CLONE_STATUS_CLONING = 'cloning';
    public const CLONE_STATUS_SUCCESS = 'success';
    public const CLONE_STATUS_FAILED = 'failed';

    public function getLatestDistUrl()
    {
        $json = json_decode($this->package_json, true);

        if (is_array($json)) {
            $json = end($json);
            $json = end($json);
            if (isset($json['dist']['url'])) {
                return $json['dist']['url'];
            }
        }

        return false;
    }

    public function scopeUserHasAccess($query)
    {
        $user = auth()->user();

        return $query->where(function($query) use ($user) {
            $query->whereIn('team_owner_id', $user->getTeamIdsWhereIsAdmin());
            $query->orWhere('user_id', $user->id);
        });

    }

    public function downloadStats()
    {
        return $this->hasMany(PackageDownloadStats::class);
    }

    public function screenshot()
    {
        return str_replace('https://example.com/', config('app.url'), $this->screenshot);
    }

    public function readme()
    {
        try {
            $readmeLink = str_replace('https://example.com/', config('app.url'), $this->readme);
            if (!empty($readmeLink)) {
                // Fix readme img host links
                $content = @file_get_contents($readmeLink);
                $content = str_ireplace('https://example.com/', config('app.url'), $content);

                return $content;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function displayName()
    {
        if (!empty($this->description)) {
            return $this->description;
        }

        if (!empty($this->name)) {
            return $this->name;
        }

        return $this->repository_url;
    }

    public function teams()
    {
        return $this->belongsToMany(Jetstream::teamModel(), 'team_packages')->withTimestamps();
    }

    public function owner()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function credential()
    {
        return $this->hasOne(Credential::class,'id','credential_id');
    }

    public function teamIdsAsArray() {

        $ids = [];
        $findTeams = $this->teams()->get();
        if ($findTeams !== null) {
            $ids = $findTeams->pluck('id')->toArray();
        }

        return $ids;
    }

    public function updatePackageWithSatis($forceUpdate = false)
    {
        if (!$forceUpdate) {
            if ((
                    $this->clone_status == self::CLONE_STATUS_RUNNING)
                || ($this->clone_status == self::CLONE_STATUS_WAITING)
                || ($this->clone_status == self::CLONE_STATUS_QUEUED)
                || ($this->clone_status == self::CLONE_STATUS_CLONING)
            ) {
                // Already waiting
                return ['dispatched' => false, 'id' => $this->id];
            }
        }

        $this->clone_status = self::CLONE_STATUS_WAITING;
        $this->save();

        return ['dispatched'=>true,'id'=>$this->id];
    }
}
