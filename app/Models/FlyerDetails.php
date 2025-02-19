<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlyerDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',  
        'flyer_id',
        'quantity',
        'flyer_price',
        'flyer_total',
    ];

    public function flyerName() {
        return $this->hasOne(Flyer::class, 'id','flyer_id');
    }
}
