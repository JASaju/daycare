@extends('pdf.layout')
@section('report_title', 'Staff Report')
@section('content')
<table class="items">
<thead><tr><th>SL#</th><th>Name</th><th>Position</th><th>Phone</th><th>Email</th><th>Hire Date</th><th>Status</th></tr></thead>
<tbody>
@forelse($staff as $i => $s)
<tr><td class="c">{{ $i+1 }}</td><td>{{ $s->name }}</td><td>{{ $s->position }}</td><td>{{ $s->phone }}</td><td>{{ $s->email }}</td><td>{{ $s->hire_date ? \Carbon\Carbon::parse($s->hire_date)->format('d-m-Y') : '—' }}</td><td>{{ ucfirst($s->status) }}</td></tr>
@empty
<tr><td colspan="7" class="c">No records found.</td></tr>
@endforelse
<tr class="total"><td colspan="6" class="r">Total Staff</td><td>{{ $staff->count() }}</td></tr>
</tbody></table>
@endsection
