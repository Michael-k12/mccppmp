<x-layouts.app :title="'Budget'">
    <div class="container mx-auto px-4 py-8">
        <!-- ✅ Page Header -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Budget Management</h2>
            @if (!$activeBudget)
                <button onclick="openModal()" class="start-proposal-btn">
                    ➕ Add Budget 
                </button>
            @endif
        </div>

        <!-- ✅ Active Proposal Warning -->
        @if ($activeBudget)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-5 py-3 rounded-xl mb-6 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <p class="text-sm md:text-base leading-snug">
                        A proposal is currently active for 
                        <strong>{{ $activeBudget->year }}</strong>. 
                        Please end it before starting a new one.
                    </p>
                </div>

                <!-- End Proposal Button -->
                <form id="endProposalForm" action="{{ route('budget.end', $activeBudget->id) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="button" onclick="confirmEndProposal()" class="end-proposal-btn">
                        End Proposal
                    </button>
                </form>
            </div>
        @endif

        <!-- ✅ Modal -->
        <div id="budgetModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative animate-fadeIn border border-gray-200">
                <!-- Close Button -->
                <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

                <!-- Modal Title -->
                <h3 class="text-2xl font-semibold mb-6 text-gray-800 text-center">Start Project Proposal</h3>

                <!-- Modal Form -->
                <form action="{{ route('budget.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- ✅ Year Input -->
                    <div>
                        <label for="milestone_date" class="block mb-2 font-medium text-gray-700">Year</label>
                        <input type="number" 
                               name="milestone_date" 
                               id="milestone_date" 
                               class="modern-input" 
                               min="2000" 
                               max="2100" 
                               value="{{ now()->year }}"
                               oninput="validateYear(this)"
                               required>
                    </div>

                    <!-- ✅ Budget Amount Input -->
                    <div>
                        <label for="amount" class="block mb-2 font-medium text-gray-700">Budget Amount</label>
                        <input type="number" 
                               name="amount" 
                               id="amount" 
                               step="0.01" 
                               min="0" 
                               class="modern-input" 
                               required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="save-budget-btn w-full">💾 Save Budget</button>
                </form>
            </div>
        </div>

<div class="mt-8">
    <h3 class="text-2xl font-semibold mb-3 text-gray-800 flex items-center justify-between">
        Previous Budgets

       <form id="deleteSelectedForm" method="POST" action="{{ route('budget.deleteSelected') }}">
    @csrf
    @method('DELETE')
        <button type="submit" id="deleteSelectedBtn" class="bg-red-500 text-white px-4 py-1 rounded-lg text-sm hover:bg-red-600 transition hidden">
            Delete Selected
        </button>
    </div>

    <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-5 py-3">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th class="px-5 py-3 text-left text-sm font-semibold">Year</th>
                    <th class="px-5 py-3 text-left text-sm font-semibold">Budget Amount</th>
                    <th class="px-5 py-3 text-center text-sm font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($budgets as $budget)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <input type="checkbox" name="selected[]" value="{{ $budget->id }}" class="budget-checkbox">
                        </td>
                        <td class="px-5 py-3 text-gray-800 font-medium">{{ $budget->year }}</td>
                        <td class="px-5 py-3 text-green-600 font-semibold">₱{{ number_format($budget->amount, 2) }}</td>
                        <td class="px-5 py-3 text-center">
                            @if (!$budget->is_ended)
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">Active</span>
                            @else
                                <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm">Ended</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
</div>

    <!-- ✅ Styles -->
    <style>
        /* Primary Buttons */
        .start-proposal-btn {
            background-color: #10b981;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        }
        .start-proposal-btn:hover {
            background-color: #059669;
            transform: scale(1.04);
        }

        .save-budget-btn {
            background-color: #2563eb;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .save-budget-btn:hover {
            background-color: #1e40af;
            transform: scale(1.05);
        }

        .end-proposal-btn {
            background-color: #ef4444;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        .end-proposal-btn:hover {
            background-color: #dc2626;
            transform: scale(1.05);
        }

        /* Modern Input Styles */
        .modern-input {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 14px;
            width: 100%;
            font-size: 15px;
            color: #111827;
            background-color: #f9fafb;
            transition: all 0.2s ease;
            -moz-appearance: textfield; /* Remove spinner for Firefox */
        }

        /* Remove number input spinners in Chrome, Edge, Safari */
        .modern-input::-webkit-outer-spin-button,
        .modern-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .modern-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
            background-color: #fff;
        }

        /* Responsive Warning Box */
        @media (max-width: 640px) {
            .bg-yellow-50 {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .end-proposal-btn {
                width: 100%;
                text-align: center;
            }
        }

        /* Modal Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }
    </style>

    <!-- ✅ SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ✅ JavaScript -->
    <script>
        function openModal() {
            document.getElementById('budgetModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('budgetModal').classList.add('hidden');
        }

        function confirmEndProposal() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will end the current proposal!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, end it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('endProposalForm').submit();
                }
            });
        }

        // ✅ Limit year to 4 digits
        function validateYear(input) {
            if (input.value.length > 4) {
                input.value = input.value.slice(0, 4);
            }
        }

        // ✅ SweetAlert Toasts
        document.addEventListener("DOMContentLoaded", function () {
            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: @json(session('error')),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: @json(session('warning')),
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if(session('info'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: @json(session('info')),
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });
            @endif
        });
    const selectAllCheckbox = document.getElementById('selectAll');
    const budgetCheckboxes = document.querySelectorAll('.budget-checkbox');
    const deleteBtn = document.getElementById('deleteSelectedBtn');

    selectAllCheckbox.addEventListener('change', function() {
        budgetCheckboxes.forEach(cb => cb.checked = this.checked);
        toggleDeleteBtn();
    });

    budgetCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleDeleteBtn);
    });

    function toggleDeleteBtn() {
        const anyChecked = Array.from(budgetCheckboxes).some(cb => cb.checked);
        deleteBtn.classList.toggle('hidden', !anyChecked);
    }

    // Submit the form with selected budgets
    document.getElementById('deleteSelectedForm').addEventListener('submit', function(e) {
        const anyChecked = Array.from(budgetCheckboxes).some(cb => cb.checked);
        if (!anyChecked) {
            e.preventDefault();
            alert('Please select at least one budget to delete.');
        }
    });
</script>

</x-layouts.app>
