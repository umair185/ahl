<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pickup_request_id',
        'picker_id',
        'middle_man_id',
        'supervisor_id',
        'middle_man_scan_date',
        'supervisor_scan_date',
    ];

    public function orderDetail()
    {
    	return $this->hasOne(Order::class, 'id','order_id');
    }

    public function scanByPicker()
    {
        return $this->hasOne(User::class, 'id','picker_id');
    }

    public function scanByMiddleMan()
    {
        return $this->hasOne(User::class, 'id','middle_man_id');
    }

    public function scanBySupervisor()
    {
        return $this->hasOne(User::class, 'id','supervisor_id');
    }
}
