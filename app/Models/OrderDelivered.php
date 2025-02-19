<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDelivered extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_assigned_id',
        'amount',
        'consignee_relation_id',
        'other_relation',
        'receiver_name',
        'cnic',
        'comment',
        'signature',
        'location_picture',
    ];

    public function consigneeRelation()
    {
        return $this->hasOne(ConsigneeRelation::class, 'id','consignee_relation_id');
    }
}
