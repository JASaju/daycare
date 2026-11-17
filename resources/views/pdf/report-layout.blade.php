<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        body { font-family: solaimanlipi, sans-serif; font-size: 10pt; color: #222; }
        h1 { font-size: 16pt; margin: 0; color: #2d3e50; }
        h2 { font-size: 11pt; margin: 16px 0 4px; color: #2d3e50; }
        .sub { color: #666; font-size: 9pt; }
        .right { text-align: right; }
        .center { text-align: center; }
        hr { border: 0; border-top: 2px solid #2d3e50; margin: 6px 0 8px; }
        .filters { font-size: 9pt; color: #444; margin-bottom: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th { background: #2d3e50; color: #fff; padding: 5px; font-size: 9pt; text-align: left; border: 1px solid #2d3e50; }
        table.data td { padding: 4px 5px; border: 1px solid #ccc; font-size: 9pt; vertical-align: top; }
        table.data tr.alt td { background: #f5f7fa; }
        table.data tr.total td { font-weight: bold; background: #e8ecf1; }
        .muted { color: #888; }
        .danger { color: #b00020; font-weight: bold; }
    </style>
</head>
<body>

<table width="100%">
    <tr>
        <td>
            <h1>@yield('title')</h1>
            <div class="sub">@yield('subtitle')</div>
        </td>
        <td class="right sub">
            Generated: {{ now()->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}<br>
            By: {{ auth()->user()->name }}
        </td>
    </tr>
</table>
<hr>

@if (!empty($filters))
    <div class="filters">
        <strong>Filters:</strong>
        @foreach ($filters as $label => $value)
            {{ $label }}: {{ ucfirst(str_replace('_', ' ', $value)) }}@if (!$loop->last) &nbsp;|&nbsp; @endif
        @endforeach
    </div>
@endif

@yield('content')

</body>
</html>
