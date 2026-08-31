<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\LeaveApplication;
use App\Models\LeaveAttachment;
use App\Models\EmployeeRecord;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Support\AuditLogger;

// use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Tcpdf\Fpdi;

class LeaveController extends Controller
{

    /*
    | EMPLOYEE: SUBMIT LEAVE
    */
    public function store(Request $request)
    {
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                \Log::info('ATTACHMENT DEBUG', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'error' => $file->getError(),
                    'error_message' => $file->getErrorMessage(),
                    'mime' => $file->getMimeType(),
                ]);
            }
        }
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'date_filed' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'number_of_days' => 'required|integer|min:1',
            'commutation' => 'nullable|string',
            'reason' => 'required|string',
            'applicants_signature' => [
                'required',
                'string',
                'starts_with:data:image/png;base64,',
            ],

            // ENHS FIELDS
            'vacation_location_type' => 'nullable|string',
            'vacation_location' => 'nullable|string',
            'sick_type' => 'nullable|string',
            'illness' => 'nullable|string',

            'masters_degree' => 'nullable|boolean',
            'board_exam_review' => 'nullable|boolean',

            'monetization' => 'nullable|boolean',
            'terminal_leave' => 'nullable|boolean',

            'other_purpose' => 'nullable|string',

            'certification_as_of' => 'nullable|date',

            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found'
            ], 404);
        }

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $isVacation = str_contains(
            strtolower($leaveType->leave_type_name),
            'vacation'
        );

        $isSick = str_contains(
            strtolower($leaveType->leave_type_name),
            'sick'
        );

        $leave = LeaveApplication::create([
            'employee_id' => $employee->employee_id,
            'leave_type_id' => $request->leave_type_id,

            'date_filed' => $request->date_filed,

            // VACATION
            'vacation_location_type' => $request->vacation_location_type,
            'vacation_location' => $request->vacation_location,

            // SICK
            'sick_type' => $request->sick_type,
            'illness' => $request->illness,

            // STUDY
            'masters_degree' => $request->masters_degree ?? false,
            'board_exam_review' => $request->board_exam_review ?? false,

            // OTHER
            'monetization' => $request->monetization ?? false,
            'terminal_leave' => $request->terminal_leave ?? false,
            'other_purpose' => $request->other_purpose,

            // DATES
            'number_of_days' => $request->number_of_days,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,

            'commutation' => $request->commutation,
            'reason' => $request->reason,
            'applicants_signature' => $request->applicants_signature,

            // CERTIFICATION
            'certification_as_of' => $request->certification_as_of,

            'vacation_total_earned' => null,
            'vacation_less_application' => $isVacation
                ? $request->number_of_days
                : null,
            'vacation_balance' => null,

            'sick_total_earned' => null,
            'sick_less_application' => $isSick
                ? $request->number_of_days
                : null,
            'sick_balance' => null,

            // DEFAULT STATUS
            'recommendation_status' => 'pending',
            'final_status' => 'pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ATTACHMENTS
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store(
                    'leave_attachments',
                    'public'
                );

                LeaveAttachment::create([
                    'leave_id' => $leave->leave_id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }
        }

        return response()->json([
            'message' => 'Leave application submitted successfully',
            'data' => $leave->load([
                'employee',
                'leaveType',
                'attachments'
            ])
        ], 201);
    }


    /*
    | EMPLOYEE: VIEW OWN LEAVES
    */
    public function myLeaves(Request $request)
    {
        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found'
            ], 404);
        }

        return LeaveApplication::with([
            'employee',
            'leaveType',
            'attachments'
        ])
            ->where('employee_id', $employee->employee_id)
            ->oldest()
            ->get();
    }


    /*
    | EMPLOYEE: VIEW SINGLE
    */
    public function myLeave($id, Request $request)
    {
        $employee = EmployeeRecord::where(
            'user_id',
            $request->user()->user_id
        )->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee record not found'
            ], 404);
        }

        return LeaveApplication::with([
            'employee',
            'leaveType',
            'attachments'
        ])
            ->where('employee_id', $employee->employee_id)
            ->where('leave_id', $id)
            ->firstOrFail();
    }


    /*
    | ADMIN: VIEW ALL
    */
    public function index()
    {
        return LeaveApplication::with([
            'employee',
            'leaveType',
            'attachments'
        ])
            ->latest()
            ->get();
    }

    /*
| ADMIN: VIEW ONE
*/
    public function show($id)
    {
        $leave = LeaveApplication::with([
            'employee',
            'leaveType',
            'attachments'
        ])->findOrFail($id);

        return response()->json($leave);
    }

