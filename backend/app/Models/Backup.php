<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $primaryKey = 'backup_id';

    protected $fillable = [
        'file_name',
        'file_path',
        'backup_type',
        'file_size',
        'status',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}