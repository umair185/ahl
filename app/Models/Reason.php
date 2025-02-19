<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;

    public function subReason()
    {
    	return $this->hasMany(SubReason::class,'reason_id','id');
    }
}
