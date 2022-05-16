<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageAccessPreset extends Model
{
    use HasFactory;

    public $casts = [
        'settings'=>'array'
    ];

    public function owner()
    {
        return $this->hasOne(User::class);
    }

    public function team()
    {
        return $this->hasOne(Team::class);
    }
}
