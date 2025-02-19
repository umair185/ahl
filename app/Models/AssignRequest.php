<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_request_id',
        'picker_id',
        'status',
    ];

    public function pickerRequest() {
        return $this->hasOne(PickupRequest::class, 'id','pickup_request_id');
    }

    public function pickerName() {
        return $this->hasOne(User::class, 'id','picker_id');
    }
}
