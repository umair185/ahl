<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackBalancing extends Model
{
    use HasFactory;

    protected $fillable = ['date_from','date_to','total_parcels','scan_parcels','mode','remarks','remarks_by'];

    public function remarksBy()
    {
        return $this->belongsTo(User::class, 'remarks_by');
    }
}
