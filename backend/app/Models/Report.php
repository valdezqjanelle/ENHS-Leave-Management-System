<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'generated_by',
        'report_type',
        'generated_date',
        'file_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}