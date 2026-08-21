<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    protected $fillable = [
        'minimum_notice_days',
        'require_documents',
        'max_consecutive_days',
        'general_policy',
    ];

    protected $casts = [
        'require_documents' => 'boolean',
    ];
}