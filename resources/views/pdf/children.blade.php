@extends('pdf.layout')
@section('report_title', 'Children Report')
@section('content')
<table class="items">
<thead><tr><th>SL#</th><th>Child</th><th>Gender</th><th>Date of Birth</th><th>Parent / Employee</th><th>Notes</th><th>Status</th></tr></thead>
<tbody>
@forelse($children as $i => $c)
<tr><td class="c">{{ $i+1 }}</td><td>{{ $c->name }}</td><td>{{ ucfirst($c->gender ?? '—') }}</td><td>{{ \Carbon\Carbon::parse($c->date_of_birth)->format('d-m-Y') }}</td><td>{{ $c->employee->name ?? '—' }}</td><td>{{ $c->notes }}</td><td>{{ ucfirst($c->status) }}</td></tr>
@empty
<tr><td colspan="7" class="c">No records found.</td></tr>
@endforelse
<tr class="total"><td colspan="6" class="r">Total Children</td><td>{{ $children->count() }}</td></tr>
</tbody></table>
@endsection
