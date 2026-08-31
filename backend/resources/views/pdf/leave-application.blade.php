<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 8mm; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9.5px;
        line-height: 1.25;
        color: #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .outer {
        border: 1px solid #000;
    }

    .form-code {
        font-size: 8.5px;
        font-style: italic;
        line-height: 1.2;
    }

    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding: 2px 0 4px;
    }

    .header .republic {
        font-size: 13px;
        font-weight: bold;
    }

    .header .dept {
        font-size: 17px;
        font-weight: bold;
        margin-top: 1px;
    }

    .header .region {
        font-size: 9.5px;
        margin-top: 1px;
    }

    .header h1 {
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 1px;
        margin-top: 5px;
    }

    .section-title {
        text-align: center;
        font-weight: bold;
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
        padding: 3px 0;
        font-size: 11px;
    }

    td, th { vertical-align: top; padding: 4px 6px; }

    .bold { font-weight: bold; }
    .center { text-align: center; }
    .italic { font-style: italic; }
    .small { font-size: 8.5px; }
    .line { border-bottom: 1px solid #000; display: inline-block; }
    .box-right { border-right: 1px solid #000; }
    .box-bottom { border-bottom: 1px solid #000; }
    .box-top { border-top: 1px solid #000; }
    .sig-line { border-bottom: 1px solid #000; height: 14px; margin-top: 12px; }

    .chk {
        display: inline-block;
        width: 8px;
        height: 8px;
        border: 1px solid #000;
        text-align: center;
        line-height: 8px;
        font-size: 7px;
        font-weight: bold;
        font-family: 'DejaVu Sans', sans-serif;
        vertical-align: middle;
        margin-right: 3px;
    }
</style>
</head>
<body>

@php
    $checked = fn($isChecked) => $isChecked ? "\u{2713}" : '';
@endphp

<table class="outer">
    <tr>
        <td colspan="2" style="padding-bottom:0;">
            <div class="form-code">Civil Service Form No. 6<br>Revised 2020</div>
        </td>
    </tr>

    <tr>
        <td colspan="2" class="header" style="border-top:none; padding-top:0;">
            <div class="republic">Republic of the Philippines</div>
            <div class="dept">Department of Education</div>
            <div class="region">
                Region 02 - Cagayan Valley<br>
                <span class="bold">SCHOOLS DIVISION OFFICE OF ISABELA</span>
            </div>
            <h1>APPLICATION FOR LEAVE</h1>
        </td>
    </tr>

    {{-- 1 & 2 --}}
    <tr>
        <td colspan="2" class="box-bottom" style="padding-bottom:2px;">
            <div class="bold">1. OFFICE / DEPARTMENT</div>
            <table style="margin-top:1px;">
                <tr>
                    <td style="width:34%; padding:0;">&nbsp;</td>
                    <td class="bold" style="width:66%; padding:0;">2. NAME:</td>
                </tr>
                <tr>
                    <td style="width:34%; padding:1px 8px 0 0;">
                        <div class="line" style="width:100%;">Echague National High School</div>
                    </td>
                    <td style="width:22%; padding:1px 8px 0 0;">
                        <div class="line" style="width:100%;">{{ $employee->last_name ?? '' }}</div>
                        <div class="center small">(Last)</div>
                    </td>
                    <td style="width:22%; padding:1px 8px 0 0;">
                        <div class="line" style="width:100%;">{{ $employee->first_name ?? '' }}</div>
                        <div class="center small">(First)</div>
                    </td>
                    <td style="width:22%; padding:1px 0 0 0;">
                        <div class="line" style="width:100%;">{{ $employee->middle_name ?? '' }}</div>
                        <div class="center small">(Middle)</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- 3, 4, 5 --}}
    <tr>
        <td colspan="2" class="box-bottom">
            <table>
                <tr>
                    <td class="bold" style="width:30%; padding:0;">3. DATE OF FILING</td>
                    <td class="bold" style="width:35%; padding:0;">4. POSITION</td>
                    <td class="bold" style="width:35%; padding:0;">5. SALARY</td>
                </tr>
                <tr>
                    <td style="width:30%; padding:3px 8px 0 0;">
                        <div class="line" style="width:100%;">{{ optional($leave->date_filed)->format('m/d/Y') }}</div>
                    </td>
                    <td style="width:35%; padding:3px 8px 0 0;">
                        <div class="line" style="width:100%;">{{ $employee->position->name ?? '' }}</div>
                    </td>
                    <td style="width:35%; padding:3px 0 0 0;">
                        <div class="line" style="width:100%;">{{ $employee->salary ? 'PHP ' . number_format($employee->salary, 2) : '' }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr><td colspan="2" class="section-title">6. DETAILS OF APPLICATION</td></tr>

    {{-- 6A / 6B --}}
    <tr>
        <td class="box-right box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold">6.A TYPE OF LEAVE TO BE AVAILED OF</div>
            <table style="margin-top:4px;">
                @foreach ($leaveTypes as $type)
                <tr>
                    <td style="width:14px; padding:1px 4px 1px 0;">
                        <span class="chk">{{ $checked($type->leave_type_id == $leave->leave_type_id) }}</span>
                    </td>
                    <td style="padding:1px 0;">{{ $type->leave_type_name }}</td>
                </tr>
                @endforeach
            </table>
            <div class="bold" style="margin-top:6px;">Others:</div>
            <div class="line" style="width:100%;">{{ $leave->other_purpose ?? '' }}</div>
        </td>

        <td class="box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold">6.B DETAILS OF LEAVE</div>

            <div style="margin-top:4px;" class="italic">In case of Vacation/Special Privilege Leave:</div>
            <div><span class="chk">{{ $checked(($leave->vacation_location_type ?? '') === 'within_philippines') }}</span> Within the Philippines
                <span class="line" style="width:120px;">{{ ($leave->vacation_location_type ?? '') === 'within_philippines' ? $leave->vacation_location : '' }}</span>
            </div>
            <div><span class="chk">{{ $checked(($leave->vacation_location_type ?? '') === 'abroad') }}</span> Abroad (Specify)
                <span class="line" style="width:110px;">{{ ($leave->vacation_location_type ?? '') === 'abroad' ? $leave->vacation_location : '' }}</span>
            </div>

            <div style="margin-top:4px;" class="italic">In case of Sick Leave:</div>
            <div><span class="chk">{{ $checked(($leave->sick_type ?? '') === 'in_hospital') }}</span> In Hospital (Specify Illness)
                <span class="line" style="width:90px;">{{ ($leave->sick_type ?? '') === 'in_hospital' ? $leave->illness : '' }}</span>
            </div>
            <div><span class="chk">{{ $checked(($leave->sick_type ?? '') === 'out_patient') }}</span> Out Patient (Specify Illness)
                <span class="line" style="width:90px;">{{ ($leave->sick_type ?? '') === 'out_patient' ? $leave->illness : '' }}</span>
            </div>

            <div style="margin-top:4px;" class="italic">In case of Special Leave Benefits for Women:</div>
            <div>(Specify Illness)
                <span class="line" style="width:150px;">{{ $leave->special_leave_women ?? '' }}</span>
            </div>

            <div style="margin-top:4px;" class="italic">In case of Study Leave:</div>
            <div><span class="chk">{{ $checked($leave->masters_degree) }}</span> Completion of Master's Degree</div>
            <div><span class="chk">{{ $checked($leave->board_exam_review) }}</span> BAR / Board Examination Review</div>

            <div style="margin-top:4px;" class="italic">Other purpose:</div>
            <div><span class="chk">{{ $checked($leave->monetization) }}</span> Monetization of Leave Credits</div>
            <div><span class="chk">{{ $checked($leave->terminal_leave) }}</span> Terminal Leave</div>
        </td>
    </tr>

    {{-- 6C / 6D --}}
    <tr>
        <td class="box-right box-bottom" style="width:50%;">
            <div class="bold">6.C NUMBER OF WORKING DAYS APPLIED FOR</div>
            <div class="line" style="width:100%; margin-top:8px;">{{ $leave->number_of_days ?? '' }}</div>

            <div class="bold" style="margin-top:8px;">INCLUSIVE DATES</div>
            <div class="line" style="width:100%; margin-top:8px;">
                {{ \Carbon\Carbon::parse($leave->start_date)->format('F j, Y') }}
                -
                {{ \Carbon\Carbon::parse($leave->end_date)->format('F j, Y') }}
            </div>
        </td>
        <td class="box-bottom" style="width:50%;">
            <div class="bold">6.D COMMUTATION</div>
            <div style="margin-top:4px;"><span class="chk">{{ $checked(($leave->commutation ?? '') === 'not_requested') }}</span> Not Requested</div>
            <div><span class="chk">{{ $checked(($leave->commutation ?? '') === 'requested') }}</span> Requested</div>
            <div class="sig-line" style="width:100%; margin-top:16px;"></div>
            <div class="center small">(Signature of Applicant)</div>
        </td>
    </tr>

    <tr><td colspan="2" class="section-title">7. DETAILS OF ACTION ON APPLICATION</td></tr>

    {{-- 7A / 7B --}}
    <tr>
        <td class="box-right box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold">7.A CERTIFICATION OF LEAVE CREDITS</div>
            <div style="margin-top:3px;">As of
                <span class="line" style="width:130px;">{{ optional($leave->certification_as_of)->format('F j, Y') }}</span>
            </div>
            <table style="margin-top:5px; border:1px solid #000;">
                <tr class="bold" style="border-bottom:1px solid #000;">
                    <td style="border-right:1px solid #000; padding:2px 6px;"></td>
                    <td style="border-right:1px solid #000; padding:2px 6px;">Vacation Leave</td>
                    <td style="padding:2px 6px;">Sick Leave</td>
                </tr>
                <tr style="border-bottom:1px solid #000;">
                    <td style="border-right:1px solid #000; padding:2px 6px;">Total Earned</td>
                    <td style="border-right:1px solid #000; padding:2px 6px;">{{ $leave->vacation_total_earned ?? '' }}</td>
                    <td style="padding:2px 6px;">{{ $leave->sick_total_earned ?? '' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #000;">
                    <td style="border-right:1px solid #000; padding:2px 6px;">Less this application</td>
                    <td style="border-right:1px solid #000; padding:2px 6px;">{{ $leave->vacation_less_application ?? '' }}</td>
                    <td style="padding:2px 6px;">{{ $leave->sick_less_application ?? '' }}</td>
                </tr>
                <tr>
                    <td style="border-right:1px solid #000; padding:2px 6px;">Balance</td>
                    <td style="border-right:1px solid #000; padding:2px 6px;">{{ $leave->vacation_balance ?? '' }}</td>
                    <td style="padding:2px 6px;">{{ $leave->sick_balance ?? '' }}</td>
                </tr>
            </table>
            <div class="sig-line" style="width:100%; margin-top:14px;"></div>
            <div class="center">(Authorized Officer)</div>
        </td>

        <td class="box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold">7.B RECOMMENDATION</div>
            <div style="margin-top:3px;"><span class="chk">{{ $checked(($leave->recommendation_status ?? '') === 'approved') }}</span> For approval</div>
            <div><span class="chk">{{ $checked(($leave->recommendation_status ?? '') === 'disapproved') }}</span> For disapproval due to
                <span class="line" style="width:150px;">{{ $leave->recommendation_reason ?? '' }}</span>
            </div>
            <div class="line" style="width:100%; margin-top:6px;">&nbsp;</div>
            <div class="line" style="width:100%; margin-top:6px;">&nbsp;</div>
            <div class="sig-line" style="width:100%; margin-top:14px;"></div>
            <div class="center bold small">Emily O. Benitez, EdD</div>
            <div class="center italic small">School Principal II</div>
        </td>
    </tr>

    {{-- 7C / 7D --}}
    <tr>
        <td class="box-right" style="width:50%;">
            <div class="bold">7.C APPROVED FOR:</div>
            <div style="margin-top:6px;">
                <span class="line" style="width:60px; display:inline-block;">{{ $leave->days_with_pay ?? '' }}</span>
                days with pay
            </div>
            <div style="margin-top:4px;">
                <span class="line" style="width:60px; display:inline-block;">{{ $leave->days_without_pay ?? '' }}</span>
                days without pay
            </div>
            <div style="margin-top:4px;">
                <span class="line" style="width:60px; display:inline-block;"></span>
                others (Specify)
                <span class="line" style="width:100px; display:inline-block;">{{ $leave->other_approval ?? '' }}</span>
            </div>
        </td>

        <td style="width:50%;">
            <div class="bold">7.D DISAPPROVED DUE TO:</div>
            <div class="line" style="width:100%; margin-top:8px;">{{ $leave->disapproval_reason ?? '' }}</div>
            <div class="line" style="width:100%; margin-top:6px;">&nbsp;</div>
        </td>
    </tr>

    <tr>
        <td colspan="2" class="center box-top" style="padding-top:12px; padding-bottom:6px;">
            <div class="sig-line" style="width:250px; margin:0 auto 3px;"></div>
            <div class="small">Schools Division Superintendent</div>
        </td>
    </tr>
</table>

</body>
</html>