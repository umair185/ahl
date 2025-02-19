<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffFinancial extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'cashier_id',
        'amount',
        'note',
    ];

    public function cashierName()
    {
    	return $this->hasOne(User::class,'id','cashier_id');
    }

    public function staffName()
    {
        return $this->hasOne(User::class,'id','staff_id');
    }
}
