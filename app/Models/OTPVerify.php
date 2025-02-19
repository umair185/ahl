<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTPVerify extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','code','status','used_status'];
}
