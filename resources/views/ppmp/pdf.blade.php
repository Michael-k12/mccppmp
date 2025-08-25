<!DOCTYPE html>
<html>
<head>
    <title>Anual Procurement Plan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.8in;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            font-family: "Times New Roman", Times, serif;
            font-size: 10px;
            font-weight: normal;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .header-table {
            margin-bottom: 5px;
        }

        .header-table td {
            border: none !important;
        }

        .header-text {
            text-align: center;
            line-height: 1.4;
            font-size: 13px;
        }

        .header-title {
            font-weight: bold;
            font-size: 15px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
        }

        .sub-section {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .green-row {
            background-color: rgb(70, 255, 141);
            font-weight: bold;
            font-size: 8px;
        }

        .yellow {
            background-color: rgb(255, 217, 63);
        }

        .border-right {
            border-right: 1px solid #000 !important;
        }

        .no-border-table td {
            border: none !important;
            padding-top: 40px;
        }
    </style>
</head>
<body>

@php
    function nf($n) {
        return floor($n) == $n ? number_format($n, 0) : number_format($n, 2);
    }
@endphp

<div style="width: 100%; text-align: center;">
    <div style="display: table; width: 50%; margin: 0 auto; margin-bottom: 4px;">
        <div style="display: table-cell; width: 15%; text-align: left;"></div>
        <div style="display: table-cell; width: 70%; text-align: center;">
            <div style="font-size: 10px;">Republic of the Philippines</div>
            <div style="font-size: 10px;">Province of Cebu</div>
            <div style="font-weight: bold; font-size: 11px;">Municipality of Madridejos</div>
        </div>
        <div style="display: table-cell; width: 15%; text-align: right;"></div>
    </div>
</div>

<div class="section-title">
    PROJECT PROCUREMENT MANAGEMENT PLAN
    {{ isset($year) ? $year : ($ppmps->first()->milestone_date ? \Carbon\Carbon::parse($ppmps->first()->milestone_date)->format('Y') : 'YEAR') }}
</div>
<div class="sub-section">COMMON USE SUPPLIES, EQUIPMENT & OTHERS</div>

<div style="position: relative; margin: 10px 0; font-size: 10px;">
    End-User/Unit/Department: 
    <span style="margin-left: 100px; font-weight: bold;">Madridejos Community College</span>
    <span style="position: absolute; left: 62%; top: 0;">
        Fund: <u><strong>GENERAL</strong></u>
    </span>
</div>

<table>
    <tbody>
        <tr>
            <td rowspan="2">Code</td>
            <td rowspan="2">General Description</td>
            <td rowspan="2">Unit</td>
            <td rowspan="2">Price</td>
            <td rowspan="2">Qty</td>
            <td rowspan="2">Estimated Budget</td>
            <td rowspan="2">Mode of Procurement</td>
            <td colspan="12">Schedule/Milestone of Activities</td>
            <td rowspan="2" class="border-right">Total</td>
        </tr>
        <tr>
            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $m)
                <td style="width: 3%;">{{ $m }}</td>
            @endforeach
        </tr>

        @php $i = 1; $grand = 0; @endphp
        @foreach ($ppmps->groupBy('classification') as $classification => $group)
            <tr>
                <td colspan="2" class="green-row" style="text-align: left; border-right: none;">
                    {{ strtoupper($classification) }}
                </td>
                @for ($col = 3; $col <= 20; $col++)
                    <td class="green-row"></td>
                @endfor
            </tr>
            @foreach ($group as $ppmp)
                @php
                    $month = \Carbon\Carbon::parse($ppmp->milestone_date)->format('n');
                    $grand += $ppmp->estimated_budget;
                @endphp
                <tr>
                    <td>{{ $i++ }}</td>
                    <td style="text-align: left">{{ $ppmp->description }}</td>
                    <td>{{ $ppmp->unit }}</td>
                    <td>{{ nf($ppmp->price) }}</td>
                    <td>{{ $ppmp->quantity }}</td>
                    <td>{{ nf($ppmp->estimated_budget) }}</td>
                    <td>{{ $ppmp->mode_of_procurement }}</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="{{ $m == $month ? 'yellow' : '' }}"></td>
                    @endfor
                    <td class="border-right">{{ nf($ppmp->estimated_budget) }}</td>
                </tr>

                @if ($loop->last)
                    <tr>
                        @for ($col = 1; $col <= 20; $col++)
                            <td>&nbsp;</td>
                        @endfor
                    </tr>
                @endif
            @endforeach
        @endforeach

        <tr style="font-weight: bold; background-color: #e5e7eb;">
            <td></td>
            <td style="text-align: left;">GRAND TOTAL</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ nf($grand) }}</td>
            <td></td>
            @for ($m = 1; $m <= 12; $m++)
                <td></td>
            @endfor
            <td class="border-right">{{ nf($grand) }}</td>
        </tr>
    </tbody>
</table>

<br><br>
<table class="no-border-table" style="width: 100%;">
    <tr>
        <td></td>
        <td style="text-align: left;">
            <strong>Prepared by:</strong><br>
            <u>_________________________</u><br>
        </td>
        <td colspan="5"></td>
        <td colspan="12" style="text-align: left;">
            <strong>Approved by:</strong><br>
            <u>_________________________</u><br>
        </td>
    </tr>
</table>

</body>
</html>
