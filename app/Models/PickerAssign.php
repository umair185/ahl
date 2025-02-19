<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickerAssign extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'vendor_id',
        'picker_id',
        'status'
    ];

    public function vendor()
    {
    	return $this->hasOne(Vendor::class, 'id','vendor_id');
    }
}
