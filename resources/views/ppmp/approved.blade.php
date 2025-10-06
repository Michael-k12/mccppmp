<x-layouts.app :title="'Annual Project Plan'">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <div class="container">
        <div class="top-bar">
            <h2>Annual Procurement Plan</h2>
            <div class="filter-form">
                <form action="{{ route('ppmp.approved') }}" method="GET">
                    <select name="year" onchange="this.form.submit()">
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
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <button type="button" id="deleteButton" class="delete-button">Delete Year</button>
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
        document.getElementById('deleteButton').addEventListener('click', function () {
            const year = "{{ request('year') }}";

            if (!year) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Year Selected',
                    text: 'Please select a year before deleting.',
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: `This will permanently delete all PPMP records for ${year}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteYearForm').submit();
                }
            });
        });

        @if (session('success'))
            Swal.fire('Deleted!', "{{ session('success') }}", 'success');
        @endif

        @if (session('error'))
            Swal.fire('Error', "{{ session('error') }}", 'error');
        @endif
    </script>
</x-layouts.app>
