<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $filllable = [
        'name',
        'code',
        'state_id',
    ];

    public function state()
    {
    	return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function areas(){
        return $this->hasMany(SubArea::class,'city_id','id');
    }

    public function weightCity(){
        return $this->hasMany(VendorWeight::class,'city_id','id');
    }

}
