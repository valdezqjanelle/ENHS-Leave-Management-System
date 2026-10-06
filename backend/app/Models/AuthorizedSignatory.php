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
        'updated_by',
    ];

    protected $hidden = [
        'signature_path',
    ];
}
