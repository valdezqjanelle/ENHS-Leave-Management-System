<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 10mm; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .outer {
        border: 1px solid #000;
    }

    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding: 8px 0;
    }

    .header h1 {
        font-size: 15px;
        margin-top: 8px;
    }

    .section-title {
        text-align: center;
        font-weight: bold;
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
        padding: 4px 0;
        font-size: 12px;
    }

    td, th {
        vertical-align: top;
        padding: 6px;
    }

    .bold { font-weight: bold; }
    .center { text-align: center; }
    .small { font-size: 9px; }
    .line { border-bottom: 1px solid #000; min-height: 14px; padding: 2px 4px; }
    .box-right { border-right: 1px solid #000; }
    .box-bottom { border-bottom: 1px solid #000; }
    .box-top { border-top: 1px solid #000; }
    .checkbox { display: inline-block; width: 10px; }
    .sig-line { border-bottom: 1px solid #000; height: 20px; margin-top: 20px; }
</style>
</head>
<body>

<table class="outer">
    <tr>
        <td colspan="2" class="header">
            Civil Service Form No. 6<br>
            Revised 2020<br><br>
            Republic of the Philippines<br>
            Department of Education<br>
            Region II &ndash; Cagayan Valley<br>
            Schools Division of Isabela
            <h1>APPLICATION FOR LEAVE</h1>
        </td>
    </tr>

    {{-- 1 & 2 --}}
    <tr>
        <td class="box-right box-bottom" style="width:33%;">
            <div class="bold">1. OFFICE / DEPARTMENT</div>
            <div class="line" style="margin-top:16px;">{{ $leave->employee->department ?? '' }}</div>
        </td>
        <td class="box-bottom">
            <div class="bold">2. NAME</div>
            <table>
                <tr>
                    <td style="width:33%;">
                        <div class="line">{{ $leave->employee->last_name ?? '' }}</div>
                        <div class="center small">(Last)</div>
                    </td>
                    <td style="width:33%;">
                        <div class="line">{{ $leave->employee->first_name ?? '' }}</div>
                        <div class="center small">(First)</div>
                    </td>
                    <td style="width:33%;">
                        <div class="line">{{ $leave->employee->middle_name ?? '' }}</div>
                        <div class="center small">(Middle)</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- 3, 4, 5 --}}
    <tr>
        <td class="box-right box-bottom" style="width:33%;">
            <div class="bold">3. DATE OF FILING</div>
            <div class="line" style="margin-top:16px;">{{ \Carbon\Carbon::parse($leave->date_filed)->format('F j, Y') }}</div>
        </td>
        <td class="box-bottom" colspan="2">
            <table>
                <tr>
                    <td class="box-right" style="width:50%;">
                        <div class="bold">4. POSITION</div>
                        <div class="line" style="margin-top:16px;">{{ $leave->employee->position ?? '' }}</div>
                    </td>
                    <td style="width:50%;">
                        <div class="bold">5. SALARY</div>
                        <div class="line" style="margin-top:16px;">&#8369;{{ $leave->employee->salary ?? '' }}</div>
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
            <table style="margin-top:8px;">
                @foreach ($leaveTypes as $type)
                <tr>
                    <td style="width:16px; padding:2px;">
                        <span class="checkbox">{{ $type->leave_type_id == $leave->leave_type_id ? '&#9745;' : '&#9744;' }}</span>
                    </td>
                    <td style="padding:2px;">{{ $type->leave_type_name }}</td>
                </tr>
                @endforeach
            </table>
            <div class="bold" style="margin-top:16px; font-style:italic;">Others:</div>
            <div class="line">{{ $leave->other_purpose ?? '' }}</div>
        </td>

        <td class="box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold center box-bottom" style="padding-bottom:6px;">6.B DETAILS OF LEAVE</div>

            <div style="margin-top:8px;">
                <div class="bold">In case of Vacation Leave:</div>
                <div>&#9744; Within the Philippines
                    <span class="line" style="display:inline-block; width:120px;">
                        {{ ($leave->vacation_location_type ?? '') === 'within_philippines' ? $leave->vacation_location : '' }}
                    </span>
                </div>
                <div>&#9744; Abroad (Specify)
                    <span class="line" style="display:inline-block; width:120px;">
                        {{ ($leave->vacation_location_type ?? '') === 'abroad' ? $leave->vacation_location : '' }}
                    </span>
                </div>
            </div>

            <div style="margin-top:8px;">
                <div class="bold">In case of Sick Leave:</div>
                <div>&#9744; In Hospital
                    <span class="line" style="display:inline-block; width:120px;">
                        {{ ($leave->sick_type ?? '') === 'in_hospital' ? $leave->illness : '' }}
                    </span>
                </div>
                <div>&#9744; Out Patient
                    <span class="line" style="display:inline-block; width:120px;">
                        {{ ($leave->sick_type ?? '') === 'out_patient' ? $leave->illness : '' }}
                    </span>
                </div>
            </div>

            <div style="margin-top:8px;">
                <div class="bold">In case of Special Leave Benefits for Women:</div>
                <div class="line">{{ $leave->special_leave_women ?? '' }}</div>
            </div>

            <div style="margin-top:8px;">
                <div class="bold">In case of Study Leave:</div>
                <div>{!! $leave->masters_degree ? '&#9745;' : '&#9744;' !!} Completion of Master's Degree</div>
                <div>{!! $leave->board_exam_review ? '&#9745;' : '&#9744;' !!} BAR / Board Examination Review</div>
            </div>

            <div style="margin-top:8px;">
                <div class="bold">Other Purpose:</div>
                <div>{!! $leave->monetization ? '&#9745;' : '&#9744;' !!} Monetization of Leave Credits</div>
                <div>{!! $leave->terminal_leave ? '&#9745;' : '&#9744;' !!} Terminal Leave</div>
                <div class="line">{{ $leave->other_purpose ?? '' }}</div>
            </div>
        </td>
    </tr>

    {{-- 6C / 6D --}}
    <tr>
        <td class="box-right box-bottom" style="width:50%;">
            <div class="bold">6.C NUMBER OF WORKING DAYS APPLIED FOR</div>
            <div class="line" style="margin-top:12px;">{{ $leave->number_of_days ?? '' }}</div>
            <div class="center small">Working Day(s)</div>
            <div class="bold" style="margin-top:16px;">Inclusive Dates</div>
            <div class="line">
                {{ \Carbon\Carbon::parse($leave->start_date)->format('F j, Y') }}
                -
                {{ \Carbon\Carbon::parse($leave->end_date)->format('F j, Y') }}
            </div>
        </td>
        <td class="box-bottom" style="width:50%;">
            <div class="bold">6.D COMMUTATION</div>
            <div style="margin-top:12px;">{!! ($leave->commutation ?? '') === 'requested' ? '&#9745;' : '&#9744;' !!} Requested</div>
            <div>{!! ($leave->commutation ?? '') === 'not_requested' ? '&#9745;' : '&#9744;' !!} Not Requested</div>
            <div class="sig-line" style="margin-top:30px;"></div>
            <div class="center small">Signature of Applicant</div>
        </td>
    </tr>

    <tr><td colspan="2" class="section-title">7. DETAILS OF ACTION ON APPLICATION</td></tr>

    {{-- 7A / 7B / 7C / 7D --}}
    <tr>
        <td class="box-right box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold box-bottom" style="padding-bottom:4px;">7.A CERTIFICATION OF LEAVE CREDITS</div>
            <table style="margin-top:6px;">
                <tr class="bold">
                    <td></td>
                    <td>Vacation Leave</td>
                    <td>Sick Leave</td>
                </tr>
                <tr>
                    <td>Total Earned</td>
                    <td>{{ $leave->vacation_total_earned ?? '' }}</td>
                    <td>{{ $leave->sick_total_earned ?? '' }}</td>
                </tr>
                <tr>
                    <td>Less this application</td>
                    <td>{{ $leave->vacation_less_application ?? '' }}</td>
                    <td>{{ $leave->sick_less_application ?? '' }}</td>
                </tr>
                <tr>
                    <td>Balance</td>
                    <td>{{ $leave->vacation_balance ?? '' }}</td>
                    <td>{{ $leave->sick_balance ?? '' }}</td>
                </tr>
            </table>
            <div class="sig-line" style="margin-top:30px;"></div>
            <div class="center bold">Human Resource Management Officer</div>
        </td>

        <td class="box-bottom" style="width:50%; vertical-align:top;">
            <div class="bold box-bottom" style="padding-bottom:4px;">7.B RECOMMENDATION</div>
            <div style="margin-top:6px;">{!! ($leave->recommendation_status ?? '') === 'approved' ? '&#9745;' : '&#9744;' !!} For Approval</div>
            <div>{!! ($leave->recommendation_status ?? '') === 'disapproved' ? '&#9745;' : '&#9744;' !!} For Disapproval due to:</div>
            <div class="line">{{ $leave->recommendation_reason ?? '' }}</div>
            <div class="sig-line" style="margin-top:20px;"></div>
            <div class="center bold">Immediate Supervisor</div>

            <div class="box-top" style="margin-top:10px; padding-top:6px;">
                <div class="bold">7.C APPROVED FOR</div>
                <div>________ {{ $leave->days_with_pay ?? '' }} day(s) with pay</div>
                <div>________ {{ $leave->days_without_pay ?? '' }} day(s) without pay</div>
                <div>Others:
                    <span class="line" style="display:inline-block; width:120px;">{{ $leave->other_approval ?? '' }}</span>
                </div>
            </div>

            <div class="box-top" style="margin-top:10px; padding-top:6px;">
                <div class="bold">7.D DISAPPROVED DUE TO</div>
                <div class="line">{{ $leave->disapproval_reason ?? '' }}</div>
                <div class="sig-line" style="margin-top:20px;"></div>
                <div class="center bold">Schools Division Superintendent / Approving Authority</div>
            </div>
        </td>
    </tr>
</table>

</body>
</html>
