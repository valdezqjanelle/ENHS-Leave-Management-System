<?php

namespace Tests\Unit;

use App\Models\LeaveDocumentRequirement;
use PHPUnit\Framework\TestCase;

class LeaveDocumentRequirementTest extends TestCase
{
    public function test_conditional_requirement_is_enforced_only_at_a_configured_threshold(): void
    {
        $requirement = new LeaveDocumentRequirement([
            'is_active' => true,
            'is_required' => true,
            'requirement_type' => 'conditional',
            'condition_days' => 5,
            'condition_filed_in_advance' => true,
        ]);

        $this->assertFalse($requirement->isRequiredForDays(4));
        $this->assertTrue($requirement->isRequiredForDays(5));
        $this->assertTrue($requirement->isRequiredForDays(1, true));
    }

    public function test_unconfigured_conditional_optional_and_inactive_requirements_do_not_block(): void
    {
        $unconfigured = new LeaveDocumentRequirement([
            'is_active' => true,
            'is_required' => true,
            'requirement_type' => 'conditional',
            'condition_days' => null,
            'condition_filed_in_advance' => false,
        ]);
        $optional = new LeaveDocumentRequirement([
            'is_active' => true,
            'is_required' => false,
            'requirement_type' => 'always',
        ]);
        $inactive = new LeaveDocumentRequirement([
            'is_active' => false,
            'is_required' => true,
            'requirement_type' => 'always',
        ]);

        $this->assertFalse($unconfigured->isRequiredForDays(100));
        $this->assertFalse($optional->isRequiredForDays(1));
        $this->assertFalse($inactive->isRequiredForDays(1));
    }
}
