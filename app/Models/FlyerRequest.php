<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlyerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',  
        'status',
        'total',
    ];

    public function flyerDetail() {
        return $this->hasMany(FlyerDetails::class, 'request_id','id');
    }

    public function vendor() {
        return $this->hasOne(Vendor::class, 'id','vendor_id');
    }
}
