<x-layouts.app :title="'Realign'">
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Realignment</h2>

    <!-- 🔍 Search Bar -->
    <div class="flex justify-end mb-4">
        <input 
            type="text" 
            id="searchInput" 
            placeholder="Search by department or description..." 
            class="border border-gray-300 rounded-md px-4 py-2 w-80 focus:ring focus:ring-blue-300 focus:border-blue-400"
            onkeyup="filterTable()"
        >
    </div>

    <form method="POST" action="{{ route('ppmp.updateDepartmentQuantities', 'all') }}" onsubmit="calculateFinalQuantities()">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 shadow rounded-lg" id="ppmpTable">
                <thead class="bg-blue-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left border">Department</th>
                        <th class="px-4 py-3 text-left border">Description</th>
                        <th class="px-4 py-3 text-center border">Current Quantity</th>
                        <th class="px-4 py-3 text-center border">Add</th>
                        <th class="px-4 py-3 text-center border">Subtract</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ppmps as $index => $ppmp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 border">{{ $ppmp->department }}</td>
                            <td class="px-4 py-3 border">{{ $ppmp->description }}</td>
                            <td class="px-4 py-3 text-center border">
                                {{ $ppmp->quantity }}
                                <input type="hidden" name="current_quantities[]" value="{{ $ppmp->quantity }}">
                            </td>
                            <td class="px-4 py-3 text-center border">
                                <input type="number" name="additions[]" value="" min="0" class="border-gray-300 rounded w-20 text-center" placeholder="0">
                            </td>
                            <td class="px-4 py-3 text-center border">
                                <input type="number" name="subtractions[]" value="" min="0" class="border-gray-300 rounded w-20 text-center" placeholder="0">
                            </td>
                            <input type="hidden" name="ppmp_ids[]" value="{{ $ppmp->id }}">
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="form-actions">
            <button type="submit" class="save-btn">Save Changes</button>
            <a href="{{ route('ppmp.principalview') }}" class="cancel-link">Cancel</a>
        </div>

        <style>
            .form-actions {
                margin-top: 1.5rem;
                display: flex;
                align-items: center;
            }
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
                margin-left: 1rem;
                color: #4b5563;
                text-decoration: none;
            }
            .cancel-link:hover {
                text-decoration: underline;
            }
        </style>
    </form>
</div>

<script>
    // 🧮 Compute adjusted quantities before submit
    function calculateFinalQuantities() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row) => {
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
