<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bilty extends Model
{
    use HasFactory;

    protected $fillable = ['bilty_number','manual_bilty_number','from','to','created_by','create_in','open_in','open_by','status'];

    public function From()
    {
        return $this->hasOne(City::class, 'id','from');
    }

    public function To()
    {
        return $this->hasOne(City::class, 'id','to');
    }

    public function fromCity()
    {
        return $this->hasOne(City::class, 'id','create_in');
    }

    public function toCity()
    {
        return $this->hasOne(City::class, 'id','open_in');
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id','created_by');
    }

    public function openBy()
    {
        return $this->hasOne(User::class, 'id','open_by');
    }

    public function sags()
    {
        return $this->hasMany(SagInBilty::class, 'bilty_id','id');
    }
}
