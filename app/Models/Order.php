<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'vendor_id',
        'order_reference',
        'consignee_first_name',
        'consignee_last_name',
        'consignee_email',
        'consignee_address',
        'consignee_phone',
        'consignee_country',
        'consignee_state',
        'consignee_city',
        'consignment_order_id',
        'consignment_order_type',
        'consignment_cod_price',
        'consignment_weight',//remove when ahl weight id done every where
        'vendor_weight_id',
        'consignment_packaging',
        'consignment_pieces',
        'consignment_description',
        'pickup_location',
        'order_status',
        'consignment_origin_city',
        'additional_services_type',
        'delayed_status',
        'hold_status',
        'hold_reason',
        'vendor_weight_price',
        'update_by',
        'photo',
        'photo_upload_by',
        'parcel_nature',
        'previous_value',
        'reverse_remarks',
        'vendor_tax_price',
        'vendor_fuel_price',
        'sag_status',
        'sag_id',
        'parcel_limit',
        'parcel_attempts',
        'previous_order_value',
    ];
    
    public function orderType() {
        return $this->hasOne(OrderType::class, 'id','consignment_order_type');
    }

    public function vendor() {
        return $this->hasOne(Vendor::class, 'id','vendor_id');
    }
    
    public function orderStatus() {
        return $this->hasOne(Status::class, 'id', 'order_status');
    }
    
    public function orderPacking() {
        return $this->hasOne(Packing::class, 'id','consignment_packaging');
    }
    
    public function customerCity() {
        return $this->hasOne(City::class, 'id','consignee_city');
    }

    public function originCity() {
        return $this->hasOne(City::class, 'id','consignment_origin_city');
    }

    public function customerCountry() {
        return $this->hasOne(Country::class, 'id','consignee_country');
    }

    public function customerState() {
        return $this->hasOne(State::class, 'id','consignee_state');
    }

    public function scanOrder() {
        return $this->hasOne(ScanOrder::class, 'order_id','id');
    }

    public function vendorWeight() {
        return $this->hasOne(VendorWeight::class, 'id','vendor_weight_id');
    }

    public function orderAssigned() {
         return $this->hasOne(OrderAssigned::class, 'order_id','id')->where('status', 1);
    }
    
    public function reattemptOrderAssigned() {
        return $this->hasOne(OrderAssigned::class, 'order_id','id')->where('status', 0);
    }

    public function getFullNameAttribute() {
        return $this->consignee_first_name.' '.$this->consignee_last_name;
        //return "{$this->consignee_first_name} {$this->consignee_last_name}";
    }

    public function shiperAdviser() {
        return $this->hasOne(ShiperAdviser::class, 'order_id','id');
    }
    
    public function pickupLocation() {
        return $this->hasOne(WarehouseLocation::class, 'id','pickup_location');
    }

    public function orderStaff(){
        return $this->hasOne(User::class,'id','photo_upload_by');
    }

    public function orderUpdateStaff(){
        return $this->hasOne(User::class,'id','update_by');
    }

    public function countOrderAssigned() {
        return $this->hasMany(OrderAssigned::class, 'order_id','id');
    }

    public function parcelNature() {
        return $this->hasOne(ParcelNature::class, 'id','parcel_nature');
    }

    public function sagParcel() {
        return $this->hasOne(ParcelSag::class, 'id','sag_id');
    }

    public function orderReAssigned() {
        return $this->hasMany(OrderAssigned::class, 'order_id','id');
    }
    
    
}
