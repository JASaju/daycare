<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: solaimanlipi; font-size: 9pt; color: #111; }
        table { border-collapse: collapse; width: 100%; }
        .meta td { border: 1px solid #000; padding: 4px 6px; font-size: 8.5pt; }
        .meta td.lbl { font-weight: bold; width: 18%; background: #fafafa; }
        .items th { border: 1px solid #000; padding: 4px 5px; font-size: 8pt; text-align: left; background: #f2f2f2; }
        .items td { border: 1px solid #000; padding: 3px 5px; font-size: 8pt; }
        .items tr.total td { font-weight: bold; background: #f7f7f7; }
        .r { text-align: right; }
        .c { text-align: center; }
        .sign td { text-align: center; font-size: 8pt; padding-top: 6px; }
        .line { border-top: 1px dashed #000; width: 70%; margin: 0 auto; }
        h4 { margin: 10px 0 4px 0; font-size: 10pt; }
    </style>
</head>
<body>

<table>
    <tr>
        <td style="width:30%;vertical-align:top;text-align:center;">
            <barcode code="{{ $code }}" type="C128A" height="0.9" size="0.9" />
            <div style="font-size:10px;letter-spacing:1px;">{{ $code }}</div>
        </td>
        <td style="width:40%;text-align:center;">
            <div style="font-size:13pt;font-weight:bold;">Daycare Center</div>
            <div style="font-size:10pt;color:#444;">@yield('report_title')</div>
        </td>
        <td style="width:30%;text-align:right;font-weight:bold;font-size:10pt;">DAYCARE SYSTEM</td>
    </tr>
</table>

<br>

<table class="meta">
    <tr>
        <td class="lbl">Code</td>
        <td><b>{{ $code }}</b></td>
        <td class="lbl">Generated Date</td>
        <td>{{ now('Asia/Dhaka')->format('d-m-Y h:i A') }}</td>
    </tr>
    <tr>
        <td class="lbl">Prepared By</td>
        <td>{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</td>
        <td class="lbl">Filters</td>
        <td>
            @forelse($filters ?? [] as $label => $value)
                {{ $label }}: <b>{{ $value }}</b>@if(!$loop->last); @endif
            @empty
                None
            @endforelse
        </td>
    </tr>
</table>

<h4>@yield('section_title', 'Items')</h4>

@yield('content')

<br><br>

<table class="sign">
    <tr>
        <td style="width:33%;">
            Prepared By<br><br>
            {{ now('Asia/Dhaka')->format('d-m-Y') }}<br>
            {{ auth()->user()->name }}<br>
            {{ ucfirst(auth()->user()->role) }}
        </td>
        <td style="width:33%;">
            Authorized By<br><br><br>
            <div class="line"></div>
            Authorized Officer
        </td>
        <td style="width:33%;">
            Verified By<br><br><br>
            <div class="line"></div>

        </td>
    </tr>
</table>

<br>
<table>
    <tr>
        <td class="c">
            <barcode code="{{ $qrUrl }}" type="QR" class="barcode" size="0.9" error="M" disableborder="1" />
        </td>
    </tr>
</table>

</body>
</html>