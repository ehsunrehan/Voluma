<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    protected $fillable = [
        'user_id','job_id','original_path','from_format','to_format',
        'status','converted_url','credits_used',
    ];
}