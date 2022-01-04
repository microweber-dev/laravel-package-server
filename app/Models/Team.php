<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Team as JetstreamTeam;
use Glorand\Model\Settings\Traits\HasSettingsTable;

class Team extends JetstreamTeam
{
    use HasFactory;
    use HasSettingsTable;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'slug',
        'is_private',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->token = md5(uniqid());
        });

        self::updating(function ($model) {
            if (empty($model->token)) {
                $model->token = md5(uniqid());
            }
        });
    }


    public function packages()
    {
        return $this->belongsToMany(Package::class, 'team_packages')->withTimestamps();
    }
}
