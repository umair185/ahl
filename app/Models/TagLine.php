<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_line',  
        'status',
    ];
}
