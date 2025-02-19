<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhlWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'weight',
    ];

    public function weightCity(){
        return $this->hasOne(VendorWeight::class,'ahl_weight_id','id');
    }
}
