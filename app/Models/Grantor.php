<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grantor_relation',
        'grantor_name',
        'grantor_father_name',
        'grantor_cnic',
        'grantor_age',
        'grantor_address',
        'grantor_pin_location',
        'grantor_house',
        'grantor_job',
        'grantor_income',
        'grantor_phone',
        'grantor_relation_two',
        'grantor_name_two',
        'grantor_father_name_two',
        'grantor_cnic_two',
        'grantor_age_two',
        'grantor_address_two',
        'grantor_pin_location_two',
        'grantor_house_two',
        'grantor_job_two',
        'grantor_income_two',
        'grantor_phone_two',
        'grantor_image_one',
        'grantor_image_two',
    ];

}
