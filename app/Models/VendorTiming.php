<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTiming extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'vendor_id',  
        'timing_slot_id',  
        'status',
    ];
    
    public function vendorTiming() {
        return $this->hasOne(AhlTimings::class, 'id','timing_slot_id');
    }
}
