<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPackage extends Model
{
    use HasFactory;

    public const PACKAGE_PAID = 1;
    public const PACKAGE_FREE = 0;

    protected $attributes = [
        'is_paid' => self::PACKAGE_FREE,
    ];

    public $fillable = [
        'is_paid',
        'is_visible',
        'whmcs_product_ids',
    ];

    public $casts = [
        'is_paid'=>'int',
        'is_visible'=>'int',
        'whmcs_product_ids'=>'array'
    ];

    public function package()
    {
        return $this->hasOne(Package::class, 'id', 'package_id');
    }

    public function team()
    {
        return $this->hasOne(Team::class, 'id', 'team_id');
    }
}
