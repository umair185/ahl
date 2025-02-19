<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id','complain','action','status','action_by'];

    public function userDetail()
    {
        return $this->hasOne(User::class, 'id', 'action_by');
    }

    public function vendorDetail()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }
}
