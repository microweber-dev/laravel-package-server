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
        'position',
        'is_paid',
        'is_visible',
        'whmcs_product_ids',
        'package_access_preset_id',
        'buy_url',
        'buy_url_from',
    ];

    public $casts = [
        'position'=>'int',
        'is_paid'=>'int',
        'is_visible'=>'int',
        'package_access_preset_id'=>'int',
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

    public function packageAccessPreset()
    {
        return $this->hasOne(PackageAccessPreset::class, 'id', 'package_access_preset_id');
    }

    public function getWhmcsProductIds()
    {
        if (is_numeric($this->package_access_preset_id) && $this->package_access_preset_id > 0) {

            $getPreset = PackageAccessPreset::where('id',$this->package_access_preset_id)->first();
            if ($getPreset !== null) {
                if(isset($getPreset->settings['whmcs_product_ids'])) {
                   return $getPreset->settings['whmcs_product_ids'];
                }
            }
        }

        return $this->whmcs_product_ids;
    }
}
