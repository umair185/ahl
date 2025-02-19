<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorFinancial extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'cashier_id',
        'amount',
        'ahl_commission',
        'ahl_gst',
        'date_from',
        'date_to',
        'remarks',
        'flyer_amount',
        'deduction_amount',
        'fuel_adjustment',
        'financial_report',
        'created_at',
        'updated_at',
        'financial_payment',
        'invoice_number',
        'invoice_type',
        'paid_number',
        'advance_amount',
        'deduction_remarks',
    ];

    public function cashierName()
    {
    	return $this->hasOne(User::class,'id','cashier_id');
    }

    public function vendorName()
    {
        return $this->hasOne(Vendor::class,'id','vendor_id');
    }
}
