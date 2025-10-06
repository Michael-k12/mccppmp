<x-layouts.app :title="'Annual Project Plan'">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .top-bar h2 {
            font-size: 20px;
            font-weight: bold;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-form select {
            padding: 6px 10px;
            font-size: 14px;
        }

        .print-button,
        .delete-button {
            background-color: #2563eb;
            color: white;
            padding: 6px 12px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .print-button:hover {
            background-color: #1d4ed8;
        }

        .delete-button {
            background-color: #dc2626;
        }

        .delete-button:hover {
            background-color: #b91c1c;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 1rem;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #cbd5e0;
            padding: 6px 10px;
            background-color: white;
        }

        .excel-table th {
            background-color: rgb(154, 169, 255);
            text-transform: uppercase;
            font-size: 11px;
            font-weight: bold;
        }

        .excel-table tr:hover td {
            background-color: #f9fafb;
        }

        .total-row {
            font-weight: bold;
            background-color: #f1f5f9;
        }

        .no-data {
            text-align: center;
            font-style: italic;
        }
    </style>

    <!-- ✅ Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container">
        <div class="top-bar">
            <h2>Annual Procurement Plan</h2>
            <div class="filter-form">
                <form id="yearForm" action="{{ route('ppmp.approved') }}" method="GET">
                    <select id="yearSelect" name="year" onchange="this.form.submit()">
                        <option value="">-- Select Year --</option>
                        @foreach ($availableYears as $yearOption)
                            <option value="{{ $yearOption }}" {{ request('year') == $yearOption ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- ✅ Download PDF -->
                <a href="{{ route('ppmp.download.pdf', ['year' => request('year')]) }}" class="print-button">
                    Download PDF
                </a>

                <!-- 🗑️ Delete selected year -->
                <form id="deleteYearForm" action="{{ route('ppmp.delete.year') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="year" id="deleteYear" value="{{ request('year') }}">
                    <button type="button" class="delete-button" id="deleteButton">Delete Year</button>
                </form>
            </div>
        </div>

        @php $grandTotal = 0; @endphp

        <table class="excel-table">
            <thead>
                <tr>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Estimated Budget</th>
                    <th>Mode Of Procurement</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ppmps->sortByDesc('created_at')->groupBy('classification') as $classification => $group)
                    @foreach ($group as $ppmp)
                        @php $grandTotal += $ppmp->estimated_budget; @endphp
                        <tr>
                            <td>{{ strtoupper($ppmp->classification) }}</td>
                            <td>{{ $ppmp->description }}</td>
                            <td>{{ $ppmp->unit }}</td>
                            <td>{{ number_format($ppmp->price, 2) }}</td>
                            <td>{{ $ppmp->quantity }}</td>
                            <td>{{ number_format($ppmp->estimated_budget, 2) }}</td>
                            <td>{{ $ppmp->mode_of_procurement }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="no-data">No approved Project Plan found.</td>
                    </tr>
                @endforelse

                <tr class="total-row">
                    <td colspan="5">TOTAL</td>
                    <td>{{ number_format($grandTotal, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('deleteButton').addEventListener('click', function() {
            const selectedYear = document.getElementById('yearSelect').value;
            const form = document.getElementById('deleteYearForm');

            if (!selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Year Selected',
                    text: 'Please select a year before deleting.',
                });
                return;
            }

            Swal.fire({
                title: `Are you sure you want to delete all PPMP records for ${selectedYear}?`,
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
</x-layouts.app>
