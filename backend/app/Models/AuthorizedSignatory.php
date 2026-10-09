<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizedSignatory extends Model
{
    protected $table = 'authorized_signatories';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'designation',
        'signature_path',
        'use_signature',
        'updated_by',
    ];

    protected $casts = [
        'use_signature' => 'boolean',
    ];

    protected $hidden = [
        'signature_path',
    ];
}