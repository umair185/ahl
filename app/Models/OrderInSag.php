<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderInSag extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','sag_id','from','to','status'];

    public function fromCity()
    {
        return $this->hasOne(City::class, 'id','from');
    }

    public function toCity()
    {
        return $this->hasOne(City::class, 'id','to');
    }

    public function orderDetail()
    {
        return $this->hasOne(Order::class, 'id','order_id');
    }
}
