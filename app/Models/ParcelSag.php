<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelSag extends Model
{
    use HasFactory;

    protected $fillable = ['sag_number','sag_count','status','close_by','open_by','close_in','open_in','created_by','bilty_status','manual_seal_number'];

    public function fromCity()
    {
        return $this->hasOne(City::class, 'id','close_in');
    }

    public function toCity()
    {
        return $this->hasOne(City::class, 'id','open_in');
    }

    public function closeBy()
    {
        return $this->hasOne(User::class, 'id','close_by');
    }

    public function openBy()
    {
        return $this->hasOne(User::class, 'id','open_by');
    }

    public function orders()
    {
        return $this->hasMany(OrderInSag::class, 'sag_id','id');
    }
}
