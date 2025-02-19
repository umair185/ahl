<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAssigned extends Model
{
    use HasFactory;

    protected $casts = [
        'cancel_reason' => 'json'
    ];
    
    protected $fillable = [
        'vendor_id',
        'order_id',
        'rider_id',
        'drop_off_location',
        'latitude',
        'longitude',
        'trip_status_id',
        'status',
        'reattempt_by',
        'force_status',
        'remarks',
        'remarks_by',
        'remarks_status',
        'cdrid',
        'call_response',
        'call_input',
        'ivr_value',
    ];

    public function riderVendor() {
        return $this->hasOne(Vendor::class, 'id','vendor_id');
    }

    public function order() {
        return $this->hasOne(Order::class, 'id','order_id');
    }

    public function rider()
    {
        return $this->hasOne(User::class, 'id','rider_id');
    }

    public function tripStatus()
    {
        return $this->hasOne(TripStatus::class, 'id','trip_status_id');
    }

    public function orderDelivery()
    {
        return $this->hasOne(OrderDelivered::class, 'order_assigned_id','id');
    }

    public function orderDecline()
    {
        return $this->hasOne(OrderDecline::class, 'order_assigned_id','id');
    }

    public function scanOrder()
    {
        return $this->hasOne(ScanOrder::class, 'order_id', 'order_id');
    }

    public function orderDetail()
    {
        return $this->hasOne(Order::class, 'id','order_id');
    }
    
    public function supervisorName()
    {
        return $this->hasOne(User::class, 'id','reattempt_by');
    }

    public function remarksBy()
    {
        return $this->hasOne(User::class, 'id','remarks_by');
    }
}
