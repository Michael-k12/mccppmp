<!DOCTYPE html>
<html>
<head>
    <title>Annual Procurement Plan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.8in;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            margin: 0; /* Remove extra margin */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            font-size: 10px;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .header-logo {
            height: 60px;
            width: auto;
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

<!-- Header -->
<div style="width: 100%; text-align: center;">
    <div style="display: table; width: 45%; margin: 0 auto; margin-bottom: 4px;">
        <div style="display: table-cell; width: 15%; text-align: left;">
            <img src="{{ public_path('images/bshm.jpg') }}" class="header-logo">
        </div>
        <div style="display: table-cell; width: 70%; text-align: center;">
            <div style="font-weight: bold; font-size: 15px;">Madridejos Community College</div>
            <div style="font-weight: bold; font-size: 15px;">Information Technology Department</div>
            <div style="font-weight: bold; font-size: 11px;">Crossing Bunakan, Madridejos, Cebu</div>
        </div>
        <div style="display: table-cell; width: 15%; text-align: right;">
            <img src="{{ public_path('images/logo-mcc.png') }}" class="header-logo">
        </div>
    </div>
</div>

<!-- Divider -->
<div style="border-top: 1px solid #000; width: 45%; margin: 4px auto 8px auto;"></div>

<!-- Title -->
<div class="section-title">
    PROJECT PROCUREMENT MANAGEMENT PLAN
    {{ isset($year) ? $year : ($ppmps->first()->milestone_date ? \Carbon\Carbon::parse($ppmps->first()->milestone_date)->format('Y') : 'YEAR') }}
</div>
<div class="sub-section">COMMON USE SUPPLIES, EQUIPMENT & OTHERS</div>

<!-- Department Info -->
<div style="position: relative; margin: 10px 0; font-size: 10px;">
    End-User/Unit/Department:
    <span style="margin-left: 100px; font-weight: bold;">BSHM DEPARTMENT</span>
    <span style="position: absolute; left: 62%; top: 0;">
        Fund: <u><strong>GENERAL</strong></u>
    </span>
</div>

<!-- Table -->
<table>
    <tbody>
        <tr>
            <td rowspan="2" style="width: 2%;">Code</td>
            <td rowspan="2" style="width: 17%;">General Description</td>
            <td rowspan="2" style="width: 5%;">Unit</td>
            <td rowspan="2" style="width: 5%;">Price</td>
            <td rowspan="2" style="width: 5%;">Qty</td>
            <td rowspan="2" style="width: 8%;">Estimated Budget</td>
            <td rowspan="2" style="width: 7%;">Mode of Procurement</td>
            <td colspan="12">Schedule/Milestone of Activities</td>
            <td rowspan="2" class="border-right" style="width: 10%;">Total</td>
        </tr>
        <tr>
            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $m)
                <td style="width: 3%;">{{ $m }}</td>
            @endforeach
        </tr>

        @php $i = 1; $grand = 0; @endphp
        @foreach ($ppmps->groupBy('classification') as $classification => $group)
            <tr>
                <td colspan="2" class="green-row" style="text-align: left;">{{ strtoupper($classification) }}</td>
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
                    <td style="text-align: left;">{{ $ppmp->description }}</td>
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

<!-- Signature -->
<br><br>
<table class="no-border-table" style="width: 100%;">
    <tr>
        <td></td>
        <td style="text-align: left;">
            <strong>Prepared by:</strong><br>
            <u>_________________________</u><br>
            Program Head, BSHM
        </td>
        <td colspan="5"></td>
        <td colspan="12" style="text-align: left;">
            <strong>Approved by:</strong><br>
            <u>_________________________</u><br>
            College President
        </td>
    </tr>
</table>

</body>
</html>
