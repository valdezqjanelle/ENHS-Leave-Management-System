<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAttachment extends Model
{
    protected $table = 'leave_attachments';

    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'leave_id',
        'file_name',
        'file_path',
    ];

    public function leaveApplication()
    {
        return $this->belongsTo(
            LeaveApplication::class,
            'leave_id',
            'leave_id'
        );
    }
}