@extends('pdf.layout')
@section('report_title', 'Instrument Cost Report')
@section('content')
<table class="items">
<thead><tr><th>SL#</th><th>Instrument</th><th>Category</th><th>Condition</th><th>Purchase Date</th><th class="r">Qty</th><th class="r">Unit Price</th><th class="r">Total Cost</th></tr></thead>
<tbody>
@php $g = 0; $q = 0; @endphp
@forelse($instruments as $i => $ins)
@php $line = $ins->quantity * $ins->purchase_price; $g += $line; $q += $ins->quantity; @endphp
<tr><td class="c">{{ $i+1 }}</td><td>{{ $ins->name }}</td><td>{{ $ins->category ?? '—' }}</td><td>{{ ucfirst($ins->condition) }}</td><td>{{ $ins->purchase_date ? \Carbon\Carbon::parse($ins->purchase_date)->format('d-m-Y') : '—' }}</td><td class="r">{{ $ins->quantity }}</td><td class="r">{{ number_format($ins->purchase_price,2) }}</td><td class="r">{{ number_format($line,2) }}</td></tr>
@empty
<tr><td colspan="8" class="c">No instruments found.</td></tr>
@endforelse
<tr class="total"><td colspan="5" class="r">Total</td><td class="r">{{ $q }}</td><td></td><td class="r">{{ number_format($g,2) }}</td></tr>
</tbody></table>
@endsection
