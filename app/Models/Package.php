<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $attributes = [];

    public const CLONE_STATUS_WAITING = 'waiting';
    public const CLONE_STATUS_RUNNING = 'running';
    public const CLONE_STATUS_SUCCESS = 'success';
    public const CLONE_STATUS_FAILED = 'failed';

     /**
     * Get all of the teams the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Jetstream::teamModel(), Jetstream::membershipModel())
                        ->withTimestamps()
                        ->as('membership');
    }
}
