<x-layouts.app :title="'Budget'">

    @push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endpush

    <div class="container mx-auto px-4 py-8">
        {{-- Adjusted to ensure title and button/warning are always on the same row on desktop --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 flex-shrink-0">Budget Management</h2>
            
            {{-- Unified container for the right-hand element (Button or Warning) --}}
            <div class="w-full sm:w-auto flex justify-end">
                @if (!$activeBudget)
                    <button onclick="openModal()" class="start-proposal-btn">
                        ➕ Add Budget
                    </button>
                @endif
            </div>
        </div>

        @if ($activeBudget)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-5 py-4 rounded-xl mb-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <p class="text-sm md:text-base leading-snug">
                        A proposal is currently active for
                        <strong>{{ $activeBudget->year }}</strong>.
                        Please end it before starting a new one.
                    </p>
                </div>

                <form id="endProposalForm" action="{{ route('budget.end', $activeBudget->id) }}" method="POST" class="w-full sm:w-auto flex-shrink-0">
                    @csrf
                    <button type="button" onclick="confirmEndProposal()" class="end-proposal-btn w-full sm:w-auto">
                        End Proposal
                    </button>
                </form>
            </div>
        @endif

        <div id="budgetModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md relative animate-fadeIn border border-gray-200">
                <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

                <h3 class="text-xl sm:text-2xl font-semibold mb-6 text-gray-800 text-center">Start Project Proposal</h3>

                <form id="budgetForm" action="{{ route('budget.store') }}" method="POST" class="space-y-6">
                    @csrf

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

                    <div>
                        <label for="amount" class="block mb-2 font-medium text-gray-700">Budget Amount</label>
                        <input type="text"
                               name="amount"
                               id="amount"
                               class="modern-input"
                               required
                               oninput="formatNumberInput(this)">
                    </div>

                    <button type="submit" class="save-budget-btn w-full">Save Budget</button>
                </form>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="text-xl sm:text-2xl font-semibold mb-4 text-gray-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                Previous Budgets

                <form id="deleteSelectedForm" method="POST" action="{{ route('budget.deleteSelected') }}" class="w-full sm:w-auto flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="deleteSelectedBtn" class="bg-red-500 text-white px-4 py-1 rounded-lg text-sm hover:bg-red-600 transition hidden w-full sm:w-auto">
                        Delete Selected
                    </button>
                </form>
            </h3>

            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-x-auto">
                <table class="w-full border-collapse min-w-[600px]">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-sm">
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
        </div>
    </div>

    <style>
        .start-proposal-btn {
            background-color: #10b981;
            color: white;
            padding: 8px 14px; /* smaller than before */
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Adjusted shadow */
        }
        .start-proposal-btn:hover {
            background-color: #059669;
            transform: scale(1.03);
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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .save-budget-btn:hover {
            background-color: #1e40af;
            transform: scale(1.05);
        }

        .end-proposal-btn {
            background-color: #ef4444;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .end-proposal-btn:hover {
            background-color: #dc2626;
            transform: scale(1.05);
        }

        .modern-input {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 14px;
            width: 100%;
            font-size: 15px;
            color: #111827;
            background-color: #f9fafb;
            transition: all 0.2s ease;
            -moz-appearance: textfield;
        }

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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }
          /* Responsive Consistency */
        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            table {
                font-size: 14px;
            }
            .end-proposal-btn, .start-proposal-btn {
                width: 100%;
            }
            /* Ensure the header elements stack nicely on mobile */
            .flex-col.sm\:flex-row {
                flex-direction: column;
            }
            .flex-col.sm\:flex-row > h2 {
                margin-bottom: 0.5rem; /* Add a little space below the title on mobile */
            }
            .flex-col.sm\:flex-row > div:last-child {
                justify-content: flex-start; /* Align button/warning to the left on mobile */
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        function validateYear(input) {
            if (input.value.length > 4) {
                input.value = input.value.slice(0, 4);
            }
        }

        // ✅ Format amount input
        function formatNumberInput(input) {
            let value = input.value.replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) value = parts[0] + '.' + parts[1];
            if (parts[1]) parts[1] = parts[1].slice(0, 2);
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            let decimalPart = parts[1] ? '.' + parts[1] : '';
            input.value = integerPart + decimalPart;
        }

        document.getElementById('budgetForm').addEventListener('submit', function(e) {
            const amountInput = document.getElementById('amount');
            // Remove commas before submission to ensure backend receives a clean number
            amountInput.value = amountInput.value.replace(/,/g, '');
        });

        // ✅ Checkbox selection
        const selectAllCheckbox = document.getElementById('selectAll');
        const budgetCheckboxes = document.querySelectorAll('.budget-checkbox');
        const deleteBtn = document.getElementById('deleteSelectedBtn');

        selectAllCheckbox.addEventListener('change', function() {
            budgetCheckboxes.forEach(cb => cb.checked = this.checked);
            toggleDeleteBtn();
        });

        budgetCheckboxes.forEach(cb => cb.addEventListener('change', toggleDeleteBtn));

        function toggleDeleteBtn() {
            const anyChecked = Array.from(budgetCheckboxes).some(cb => cb.checked);
            deleteBtn.classList.toggle('hidden', !anyChecked);
        }

        document.getElementById('deleteSelectedForm').addEventListener('submit', function(e) {
            // Re-check for selected items before submitting for deletion
            const anyChecked = Array.from(budgetCheckboxes).some(cb => cb.checked);
            if (!anyChecked) {
                e.preventDefault();
                alert('Please select at least one budget to delete.');
            } else {
                // Add a confirmation for delete selected
                e.preventDefault();
                Swal.fire({
                    title: 'Confirm Delete?',
                    text: "You are about to delete the selected budget records.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete them!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Dynamically add hidden inputs for selected IDs to the form
                        const form = document.getElementById('deleteSelectedForm');
                        budgetCheckboxes.forEach(cb => {
                            if (cb.checked) {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'selected[]';
                                hiddenInput.value = cb.value;
                                form.appendChild(hiddenInput);
                            }
                        });
                        form.submit();
                    }
                });
            }
        });

        // ✅ SweetAlert Toasts
        document.addEventListener("DOMContentLoaded", function() {
            // Close modal if there are validation errors (assuming Laravel redirects back with errors)
            // You might need additional logic if Laravel sends error messages to the session/view.
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 2500 });
            @endif
            @if(session('error'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: @json(session('error')), showConfirmButton: false, timer: 3000 });
            @endif
            @if(session('warning'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: @json(session('warning')), showConfirmButton: false, timer: 3000 });
            @endif
            @if(session('info'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: @json(session('info')), showConfirmButton: false, timer: 2500 });
            @endif
        });
    </script>

</x-layouts.app>