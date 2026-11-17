@extends('pdf.layout')
@section('report_title', 'Invoices Report')
@section('content')
@php $tA = 0; $tP = 0; $tB = 0; @endphp
<table class="items">
<thead><tr><th>SL#</th><th>Parent / Employee</th><th>Child</th><th>Period</th><th>Due Date</th><th>Status</th><th class="r">Amount</th><th class="r">Paid</th><th class="r">Balance</th></tr></thead>
<tbody>
@forelse($invoices as $i => $inv)
@php $tA += $inv->amount; $tP += $inv->paid_amount; $tB += $inv->balance; @endphp
<tr><td class="c">{{ $i+1 }}</td><td>{{ $inv->employee->name ?? '—' }}</td><td>{{ $inv->child->name ?? '—' }}</td><td>{{ $inv->month }} {{ $inv->year }}</td><td>{{ $inv->due_date ? $inv->due_date->format('d-m-Y') : '—' }}</td><td>{{ ucfirst($inv->status) }}</td><td class="r">{{ number_format($inv->amount,2) }}</td><td class="r">{{ number_format($inv->paid_amount,2) }}</td><td class="r">{{ number_format($inv->balance,2) }}</td></tr>
@empty
<tr><td colspan="9" class="c">No records found.</td></tr>
@endforelse
<tr class="total"><td colspan="6" class="r">Grand Total (BDT)</td><td class="r">{{ number_format($tA,2) }}</td><td class="r">{{ number_format($tP,2) }}</td><td class="r">{{ number_format($tB,2) }}</td></tr>
</tbody></table>
@endsection
