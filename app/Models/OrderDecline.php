<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDecline extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_assigned_id',
        'order_decline_status_id',
        'order_decline_reason_id',
        'additional_note',
        'image',
    ];

    public function orderDeclineStatus()
    {
        return $this->hasOne(OrderDeclineStatus::class, 'id','order_decline_status_id');
    }

    public function orderDeclineReason()
    {
        return $this->hasOne(OrderDeclineReason::class, 'id','order_decline_reason_id');
    }
}
