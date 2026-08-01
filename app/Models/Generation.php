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
    'status',
    'tripo_url',
    'file_size',
    'source_type',
    'prompt',
    'credits_used',
];
}