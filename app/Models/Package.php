<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Jetstream;

class Package extends Model
{
    use HasFactory;

    public const CLONE_STATUS_WAITING = 'waiting';
    public const CLONE_STATUS_RUNNING = 'running';
    public const CLONE_STATUS_SUCCESS = 'success';
    public const CLONE_STATUS_FAILED = 'failed';

    public function screenshot()
    {
        return str_replace('https://example.com/', config('app.url'), $this->screenshot);
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
}
