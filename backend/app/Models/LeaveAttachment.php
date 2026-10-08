<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAttachment extends Model
{
    protected $table = 'leave_attachments';

    protected $primaryKey = 'attachment_id';

    protected $hidden = [
        'file_path',
    ];

    protected $fillable = [
        'leave_id',
        'file_name',
        'file_path',
        'leave_document_requirement_id',
        'stored_filename',
        'file_type',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function leaveApplication()
    {
        return $this->belongsTo(
            LeaveApplication::class,
            'leave_id',
            'leave_id'
        );
    }

    public function documentRequirement()
    {
        return $this->belongsTo(
            LeaveDocumentRequirement::class,
            'leave_document_requirement_id'
        );
    }
}