public function downloadPdf($id)
{
    \Log::info('PDF METHOD REACHED', [
        'id' => $id,
        'user_id' => auth()->user()?->user_id,
        'email' => auth()->user()?->email,
    ]);

    $leave = LeaveApplication::with([
        'employee.position',
        'leaveType'
    ])->findOrFail($id);

    $employee = $leave->employee;

    /*
    |--------------------------------------------------------------------------
    | LOAD COORDINATES
    |--------------------------------------------------------------------------
    */

    $coords = require config_path(
        'cs_form_6_coordinates.php'
    );
    /*
    |--------------------------------------------------------------------------
    | FILE NAME
    |--------------------------------------------------------------------------
    */

    $employeeName = $employee
        ? "{$employee->last_name}_{$employee->first_name}"
        : "employee_{$leave->employee_id}";

    $dateFiled = \Carbon\Carbon::parse($leave->date_filed)
        ->format('Y-m-d');

    $filename = "Leave_Application_{$employeeName}_{$dateFiled}.pdf";

    /*
    |--------------------------------------------------------------------------
    | CS FORM 6 TEMPLATE
    |--------------------------------------------------------------------------
    */

    $template = storage_path(
        'app/pdf-templates/cs-form-6.pdf'
    );

    \Log::info('TCPDF TEMPLATE CHECK', [
        'path' => $template,
        'exists' => file_exists($template),
        'readable' => is_readable($template),
    ]);

    if (!file_exists($template)) {
        return response()->json([
            'message' => 'CS Form template not found.',
            'path' => $template,
        ], 500);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE A4 PDF
    |--------------------------------------------------------------------------
    */

    $pdf = new Fpdi(
        'P',
        'pt',
        'A4',
        true,
        'UTF-8',
        false
    );

    $pdf->SetCreator('ENHS Leave System');
    $pdf->SetAuthor('ENHS Leave System');
    $pdf->SetTitle('Leave Application');

    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->AddPage();

    /*
    |--------------------------------------------------------------------------
    | IMPORT CS FORM 6
    |--------------------------------------------------------------------------
    */

    $pdf->setSourceFile($template);

    $tpl = $pdf->importPage(1);

    // A4 = 595.28 × 841.89 pt
    $pdf->useTemplate(
        $tpl,
        0,
        0,
        595.28,
        841.89
    );


    /*
    |--------------------------------------------------------------------------
    | NORMAL FONT
    |--------------------------------------------------------------------------
    */

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 8);

    /*
    |--------------------------------------------------------------------------
    | HELPER FOR COORDINATES
    |--------------------------------------------------------------------------
    |
    | Instead of:
    |     $pdf->Text(270, 125, $value);
    |
    | We can now use:
    |     $this->pdfText($pdf, $coords['last_name'], $value);
    |
    */

    $text = function ($key, $value) use ($pdf, $coords) {

        if (
            !isset($coords[$key]) ||
            $value === null ||
            $value === ''
        ) {
            return;
        }

        $pdf->Text(
            $coords[$key]['x'],
            $coords[$key]['y'],
            (string) $value
        );
    };

    /*
    |--------------------------------------------------------------------------
    | HELPER FOR CHECKMARKS
    |--------------------------------------------------------------------------
    */

    $check = function ($key, $condition) use ($pdf, $coords) {

        if (
            !$condition ||
            !isset($coords[$key])
        ) {
            return;
        }

        $pdf->SetFont(
            'dejavusans',
            '',
            8
        );

        $pdf->Text(
            $coords[$key]['x'],
            $coords[$key]['y'],
            "\u{2713}"
        );

        $pdf->SetFont(
            'helvetica',
            '',
            8
        );
    };

        $textWrap = function ($key, $value, $width, $lineHeight = 10) use ($pdf, $coords) {

        if (
            !isset($coords[$key]) ||
            $value === null ||
            $value === ''
        ) {
            return;
        }

        $pdf->SetXY(
            $coords[$key]['x'],
            $coords[$key]['y']
        );

        $pdf->MultiCell(
            $width,
            $lineHeight,
            (string) $value,
            0,
            'L',
            false,
            1
        );
    };

    /*
    |--------------------------------------------------------------------------
    | 1–5. EMPLOYEE INFORMATION
    |--------------------------------------------------------------------------
    */

    $text(
        'office_department',
        'Echague National High School'
    );

    $text(
        'last_name',
        $employee->last_name ?? ''
    );

    $text(
        'first_name',
        $employee->first_name ?? ''
    );

    $text(
        'middle_name',
        $employee->middle_name ?? ''
    );

    $text(
        'date_of_filing',
        \Carbon\Carbon::parse(
            $leave->date_filed
        )->format('m/d/Y')
    );

    $text(
        'position',
        $employee->position->name ?? ''
    );

    $text(
        'salary',
        number_format(
            $employee->salary ?? 0,
            2
        )
    );

    /*
    |--------------------------------------------------------------------------
    | 6.A. TYPE OF LEAVE
    |--------------------------------------------------------------------------
    */

    $leaveName = strtolower(
        $leave->leaveType->leave_type_name ?? ''
    );

    $check(
        'chk_vacation',
        str_contains($leaveName, 'vacation')
    );

    $check(
        'chk_mandatory_forced',
        str_contains($leaveName, 'mandatory') ||
        str_contains($leaveName, 'forced')
    );

    $check(
        'chk_sick',
        str_contains($leaveName, 'sick')
    );

    $check(
        'chk_maternity',
        str_contains($leaveName, 'maternity')
    );

    $check(
        'chk_paternity',
        str_contains($leaveName, 'paternity')
    );

    $check(
        'chk_special_privilege',
        str_contains($leaveName, 'special privilege')
    );

    $check(
        'chk_solo_parent',
        str_contains($leaveName, 'solo parent')
    );

    $check(
        'chk_study',
        str_contains($leaveName, 'study')
    );

    $check(
        'chk_vawc',
        str_contains($leaveName, 'vawc')
    );

    $check(
        'chk_rehabilitation',
        str_contains($leaveName, 'rehabilitation')
    );

    $check(
        'chk_special_women',
        str_contains($leaveName, 'women')
    );

    $check(
        'chk_special_emergency',
        str_contains($leaveName, 'emergency') ||
        str_contains($leaveName, 'calamity')
    );

    $check(
        'chk_adoption',
        str_contains($leaveName, 'adoption')
    );

    $text(
        'others_specify',
        $leave->other_purpose ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | 6.B. DETAILS OF LEAVE
    |--------------------------------------------------------------------------
    */

    $check(
        'chk_within_philippines',
        $leave->vacation_location_type === 'within_philippines'
    );

    $text(
        'within_philippines_text',
        $leave->vacation_location_type === 'within_philippines'
            ? $leave->vacation_location
            : ''
    );

    $check(
        'chk_abroad',
        $leave->vacation_location_type === 'abroad'
    );

    $text(
        'abroad_text',
        $leave->vacation_location_type === 'abroad'
            ? $leave->vacation_location
            : ''
    );

    $check(
        'chk_in_hospital',
        $leave->sick_type === 'in_hospital'
    );

        $textWrap(
        'in_hospital_illness',
        $leave->sick_type === 'in_hospital' ? $leave->illness : '',
        150
    );

    $check(
        'chk_out_patient',
        $leave->sick_type === 'out_patient'
    );

        $textWrap(
        'out_patient_illness',
        $leave->sick_type === 'out_patient' ? $leave->illness : '',
        150
    );

    $textWrap(
        'special_women_illness',
        $leave->special_women_illness ?? '',
        150
    );

    $check(
        'chk_completion_masters',
        (bool) $leave->masters_degree
    );

    $check(
        'chk_bar_board_exam',
        (bool) $leave->board_exam_review
    );

    $check(
        'chk_monetization',
        (bool) $leave->monetization
    );

    $check(
        'chk_terminal_leave',
        (bool) $leave->terminal_leave
    );

    /*
    |--------------------------------------------------------------------------
    | 6.C. NUMBER OF DAYS
    |--------------------------------------------------------------------------
    */

    $text(
        'working_days_applied',
        $leave->number_of_days
    );

    /*
    |--------------------------------------------------------------------------
    | INCLUSIVE DATES
    |--------------------------------------------------------------------------
    */

    $inclusiveDates =
        \Carbon\Carbon::parse(
            $leave->start_date
        )->format('M d, Y')
        . ' - ' .
        \Carbon\Carbon::parse(
            $leave->end_date
        )->format('M d, Y');

    $text(
        'inclusive_dates',
        $inclusiveDates
    );

    /*
    |--------------------------------------------------------------------------
    | 6.D. COMMUTATION
    |--------------------------------------------------------------------------
    */

    $check(
        'chk_commutation_not_requested',
        in_array(
            $leave->commutation,
            ['not_requested', 'not requested']
        )
    );

    $check(
        'chk_commutation_requested',
        $leave->commutation === 'requested'
    );

    /*
    |--------------------------------------------------------------------------
    | APPLICANT SIGNATURE
    |--------------------------------------------------------------------------
    */

    if ($leave->applicants_signature) {

        $image = preg_replace(
            '#^data:image/\w+;base64,#i',
            '',
            $leave->applicants_signature
        );

        $binary = base64_decode(
            $image
        );

        if ($binary !== false) {

            $tmp = tempnam(
                sys_get_temp_dir(),
                'sig'
            );

            file_put_contents(
                $tmp,
                $binary
            );

            if (isset($coords['applicant_signature'])) {

                $pdf->Image(
                    $tmp,
                    $coords['applicant_signature']['x'],
                    $coords['applicant_signature']['y'],
                    90,
                    28,
                    'PNG'
                );
            }

            @unlink($tmp);
        }
    }

    $middleInitial = '';

if (!empty($employee->middle_name)) {
    $middleInitial = strtoupper(
        substr(
            trim($employee->middle_name),
            0,
            1
        )
    ) . '.';
}

$applicantSignatureName = trim(
    ($employee->first_name ?? '') . ' ' .
    ($middleInitial ? $middleInitial . ' ' : '') .
    ($employee->last_name ?? '')
);

$text(
    'applicant_signature_name',
    $applicantSignatureName
);

    /*
    |--------------------------------------------------------------------------
    | 7.A. CERTIFICATION OF LEAVE CREDITS
    |--------------------------------------------------------------------------
    */

    $text(
        'certification_as_of',
        $leave->certification_as_of
            ? \Carbon\Carbon::parse(
                $leave->certification_as_of
            )->format('m/d/Y')
            : ''
    );

    $text(
        'vl_total_earned',
        $leave->vacation_total_earned
    );

    $text(
        'sl_total_earned',
        $leave->sick_total_earned
    );

    $text(
        'vl_less_this_application',
        $leave->vacation_less_application
    );

    $text(
        'sl_less_this_application',
        $leave->sick_less_application
    );

    $text(
        'vl_balance',
        $leave->vacation_balance
    );

    $text(
        'sl_balance',
        $leave->sick_balance
    );

    /*
    |--------------------------------------------------------------------------
    | 7.B. RECOMMENDATION
    |--------------------------------------------------------------------------
    */

    $check(
        'chk_for_approval',
        $leave->recommendation_status === 'approved'
    );

    $check(
        'chk_for_disapproval',
        $leave->recommendation_status === 'disapproved'
    );

    if (
        $leave->recommendation_status === 'disapproved'
    ) {

        $textWrap(
            'disapproval_reason_line',
            $leave->recommendation_reason,
            180
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 7.C. APPROVED FOR
    |--------------------------------------------------------------------------
    */

    $text(
        'approved_days_with_pay',
        $leave->days_with_pay
    );

    $text(
        'approved_days_without_pay',
        $leave->days_without_pay
    );

    $text(
        'approved_days_others',
        $leave->other_approval
    );

    /*
    |--------------------------------------------------------------------------
    | 7.D. DISAPPROVED DUE TO
    |--------------------------------------------------------------------------
    */

        $textWrap(
        'disapproved_reason_l1',
        $leave->disapproval_reason,
        180
    );

    /*
    |--------------------------------------------------------------------------
    | OUTPUT
    |--------------------------------------------------------------------------
    */

    return response(
        $pdf->Output('', 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>
                "inline; filename=\"{$filename}\"",
        ]
    );
}
    public function downloadAttachment($leave_id, $attachment_id)
    {
        $attachment = LeaveAttachment::where('leave_id', $leave_id)
            ->where('attachment_id', $attachment_id)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json([
                'message' => 'Attachment file not found.'
            ], 404);
        }

        $path = Storage::disk('public')->path($attachment->file_path);

        return response()->file($path);
    }


    /*
|--------------------------------------------------------------------------
| ADMIN: UPDATE STATUS
|--------------------------------------------------------------------------
*/
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            // Certification
            'certification_as_of' => 'nullable|date',

            'vacation_total_earned' => 'nullable|numeric',
            'vacation_less_application' => 'nullable|numeric',
            'vacation_balance' => 'nullable|numeric',

            'sick_total_earned' => 'nullable|numeric',
            'sick_less_application' => 'nullable|numeric',
            'sick_balance' => 'nullable|numeric',

            // Recommendation
            'recommendation_status' => 'nullable|string',
            'recommendation_reason' => 'nullable|string',

            // Approval
            'days_with_pay' => 'nullable|integer',
            'days_without_pay' => 'nullable|integer',
            'other_approval' => 'nullable|string',

            // Final
            'final_status' => 'nullable|in:pending,approved,disapproved',
            'disapproval_reason' => 'nullable|string',
            'admin_remarks' => 'nullable|string',

            // BALANCE DEDUCTION
            'deduct_balance' => 'nullable|boolean',
            'service_credits_deduct_days' => 'nullable|numeric|min:0',
            'vacation_deduct_days' => 'nullable|numeric|min:0',
            'sick_deduct_days' => 'nullable|numeric|min:0',
        ]);

        $leave = LeaveApplication::findOrFail($id);

        $previousStatus = strtolower($leave->final_status);

        /*
    |--------------------------------------------------------------------------
    | UPDATE ONLY SUPPLIED FIELDS
    |--------------------------------------------------------------------------
    */

        $updateData = [];

        if ($request->has('certification_as_of')) {
            $updateData['certification_as_of'] =
                $request->certification_as_of;
        }

        if ($request->has('vacation_total_earned')) {
            $updateData['vacation_total_earned'] =
                $request->vacation_total_earned;
        }

        if ($request->has('vacation_less_application')) {
            $updateData['vacation_less_application'] =
                $request->vacation_less_application;
        }

        if ($request->has('vacation_balance')) {
            $updateData['vacation_balance'] =
                $request->vacation_balance;
        }

        if ($request->has('sick_total_earned')) {
            $updateData['sick_total_earned'] =
                $request->sick_total_earned;
        }

        if ($request->has('sick_less_application')) {
            $updateData['sick_less_application'] =
                $request->sick_less_application;
        }

        if ($request->has('sick_balance')) {
            $updateData['sick_balance'] =
                $request->sick_balance;
        }

        if ($request->has('recommendation_status')) {
            $updateData['recommendation_status'] =
                $request->recommendation_status;
        }

        if ($request->has('recommendation_reason')) {
            $updateData['recommendation_reason'] =
                $request->recommendation_reason;
        }

        if ($request->has('days_with_pay')) {
            $updateData['days_with_pay'] =
                $request->days_with_pay;
        }

        if ($request->has('days_without_pay')) {
            $updateData['days_without_pay'] =
                $request->days_without_pay;
        }

        if ($request->has('other_approval')) {
            $updateData['other_approval'] =
                $request->other_approval;
        }

        if ($request->has('final_status')) {
            $updateData['final_status'] =
                strtolower($request->final_status);
        }

        if ($request->has('disapproval_reason')) {
            $updateData['disapproval_reason'] =
                $request->disapproval_reason;
        }

        if ($request->has('admin_remarks')) {
            $updateData['admin_remarks'] =
                $request->admin_remarks;
        }

        $leave->update($updateData);

        /*
    |--------------------------------------------------------------------------
    | DEDUCT BALANCE ONLY IF ADMIN CHOSE YES
    |--------------------------------------------------------------------------
    */

        $newStatus = strtolower($leave->final_status);

        if (
            $previousStatus !== 'approved' &&
            $newStatus === 'approved' &&
            $request->boolean('deduct_balance')
        ) {
            $this->deductLeaveBalance(
                $leave,
                $request->vacation_deduct_days ?? 0,
                $request->sick_deduct_days ?? 0,
                $request->service_credits_deduct_days ?? 0
            );
        }

        $leave->load(['employee', 'leaveType', 'attachments']);

        if ($request->has('final_status') && $newStatus !== $previousStatus) {
            $employeeName = $leave->employee
                ? "{$leave->employee->first_name} {$leave->employee->last_name}"
                : "employee #{$leave->employee_id}";

            AuditLogger::log(
                'Leave ' . ucfirst($newStatus),
                "Marked leave #{$leave->leave_id} for {$employeeName} as {$newStatus}"
            );
        }

        return response()->json([
            'message' => 'Leave updated successfully',
            'data' => $leave
        ]);
    }


    /*
|--------------------------------------------------------------------------
| DEDUCT LEAVE BALANCE
|--------------------------------------------------------------------------
*/
    private function deductLeaveBalance(
        $leave,
        $vacationDeductDays,
        $sickDeductDays,
        $serviceCreditsDeductDays
    ) {
        $balance = LeaveBalance::where(
            'employee_id',
            $leave->employee_id
        )->first();

        if (!$balance) {
            throw new \Exception(
                'Leave balance record not found.'
            );
        }

        $vacationDays = (float) $vacationDeductDays;
        $sickDays = (float) $sickDeductDays;
        $serviceCreditsDays = (float) $serviceCreditsDeductDays;

        $totalDeduction =
            $vacationDays +
            $sickDays +
            $serviceCreditsDays;

        /*
    |--------------------------------------------------------------------------
    | TOTAL DEDUCTION CANNOT EXCEED DAYS APPLIED
    |--------------------------------------------------------------------------
    */

        if ($totalDeduction > (float) $leave->number_of_days) {
            throw new \Exception(
                'Total deduction cannot be greater than the number of days applied.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | CHECK VACATION BALANCE
    |--------------------------------------------------------------------------
    */

        $currentVacationBalance =
            (float) $balance->vacation_balance;

        if ($vacationDays > $currentVacationBalance) {
            throw new \Exception(
                'Insufficient vacation leave balance.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | CHECK SICK BALANCE
    |--------------------------------------------------------------------------
    */

        $currentSickBalance =
            (float) $balance->sick_balance;

        if ($sickDays > $currentSickBalance) {
            throw new \Exception(
                'Insufficient sick leave balance.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | CHECK SERVICE CREDITS
    |--------------------------------------------------------------------------
    */

        $currentServiceCredits =
            (float) ($balance->service_credits ?? 0);

        if ($serviceCreditsDays > $currentServiceCredits) {
            throw new \Exception(
                'Insufficient service credits.'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | DEDUCT VACATION
    |--------------------------------------------------------------------------
    */

        $balance->vacation_balance =
            $currentVacationBalance - $vacationDays;

        /*
    |--------------------------------------------------------------------------
    | DEDUCT SICK
    |--------------------------------------------------------------------------
    */

        $balance->sick_balance =
            $currentSickBalance - $sickDays;

        /*
    |--------------------------------------------------------------------------
    | DEDUCT SERVICE CREDITS
    |--------------------------------------------------------------------------
    */

        $balance->service_credits =
            $currentServiceCredits - $serviceCreditsDays;

        /*
    |--------------------------------------------------------------------------
    | USED LEAVE
    |--------------------------------------------------------------------------
    */

        $balance->used_leave =
            (float) ($balance->used_leave ?? 0)
            + $totalDeduction;

        $balance->last_updated = now();

        $balance->save();
    }

    public function destroy($id)
    {
        $leave = LeaveApplication::findOrFail($id);

        $leave->delete();

        AuditLogger::log(
            'Leave deleted',
            "Deleted leave #{$leave->leave_id} for employee #{$leave->employee_id}"
        );

        return response()->json([
            'message' => 'Leave application deleted successfully.'
        ], 200);
    }

    public function deletedLeaves()
    {
        try {
            $deletedLeaves = LeaveApplication::onlyTrashed()
                ->with([
                    'employee',
                    'leaveType',
                    'attachments'
                ])
                ->orderBy('deleted_at', 'desc')
                ->get();

            return response()->json($deletedLeaves);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch deleted leave applications', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function restoreLeave($id)
    {
        $leave = LeaveApplication::onlyTrashed()->findOrFail($id);

        $leave->restore();

        AuditLogger::log(
            'Leave restored',
            "Restored leave #{$leave->leave_id} for employee #{$leave->employee_id}"
        );

        return response()->json([
            'message' => 'Leave application restored successfully.'
        ], 200);
    }
}
