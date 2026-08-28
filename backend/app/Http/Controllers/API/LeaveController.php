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

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Do NOT deduct the employee's actual leave balance here.
        |
        | The balance will only be deducted when the admin approves
        | the leave application.
        |--------------------------------------------------------------------------
        */

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

            /*
            |--------------------------------------------------------------------------
            | These are application snapshot values only.
            | They do NOT modify the actual leave_balances table.
            |--------------------------------------------------------------------------
            */

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

/*
| ADMIN: DOWNLOAD PDF
*/
public function downloadPdf($id)
{
    $leave = LeaveApplication::with(['employee', 'leaveType'])->findOrFail($id);
    $coords = config('cs_form_6_coordinates');

    $templatePath = storage_path('app/pdf-templates/cs-form-6.pdf');

    $pdf = new Fpdi('P', 'pt', 'A4');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $templateId = $pdf->setSourceFile($templatePath);
    $tpl = $pdf->importPage(1);
    $pdf->useTemplate($tpl, 0, 0, 595.3, 841.9);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 9);

    $text = function ($key, $value) use ($pdf, $coords) {
        if (!$value || !isset($coords[$key])) return;
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY($coords[$key]['x'], $coords[$key]['y'] - 9);
        $pdf->Cell(0, 9, $value, 0, 0, 'L');
    };

    $check = function ($key) use ($pdf, $coords) {
        if (!isset($coords[$key])) return;
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetXY($coords[$key]['x'] + 0.5, $coords[$key]['y'] - 9);
        $pdf->Cell(0, 9, "\u{2713}", 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 9);
    };

    $wrapLines = function ($text, $maxWidth) use ($pdf) {
    $pdf->SetFont('helvetica', '', 9);
    $words = explode(' ', $text);
    $lines = [];
    $current = '';
    foreach ($words as $word) {
        $test = $current === '' ? $word : $current . ' ' . $word;
        if ($pdf->GetStringWidth($test) > $maxWidth && $current !== '') {
            $lines[] = $current;
            $current = $word;
        } else {
            $current = $test;
        }
    }
    if ($current !== '') $lines[] = $current;
    return $lines;
};

    $employee = $leave->employee;
    $text('office_department', 'Echague National High School');
    $text('last_name', $employee->last_name ?? '');
    $text('first_name', $employee->first_name ?? '');
    $text('middle_name', $employee->middle_name ?? '');
    $text('date_of_filing', optional($leave->date_filed)->format('m/d/Y'));
    $text('position', $employee->position->name ?? '');
    $text('salary', $employee->salary ? number_format($employee->salary, 2) : '');

    $typeCheckboxMap = [
        'vacation'          => 'chk_vacation',
        'mandatory_forced'  => 'chk_mandatory_forced',
        'sick'              => 'chk_sick',
        'maternity'         => 'chk_maternity',
        'paternity'         => 'chk_paternity',
        'special_privilege' => 'chk_special_privilege',
        'solo_parent'       => 'chk_solo_parent',
        'study'             => 'chk_study',
        'vawc'              => 'chk_vawc',
        'rehabilitation'    => 'chk_rehabilitation',
        'special_women'     => 'chk_special_women',
        'special_emergency' => 'chk_special_emergency',
        'adoption'          => 'chk_adoption',
    ];
    $code = $leave->leaveType->code ?? null;
    if ($code && isset($typeCheckboxMap[$code])) {
        $check($typeCheckboxMap[$code]);
    } elseif ($leave->leaveType->leave_type_name === 'Others') {
        $text('others_specify', $leave->other_purpose ?? '');
    }

    if ($leave->vacation_location_type === 'within_philippines') {
        $check('chk_within_philippines');
        $text('within_philippines_text', $leave->vacation_location ?? '');
    } elseif ($leave->vacation_location_type === 'abroad') {
        $check('chk_abroad');
        $text('abroad_text', $leave->vacation_location ?? '');
    }

    if ($leave->sick_type === 'in_hospital') {
        $check('chk_in_hospital');
        $text('in_hospital_illness', $leave->illness ?? '');
    } elseif ($leave->sick_type === 'out_patient') {
        $check('chk_out_patient');
        $text('out_patient_illness', $leave->illness ?? '');
    }

    if ($leave->masters_degree) $check('chk_completion_masters');
    if ($leave->board_exam_review) $check('chk_bar_board_exam');
    if ($leave->monetization) $check('chk_monetization');
    if ($leave->terminal_leave) $check('chk_terminal_leave');

    $text('working_days_applied', (string) $leave->number_of_days);
    $inclusiveDates = optional($leave->start_date)->format('M d, Y') . ' - ' . optional($leave->end_date)->format('M d, Y');
    $text('inclusive_dates', $inclusiveDates);

    if ($leave->applicants_signature) {
        $signature = preg_replace(
            '#^data:image/png;base64,#',
            '',
            $leave->applicants_signature
        );
        $signatureBinary = base64_decode($signature, true);

        if ($signatureBinary !== false) {
            $signatureCoordinates = $coords['applicant_signature'];
            $pdf->Image(
                '@' . $signatureBinary,
                $signatureCoordinates['x'],
                $signatureCoordinates['y'],
                100,
                32,
                'PNG'
            );
        $middleInitial = '';
        if (!empty($employee->middle_name)) {
            $middleInitial = strtoupper(substr(trim($employee->middle_name), 0, 1)) . '. ';
        }

        // Build formatted name: First M. Last
        $applicantName = trim(($employee->first_name ?? '') . ' ' . $middleInitial . ($employee->last_name ?? ''));

        $text('applicant_signature_name', $applicantName);
        }
    }

    if ($leave->commutation === 'requested') {
        $check('chk_commutation_requested');
    } elseif ($leave->commutation === 'not requested') {
        $check('chk_commutation_not_requested');
    }

    $text('certification_as_of', optional($leave->certification_as_of)->format('m/d/Y'));
    $text('vl_total_earned', $leave->vacation_total_earned !== null ? number_format($leave->vacation_total_earned, 2) : '');
    $text('sl_total_earned', $leave->sick_total_earned !== null ? number_format($leave->sick_total_earned, 2) : '');
    $text('vl_less_this_application', $leave->vacation_less_application !== null ? number_format($leave->vacation_less_application, 2) : '');
    $text('sl_less_this_application', $leave->sick_less_application !== null ? number_format($leave->sick_less_application, 2) : '');
    $text('vl_balance', $leave->vacation_balance !== null ? number_format($leave->vacation_balance, 2) : '');
    $text('sl_balance', $leave->sick_balance !== null ? number_format($leave->sick_balance, 2) : '');

    if ($leave->recommendation_status === 'approved') {
        $check('chk_for_approval');
    } elseif ($leave->recommendation_status === 'disapproved') {
        $check('chk_for_disapproval');
        $lines = $wrapLines($leave->recommendation_reason ?? '', 195);
        $lineKeys = ['disapproval_reason_line', 'disapproval_reason_l2', 'disapproval_reason_l3', 'disapproval_reason_l4'];
        foreach ($lineKeys as $i => $lk) {
            if (isset($lines[$i])) $text($lk, $lines[$i]);
        }
    }

    if ($leave->final_status === 'approved') {
        $text('approved_days_with_pay', $leave->days_with_pay !== null ? (string) $leave->days_with_pay : '');
        $text('approved_days_without_pay', $leave->days_without_pay !== null ? (string) $leave->days_without_pay : '');
        $text('approved_days_others', $leave->other_approval ?? '');
    } elseif ($leave->final_status === 'disapproved') {
        $lines = $wrapLines($leave->disapproval_reason ?? '', 195);
        $lineKeys = ['disapproved_reason_l1', 'disapproved_reason_l2', 'disapproved_reason_l3'];
        foreach ($lineKeys as $i => $lk) {
            if (isset($lines[$i])) $text($lk, $lines[$i]);
        }
    }

    $employeeName = $employee
        ? "{$employee->last_name}_{$employee->first_name}"
        : "employee_{$leave->employee_id}";
    $filename = "Leave_Application_{$employeeName}_{$leave->leave_id}.pdf";

    return response($pdf->Output($filename, 'S'), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ]);
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
        'final_status' => 'nullable|in:pending,approved,rejected',
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
}

