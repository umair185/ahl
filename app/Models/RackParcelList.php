<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackParcelList extends Model
{
    use HasFactory;

    protected $fillable = ['date_from','date_to','order_id','status','scan_by'];

    public function orderDetail()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function userDetail()
    {
        return $this->belongsTo(User::class, 'scan_by', 'id');
    }
}
