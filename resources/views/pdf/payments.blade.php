@extends('pdf.layout')
@section('report_title', 'Payments Report')
@section('content')
<table class="items">
<thead><tr><th>SL#</th><th>Paid Date</th><th>Parent / Employee</th><th>Child</th><th>Invoice</th><th>Method</th><th>Reference</th><th class="r">Amount</th></tr></thead>
<tbody>
@forelse($payments as $i => $p)
<tr><td class="c">{{ $i+1 }}</td><td>{{ $p->paid_date->format('d-m-Y') }}</td><td>{{ $p->invoice->employee->name ?? '—' }}</td><td>{{ $p->invoice->child->name ?? '—' }}</td><td>#{{ $p->invoice_id }} ({{ $p->invoice->month ?? '' }} {{ $p->invoice->year ?? '' }})</td><td>{{ ucwords(str_replace('_',' ',$p->method)) }}</td><td>{{ $p->reference ?? '—' }}</td><td class="r">{{ number_format($p->amount,2) }}</td></tr>
@empty
<tr><td colspan="8" class="c">No payments found.</td></tr>
@endforelse
<tr class="total"><td colspan="7" class="r">Grand Total (BDT)</td><td class="r">{{ number_format($payments->sum('amount'),2) }}</td></tr>
</tbody></table>
@endsection
