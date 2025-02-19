<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name'
    ];
    
    public function orderStatuses()
    {
        return $this->hasMany(Order::class, 'order_status','id');
    }
}
