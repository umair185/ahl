<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SubArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_name',
        'city_id',
    ];


    public function city(){
        return $this->hasOne(City::class,'id','city_id');
    }
}
