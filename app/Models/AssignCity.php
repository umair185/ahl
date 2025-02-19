<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'state_id',
        'city_id',
        'assign_by',
        'user_detail_id',
    ];

    public function userCountry()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function userState()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function userCity()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }
}
