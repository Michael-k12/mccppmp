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

        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            text-align: center;
        }

        .modal select {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .close-btn {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .confirm-btn {
            background-color: #dc2626;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
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

                <a href="{{ route('ppmp.download.pdf', ['year' => request('year')]) }}" class="print-button">
                    Download PDF
                </a>

                <button type="button" id="openDeleteModal" class="delete-button">Delete Year</button>
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

    <!-- 🧩 Modal for selecting year -->
    <div class="modal" id="yearModal">
        <div class="modal-content">
            <h3>Select a Year to Delete</h3>
            <select id="yearSelect">
                <option value="">-- Choose Year --</option>
                @foreach ($availableYears as $yearOption)
                    <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                @endforeach
            </select>
            <div style="display: flex; justify-content: space-between;">
                <button class="close-btn" id="closeModal">Cancel</button>
                <button class="confirm-btn" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>

    <form id="deleteYearForm" action="{{ route('ppmp.delete.year') }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="year" id="deleteYearInput">
    </form>

    <script>
        const modal = document.getElementById('yearModal');
        const openBtn = document.getElementById('openDeleteModal');
        const closeBtn = document.getElementById('closeModal');
        const confirmBtn = document.getElementById('confirmDelete');
        const yearSelect = document.getElementById('yearSelect');
        const deleteForm = document.getElementById('deleteYearForm');
        const deleteInput = document.getElementById('deleteYearInput');

        openBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        confirmBtn.addEventListener('click', () => {
            const selectedYear = yearSelect.value;

            if (!selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select a Year',
                    text: 'Please choose a year before deleting.'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: `This will permanently delete all PPMP records for ${selectedYear}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteInput.value = selectedYear;
                    deleteForm.submit();
                }
            });
        });

        // SweetAlert responses
        @if (session('success'))
            Swal.fire('Deleted!', "{{ session('success') }}", 'success');
        @endif

        @if (session('error'))
            Swal.fire('Error', "{{ session('error') }}", 'error');
        @endif
    </script>
</x-layouts.app>
