<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderCashCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id',
        'cashier_id',
        'amount',
        'remaining_amount',
        'in_cash_collection',
        'ibft_collection',
        'ibft_comment',
        'note',
        'created_at',
        'updated_at',
    ];

    public function rider()
    {
        return $this->hasOne(User::class, 'id','rider_id');
    }

    public function cashier()
    {
        return $this->hasOne(User::class, 'id','cashier_id');
    }
}
