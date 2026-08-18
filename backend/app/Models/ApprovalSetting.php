<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalSetting extends Model
{
    protected $primaryKey = 'approval_setting_id';

    protected $fillable = [
        'require_admin_review',
        'require_principal_endorsement',
        'allow_admin_remarks',
        'auto_update_status',
        'auto_update_balance',
    ];

    protected $casts = [
        'require_admin_review' => 'boolean',
        'require_principal_endorsement' => 'boolean',
        'allow_admin_remarks' => 'boolean',
        'auto_update_status' => 'boolean',
        'auto_update_balance' => 'boolean',
    ];
}