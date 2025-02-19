<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SagInBilty extends Model
{
    use HasFactory;

    protected $fillable = ['bilty_id','sag_id','status'];

    public function SagDetail()
    {
        return $this->hasOne(ParcelSag::class, 'id','sag_id');
    }
}
