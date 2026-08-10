<h2>Attendance Report</h2>

@foreach($attendance as $att)
    <p>
        {{ $att->employee->first_name }}
        -
        {{ $att->attendance_date }}
        -
        {{ $att->status }}
    </p>
@endforeach