<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'city_id',
    ];

    public function cityDetail()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }
}
