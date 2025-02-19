<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiperAdviser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'advise',
        'status',
        'ahl_reply',
    ];

    public function Order() {
        return $this->hasOne(Order::class, 'id','order_id');
    }
}
