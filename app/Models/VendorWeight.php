<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',  
        'ahl_weight_id',  
        'price',  
        'status',
        'min_weight',
        'max_weight',
        'city_id',
    ];

    public function ahlWeight() {
        return $this->hasOne(AhlWeight::class, 'id','ahl_weight_id');
    }

    public function city(){
        return $this->hasMany(City::class,'id','city_id');
    }
}
