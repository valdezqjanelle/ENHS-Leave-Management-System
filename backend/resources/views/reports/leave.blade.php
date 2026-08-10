<h2>Leave Report</h2>

@foreach($leaves as $leave)
    <p>
        {{ $leave->employee->first_name ?? 'N/A' }}
        -
        {{ optional($leave->leaveType)->leave_type_name ?? 'N/A' }}
        -
        {{ $leave->final_status ?? 'N/A' }}
    </p>
@endforeach