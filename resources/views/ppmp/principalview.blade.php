<x-layouts.app :title="'Realign'">
<div class="container mx-auto px-4 py-4">
    <!-- 🧭 Page Header -->
    <div class="sticky top-0 z-10 bg-white pb-3 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Realignment</h2>

        <!-- 📊 Summary -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm text-gray-700 mb-3">
            <div>
                <p><strong>Project Plan Approval</strong></p>
                <p>Allocated: ₱12,000.00</p>
                <p>Purpose: ₱12,000.00</p>
                <p>Remaining: ₱0.00</p>
            </div>

            <!-- ⚙️ Action Buttons -->
            <div class="flex flex-wrap justify-start sm:justify-end gap-2 mt-2 sm:mt-0">
                <button class="action-btn bg-green-600 hover:bg-green-700">Approve All</button>
                <button class="action-btn bg-red-600 hover:bg-red-700">Delete All</button>
                <button class="action-btn bg-blue-600 hover:bg-blue-700">Realignment</button>
            </div>
        </div>

        <!-- 🔍 Search Bar -->
        <div class="flex justify-end">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Search by department or description..." 
                class="search-bar"
                onkeyup="filterTable()"
            >
        </div>
    </div>

    <!-- 🧾 Table Section -->
    <div class="overflow-x-auto mt-4 max-h-[65vh] overflow-y-auto border border-gray-300 rounded-lg shadow">
        <form method="POST" action="{{ route('ppmp.updateDepartmentQuantities', 'all') }}" onsubmit="calculateFinalQuantities()">
            @csrf
            <table class="min-w-full bg-white" id="ppmpTable">
                <thead class="bg-blue-100 text-gray-700 text-sm">
                    <tr>
                        <th class="px-4 py-3 text-left border">Department</th>
                        <th class="px-4 py-3 text-left border">Description</th>
                        <th class="px-4 py-3 text-center border">Current Qty</th>
                        <th class="px-4 py-3 text-center border">Add</th>
                        <th class="px-4 py-3 text-center border">Subtract</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ppmps as $ppmp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 border">{{ $ppmp->department }}</td>
                            <td class="px-4 py-3 border">{{ $ppmp->description }}</td>
                            <td class="px-4 py-3 text-center border">
                                {{ $ppmp->quantity }}
                                <input type="hidden" name="current_quantities[]" value="{{ $ppmp->quantity }}">
                            </td>
                            <td class="px-4 py-3 text-center border">
                                <input type="number" name="additions[]" min="0" class="qty-input" placeholder="0">
                            </td>
                            <td class="px-4 py-3 text-center border">
                                <input type="number" name="subtractions[]" min="0" class="qty-input" placeholder="0">
                            </td>
                            <input type="hidden" name="ppmp_ids[]" value="{{ $ppmp->id }}">
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- ✅ Form Buttons -->
            <div class="form-actions mt-4 mb-2 text-center">
                <button type="submit" class="save-btn">Save</button>
                <a href="{{ route('ppmp.principalview') }}" class="cancel-link">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    /* 🌐 Search Bar */
    .search-bar {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
        width: 50%;
    }
    @media (max-width: 768px) {
        .search-bar {
            width: 100%;
        }
    }

    /* 🧮 Inputs */
    .qty-input {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        text-align: center;
        width: 4rem;
        padding: 0.25rem;
    }

    /* 🔘 Action Buttons */
    .action-btn {
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.4rem 0.9rem;
        border-radius: 0.375rem;
        transition: background-color 0.3s ease;
        white-space: nowrap;
    }

    /* ✅ Form Buttons */
    .save-btn {
        background-color: #2563eb;
        color: white;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .save-btn:hover {
        background-color: #1e40af;
    }
    .cancel-link {
        margin-left: 0.75rem;
        color: #4b5563;
        text-decoration: none;
        font-weight: 500;
    }
    .cancel-link:hover {
        text-decoration: underline;
    }

    /* 📱 Mobile Fixes */
    @media (max-width: 640px) {
        .action-btn {
            flex: 1;
            text-align: center;
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
    }
</style>

<script>
    // 🧮 Compute adjusted quantities before submit
    function calculateFinalQuantities() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const currentQty = parseInt(row.querySelector('input[name="current_quantities[]"]').value) || 0;
            const add = parseInt(row.querySelector('input[name="additions[]"]').value) || 0;
            const subtract = parseInt(row.querySelector('input[name="subtractions[]"]').value) || 0;
            const finalQty = Math.max(currentQty + add - subtract, 1);
            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'quantities[]';
            qtyInput.value = finalQty;
            row.appendChild(qtyInput);
        });
    }

    // 🔍 Search filter function
    function filterTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#ppmpTable tbody tr');
        rows.forEach(row => {
            const dept = row.cells[0].textContent.toLowerCase();
            const desc = row.cells[1].textContent.toLowerCase();
            row.style.display = (dept.includes(input) || desc.includes(input)) ? '' : 'none';
        });
    }
</script>
</x-layouts.app>
