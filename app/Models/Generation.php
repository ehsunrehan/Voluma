<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Generation extends Model
{
    protected $fillable = [

        'user_id',

        'original_image',

        'removed_background',

        'glb_file',

        'preview_image',

        'tripo_task_id',

        'status',

        'credits_used',

        'downloads',

        'renew_count'

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
