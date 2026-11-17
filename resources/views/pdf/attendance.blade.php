@extends('pdf.layout')
@section('report_title', 'Attendance Report - ' . \Carbon\Carbon::parse($date)->format('d-m-Y'))
@section('content')
    <table class="items">
        <thead>
        <tr>
            <th>SL#</th><th>Child</th><th>Gender</th><th>Parent / Employee</th>
            <th>Check In</th><th>Check Out</th><th>Status</th>
        </tr>
        </thead>
        <tbody>
        @php $in = 0; $out = 0; @endphp
        @forelse($children as $i => $c)
            @php
                $a = $c->attendances->first();

                if ($a && $a->check_in)  $in++;
                if ($a && $a->check_out) $out++;

                // Stored times are UTC, show them in Asia/Dhaka (same as the attendance page)
                $checkIn  = $a && $a->check_in
                    ? \Carbon\Carbon::parse($a->check_in, 'UTC')->setTimezone('Asia/Dhaka')->format('h:i A')
                    : '—';
                $checkOut = $a && $a->check_out
                    ? \Carbon\Carbon::parse($a->check_out, 'UTC')->setTimezone('Asia/Dhaka')->format('h:i A')
                    : '—';

                $st = !$a || !$a->check_in ? 'Not checked in' : ($a->check_out ? 'Checked out' : 'Still inside');
            @endphp
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $c->name }}</td>
                <td>{{ ucfirst($c->gender ?? '—') }}</td>
                <td>{{ $c->employee->name ?? '—' }}</td>
                <td>{{ $checkIn }}</td>
                <td>{{ $checkOut }}</td>
                <td>{{ $st }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="c">No records found.</td></tr>
        @endforelse
        <tr class="total">
            <td colspan="4" class="r">Total {{ $children->count() }} | Checked in {{ $in }} | Checked out {{ $out }}</td>
            <td colspan="3"></td>
        </tr>
        </tbody>
    </table>
@endsection