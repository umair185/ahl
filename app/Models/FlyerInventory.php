<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlyerInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'flyer_id',
        'qty',
        'remarks',
    ];

    public function flyerName() {
        return $this->hasOne(Flyer::class, 'id','flyer_id');
    }
}
