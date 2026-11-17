<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">

    <style>

        body {
            font-family: solaimanlipi;
            font-size: 12pt;
        }

        h2,
        h3,
        th,
        td {
            font-family: solaimanlipi;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 7px;
        }

        th {
            font-weight: bold;
            text-align: center;
        }

        td.center {
            text-align: center;
        }

    </style>
</head>

<body>

<div style="text-align:center;">

    <h2>শিশু দিবাযত্ন কেন্দ্র</h2>

    <h3>উপস্থিতি শীট</h3>

    <p>
        তারিখ:
        {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
    </p>

</div>

<table>

    <thead>
    <tr>
        <th>ক্রমিক</th>
        <th>শিশুর নাম</th>
        <th>প্রবেশের সময়</th>
        <th>প্রস্থানের সময়</th>
        <th>অবস্থা</th>
    </tr>
    </thead>

    <tbody>

    @forelse($children as $index => $child)

        @php
            $attendance = $child->attendances->first();
        @endphp

        <tr>

            <td class="center">
                {{ $index + 1 }}
            </td>

            <td>
                {{ $child->name }}
            </td>

            <td class="center">
                @if($attendance && $attendance->check_in)
                    {{ \Carbon\Carbon::parse($attendance->check_in, 'UTC')
                        ->setTimezone('Asia/Dhaka')
                        ->format('g:i A') }}
                @else
                    —
                @endif
            </td>

            <td class="center">
                @if($attendance && $attendance->check_out)
                    {{ \Carbon\Carbon::parse($attendance->check_out, 'UTC')
                        ->setTimezone('Asia/Dhaka')
                        ->format('g:i A') }}
                @else
                    —
                @endif
            </td>

            <td class="center">

                @if(!$attendance)
                    অনুপস্থিত
                @elseif($attendance->check_in && $attendance->check_out)
                    উপস্থিত
                @elseif($attendance->check_in)
                    প্রবেশ করেছে
                @else
                    অনুপস্থিত
                @endif

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="5" class="center">
                কোনো সক্রিয় শিশু পাওয়া যায়নি।
            </td>
        </tr>

    @endforelse

    </tbody>

</table>

</body>
</html>