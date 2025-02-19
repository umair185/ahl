<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //'country_id',
        //'state_id',
        //'city_id',
        'user_id',
        'created_by',
        'cnic',
        'vehicle',
        'salary',
        'commission',
        'phone',
        'address',
        //new data added
        'father_name',
        'father_cnic',
        'father_phone',
        'marital_status',
        'permanent_staff_address',
        'pin_location',
        'bike_number',
        'siblings',
        'payment_cheque',
        'house_image',
        //new data
        'account_number',
        'account_title',
        'bank_name',
        'reporting_to',
        'location',
        'hiring_by',
        'interviewed_by',
        'hiring_platform',
        'joining_date',
        'leaving_date',
        'company_assets',
        'remarks',
        'dob',
        'cnic_front',
        'cnic_back',
        'house_status',
        'live_from',
        'emergency_name',
        'emergency_phone',
        'emergency_relation',
        'emergency_picture',
    ];

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'createdBy');
    }

    /*public function userCountry()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }*/

    /*public function userState()
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }*/

    /*public function userCity()
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }*/

    public function user()
    {
        return $this->hasOne(User::class, 'user_detail_id', 'id');
    }

    public function assignCity()
    {
        return $this->hasMany(AssignCity::class, 'user_detail_id', 'id');
    }

    
}
