<x-layouts.app :title="'BSIT PPMP'">
    <h1 class="text-2xl font-bold mb-4"></h1>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('ppmp.bsit') }}" class="mb-4 inline-block">
        <label for="year" class="text-sm font-medium">Archive:</label>
        <select name="year" id="year" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-sm ml-2">
            <option value=""> All Years </option>
            @foreach ($availableYears as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
    </form>

    <!-- Download Button -->
    <a href="{{ route('ppmp.bsit.download', ['year' => $selectedYear]) }}" class="download-btn">Download PDF</a>

    <!-- Styles -->
    <style>
        .download-btn {
            display: inline-block;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #2563eb;
            color: white;
            border-radius: 0.375rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .download-btn:hover {
            background-color: #1d4ed8;
        }

        /* Excel Style Table */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-top: 1rem;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #cbd5e0;
            padding: 6px 10px;
            background-color: white;
        }

        .excel-table th {
            background-color:rgb(154, 169, 255);
            text-transform: uppercase;
            font-size: 11px;
            font-weight: bold;
        }

        .excel-table tr:hover td {
            background-color: #f9fafb;
        }

        .text-center {
            text-align: center;
        }
    </style>

    <!-- Excel-style Table -->
    <table class="excel-table">
        <thead>
            <tr>
                <th>Classification</th>
                <th>General Description</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Estimated Budget</th>
                <th>Mode Of Procurement</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bsitPpmps as $ppmp)
                <tr>
                    <td>{{ $ppmp->classification }}</td>
                    <td>{{ $ppmp->description }}</td>
                    <td>{{ $ppmp->unit }}</td>
                    <td>{{ number_format($ppmp->price, 2) }}</td>
                    <td>{{ $ppmp->quantity }}</td>
                    <td>{{ number_format($ppmp->estimated_budget, 2) }}</td>
                    <td>{{ $ppmp->mode_of_procurement }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No BSIT PPMPs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-layouts.app>
