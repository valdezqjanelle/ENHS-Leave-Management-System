<?php

namespace Tests\Feature;

use App\Models\LeaveDocumentRequirement;
use App\Models\EmployeeRecord;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveDocumentRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checklist_evaluates_conditional_requirements_by_day_count(): void
    {
        $user = User::create([
            'email' => 'faculty@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);
        $leaveType = $this->createLeaveType();
        LeaveDocumentRequirement::create([
            'leave_type_id' => $leaveType->leave_type_id,
            'document_name' => 'Medical Certificate',
            'is_required' => true,
            'requirement_type' => 'conditional',
            'condition_days' => 3,
            'condition_filed_in_advance' => false,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        LeaveDocumentRequirement::create([
            'leave_type_id' => $leaveType->leave_type_id,
            'document_name' => 'Inactive Document',
            'is_required' => true,
            'requirement_type' => 'always',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/leave-types/{$leaveType->leave_type_id}/document-requirements?number_of_days=2")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.required', false);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/leave-types/{$leaveType->leave_type_id}/document-requirements?number_of_days=3")
            ->assertOk()
            ->assertJsonPath('0.required', true);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/leave-types/{$leaveType->leave_type_id}/document-requirements?number_of_days=1&start_date=" . now()->addDay()->toDateString())
            ->assertOk()
            ->assertJsonPath('0.required', false);
    }

    public function test_api_rejects_submission_without_a_required_document(): void
    {
        $user = User::create([
            'email' => 'faculty@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);
        $leaveType = $this->createLeaveType();
        LeaveDocumentRequirement::create([
            'leave_type_id' => $leaveType->leave_type_id,
            'document_name' => 'Medical Certificate',
            'is_required' => true,
            'requirement_type' => 'always',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/leave-applications', [
                'leave_type_id' => $leaveType->leave_type_id,
                'date_filed' => '2026-10-09',
                'start_date' => '2026-10-12',
                'end_date' => '2026-10-12',
                'number_of_days' => 1,
                'reason' => 'Medical leave',
                'applicants_signature' => 'data:image/png;base64,signature',
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Medical Certificate is required for this leave application.'
            );
    }

    public function test_required_upload_is_linked_and_application_keeps_its_requirement_snapshot(): void
    {
        Storage::fake('supabase');
        $user = User::create([
            'email' => 'faculty@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);
        $employee = EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => 'ENHS-001',
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'sex' => 'Female',
            'employment_status' => 'active',
            'date_hired' => '2020-01-01',
        ]);
        $leaveType = $this->createLeaveType();
        $requirement = LeaveDocumentRequirement::create([
            'leave_type_id' => $leaveType->leave_type_id,
            'document_name' => 'Medical Certificate',
            'description' => 'Upload the certificate.',
            'is_required' => true,
            'requirement_type' => 'always',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/leave-applications', [
                'leave_type_id' => $leaveType->leave_type_id,
                'date_filed' => '2026-10-09',
                'start_date' => '2026-10-12',
                'end_date' => '2026-10-12',
                'number_of_days' => 1,
                'reason' => 'Medical leave',
                'applicants_signature' => 'data:image/png;base64,signature',
                'document_attachments' => [
                    $requirement->id => UploadedFile::fake()->create(
                        'medical.pdf',
                        10,
                        'application/pdf'
                    ),
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.attachments.0.leave_document_requirement_id', $requirement->id)
            ->assertJsonPath('data.document_requirements_snapshot.0.document_name', 'Medical Certificate');

        $leaveId = $response->json('data.leave_id');
        $attachmentId = $response->json('data.attachments.0.attachment_id');
        $requirement->update(['document_name' => 'Updated Certificate']);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/my-leave-applications/{$leaveId}")
            ->assertOk()
            ->assertJsonPath('document_requirements_snapshot.0.document_name', 'Medical Certificate');

        $this->get("/api/leaves/{$leaveId}/attachments/{$attachmentId}")
            ->assertOk();

        $otherUser = User::create([
            'email' => 'other@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);
        $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/leaves/{$leaveId}/attachments/{$attachmentId}")
            ->assertForbidden();

        $this->assertSame($employee->employee_id, $response->json('data.employee.employee_id'));
    }

    public function test_non_admin_cannot_manage_document_requirements(): void
    {
        $user = User::create([
            'email' => 'faculty@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);
        $leaveType = $this->createLeaveType();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/admin/leave-types/{$leaveType->leave_type_id}/document-requirements")
            ->assertForbidden();
    }

    public function test_leave_types_without_required_documents_can_be_submitted_without_files(): void
    {
        $user = User::create([
            'email' => 'faculty@example.test',
            'password' => 'password',
            'role' => 'employee',
        ]);
        EmployeeRecord::create([
            'user_id' => $user->user_id,
            'employee_code' => 'ENHS-001',
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'sex' => 'Female',
            'employment_status' => 'active',
            'date_hired' => '2020-01-01',
        ]);
        $leaveTypes = [
            $this->createLeaveType(),
            LeaveType::create([
                'code' => 'OPT',
                'leave_type_name' => 'Optional Documents Leave',
            ]),
        ];
        LeaveDocumentRequirement::create([
            'leave_type_id' => $leaveTypes[1]->leave_type_id,
            'document_name' => 'Optional Supporting Document',
            'is_required' => false,
            'requirement_type' => 'always',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        foreach ($leaveTypes as $leaveType) {
            $this->actingAs($user, 'sanctum')
                ->post('/api/leave-applications', [
                    'leave_type_id' => $leaveType->leave_type_id,
                    'date_filed' => '2026-10-09',
                    'start_date' => '2026-10-12',
                    'end_date' => '2026-10-12',
                    'number_of_days' => 1,
                    'reason' => 'Leave request',
                    'applicants_signature' => 'data:image/png;base64,signature',
                ])
                ->assertCreated();
        }
    }

    private function createLeaveType(): LeaveType
    {
        return LeaveType::create([
            'code' => 'SL',
            'leave_type_name' => 'Sick Leave',
        ]);
    }
}
