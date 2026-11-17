@extends('pdf.layout')
@section('report_title', 'Employees Report')
@section('content')
<table class="items">
<thead><tr><th>SL#</th><th>Code</th><th>Name</th><th>Department</th><th>Designation</th><th>Phone</th><th>Email</th><th class="r">Children</th></tr></thead>
<tbody>
@forelse($employees as $i => $e)
<tr><td class="c">{{ $i+1 }}</td><td>{{ $e->employee_code }}</td><td>{{ $e->name }}</td><td>{{ $e->department }}</td><td>{{ $e->designation }}</td><td>{{ $e->phone }}</td><td>{{ $e->email }}</td><td class="r">{{ $e->children_count }}</td></tr>
@empty
<tr><td colspan="8" class="c">No records found.</td></tr>
@endforelse
<tr class="total"><td colspan="7" class="r">Total Employees / Children</td><td class="r">{{ $employees->count() }} / {{ $employees->sum('children_count') }}</td></tr>
</tbody></table>
@endsection
