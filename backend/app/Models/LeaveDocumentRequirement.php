<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveDocumentRequirement extends Model
{
    protected $fillable = [
        'leave_type_id',
        'document_name',
        'description',
        'is_required',
        'requirement_type',
        'condition_days',
        'condition_filed_in_advance',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'leave_type_id' => 'integer',
        'is_required' => 'boolean',
        'condition_days' => 'integer',
        'condition_filed_in_advance' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    public function attachments()
    {
        return $this->hasMany(LeaveAttachment::class, 'leave_document_requirement_id');
    }

    public function isRequiredForDays(int $days, bool $filedInAdvance = false): bool
    {
        if (!$this->is_active || !$this->is_required) {
            return false;
        }

        if ($this->requirement_type === 'always') {
            return true;
        }

        return $this->requirement_type === 'conditional'
            && (
                ($this->condition_days !== null && $days >= $this->condition_days)
                || ($this->condition_filed_in_advance && $filedInAdvance)
            );
    }
}
