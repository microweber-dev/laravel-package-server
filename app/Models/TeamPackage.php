<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPackage extends Model
{
    use HasFactory;

    public function package()
    {
        return $this->hasOne(Package::class, 'id', 'package_id');
    }

    public function team()
    {
        return $this->hasOne(Team::class, 'id', 'team_id');
    }
}
