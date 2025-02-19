<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'vendor_id',
        'city_id',
        'vendor_time_id',
        'warehouse_location_id',
        'pickup_date',
        'estimated_parcel',
        'status',
        'remarks'
    ];
    
    public function pickupTiming() {
        return $this->hasOne(AhlTimings::class, 'id','vendor_time_id');
    }

    public function requestTiming() {
        return $this->hasOne(VendorTiming::class, 'id','vendor_time_id');
    }
    
    public function pickupLocation() {
        return $this->hasOne(WarehouseLocation::class, 'id', 'warehouse_location_id');
    }
    
    public function vendorName() {
        return $this->hasOne(Vendor::class, 'id','vendor_id');
    }

    public function assignRequest() {
        return $this->hasOne(AssignRequest::class, 'pickup_request_id','id');
    }

    public function scanParcel()
    {
        return $this->hasMany(ScanOrder::class, 'pickup_request_id', 'id');
    }
    
}
