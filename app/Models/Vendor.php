<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'vendor_name',
        'vendor_email',
        'vendor_phone',
        'addational_kgs',
        'vendor_address',
        'latitude',
        'longitude',
        'focal_person_name',
        'focal_person_phone',
        'focal_person_address',
        'focal_person_email',
        'cnic',
        'ntn',
        'strn',
        'website',
        'bank_name',
        'bank_title',
        'bank_account',
        'logo',
        'country_id',
        'city_id',
        'state_id',
        'region_id',
        'gst',
        'status',
        'commision',
        'fuel',
        'vendor_image',
        'printing_slips',
        'advance',
        'vendor_token',
        'vendor_number',
        'remarks',
        'payment_mode',
        'ntn_buyer',
        'ntn_city',
        'complain_number',
        'created_by',
        'poc',
        'datentime',
        'poc_assigned_by',
        'csr',
        'csr_datentime',
        'csr_assigned_by',
        'pickup',
        'pickup_datentime',
        'pickup_assigned_by',
        'category',
        'payment',
    ];

    public function vendorCountry() {
        return $this->hasOne(Country::class, 'id','country_id');
    }

    public function vendorState() {
        return $this->hasOne(State::class, 'id','state_id');
    }

    public function vendorCity() {
        return $this->hasOne(City::class, 'id','city_id');
    }

    public function vendorLoginDetail() {
        return $this->hasOne(User::class, 'vendor_id','id');
    }

    public function pickupLocation() {
        return $this->hasMany(WarehouseLocation::class, 'vendor_id','id');
    }

    public function vendorWeights() {
        return $this->hasMany(VendorWeight::class, 'vendor_id','id');
    }

    public function timings() {
        return $this->hasMany(VendorTiming::class, 'vendor_id','id');
    }

    public function vendorOrders() {
        return $this->hasMany(Order::class, 'vendor_id','id');
    }

    public function vendorFinancials() {
        return $this->hasMany(VendorFinancial::class, 'vendor_id','id');
    }
    
    public function awaitingParcel() {
        return $this->hasMany(Order::class, 'vendor_id','id')->where('order_status', 1)->where('parcel_nature',1);
    }

    public function awaitingTodayParcel() {
        return $this->hasMany(Order::class, 'vendor_id','id')->where('order_status', 1)->where('parcel_nature',1);
    }

    public function freshOrders() {
        return $this->hasMany(Order::class, 'vendor_id','id')->where('parcel_nature',1);
    }

    public function pickOrders() {
        return $this->hasMany(Order::class, 'vendor_id','id')->where('parcel_nature',1);
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id','created_by');
    }

    public function pocPerson() {
        return $this->hasOne(User::class, 'id','poc');
    }

    public function pocAssignedBy() {
        return $this->hasOne(User::class, 'id','poc_assigned_by');
    }

    public function csrPerson() {
        return $this->hasOne(User::class, 'id','csr');
    }

    public function csrAssignedBy() {
        return $this->hasOne(User::class, 'id','csr_assigned_by');
    }

    public function pickupPerson() {
        return $this->hasOne(User::class, 'id','pickup');
    }

    public function pickupAssignedBy() {
        return $this->hasOne(User::class, 'id','pickup_assigned_by');
    }
}
