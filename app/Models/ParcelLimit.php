<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelLimit extends Model
{
    use HasFactory;

    protected $fillable = ['city_id','limit','last_update_on','created_by'];

    public function cityName()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function userName()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
