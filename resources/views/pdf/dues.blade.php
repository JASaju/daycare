@extends('pdf.layout')
@section('report_title', 'Dues Report')
@section('section_title', 'Summary by Parent / Employee')
@section('content')
<table class="items">
<thead><tr><th>SL#</th><th>Parent / Employee</th><th class="r">Invoices</th><th class="r">Amount</th><th class="r">Paid</th><th class="r">Balance</th></tr></thead>
<tbody>
@php $n = 0; @endphp
@foreach($byEmployee as $row)
<tr><td class="c">{{ ++$n }}</td><td>{{ $row['name'] }}</td><td class="r">{{ $row['count'] }}</td><td class="r">{{ number_format($row['amount'],2) }}</td><td class="r">{{ number_format($row['paid'],2) }}</td><td class="r">{{ number_format($row['balance'],2) }}</td></tr>
@endforeach
</tbody></table>

<h4>Due Invoices</h4>
<table class="items">
<thead><tr><th>SL#</th><th>Parent / Employee</th><th>Child</th><th>Period</th><th>Due Date</th><th>Status</th><th class="r">Amount</th><th class="r">Paid</th><th class="r">Balance</th></tr></thead>
<tbody>
@forelse($invoices as $i => $inv)
<tr><td class="c">{{ $i+1 }}</td><td>{{ $inv->employee->name ?? '—' }}</td><td>{{ $inv->child->name ?? '—' }}</td><td>{{ $inv->month }} {{ $inv->year }}</td><td>{{ $inv->due_date ? $inv->due_date->format('d-m-Y') : '—' }}</td><td>{{ ucfirst($inv->status) }}</td><td class="r">{{ number_format($inv->amount,2) }}</td><td class="r">{{ number_format($inv->paid_amount,2) }}</td><td class="r">{{ number_format($inv->balance,2) }}</td></tr>
@empty
<tr><td colspan="9" class="c">No dues.</td></tr>
@endforelse
<tr class="total"><td colspan="6" class="r">Grand Total (BDT)</td><td class="r">{{ number_format($invoices->sum('amount'),2) }}</td><td class="r">{{ number_format($invoices->sum('paid_amount'),2) }}</td><td class="r">{{ number_format($invoices->sum('balance'),2) }}</td></tr>
</tbody></table>
@endsection
