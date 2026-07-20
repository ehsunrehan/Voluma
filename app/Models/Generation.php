<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generation extends Model
{
    protected $fillable = [

        'user_id',

        'task_id',

        'original_image',

        'thumbnail',

        'glb_url',

        'status',

        'credits_used'

    ];
}