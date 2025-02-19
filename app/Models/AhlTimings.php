<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhlTimings extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'timings',  
        'status',
    ];
}
