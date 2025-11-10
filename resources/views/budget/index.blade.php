<x-layouts.app :title="'Budget'">

    @push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endpush

    <div class="container mx-auto px-4 py-8">

        {{-- ✅ Page Header (Title Left, Logo Right, Buttons Below Logo) --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-start gap-4 mb-8">
            {{-- Left: Title --}}
            <h2 class="text-2xl font-bold text-gray-800">Previous Budgets</h2>

            {{-- Right: Logo + Buttons --}}
            <div class="flex flex-col items-end gap-3">
                {{-- Logo --}}
                <img src="{{ asset('logo/logo-mcc.png') }}" alt="Logo" class="h-20 w-80 mb-2">

                {{-- Buttons --}}
                <div class="flex flex-wrap justify-end items-center gap-3">
                    {{-- Add Budget Button --}}
                    @if (!$activeBudget)
                        <button onclick="openModal()" class="start-proposal-btn">
                            ➕ Add Budget
                        </button>
                    @endif

                    {{-- End Proposal Button (if active) --}}
                    @if ($activeBudget)
                        <form id="endProposalForm" action="{{ route('budget.end', $activeBudget->id) }}" method="POST">
                            @csrf
                            <button type="button" onclick="confirmEndProposal()" class="end-proposal-btn">
                                End Proposal
                            </button>
                        </form>
                    @endif

                    {{-- Delete Selected Button --}}
                    <form id="deleteSelectedForm" method="POST" action="{{ route('budget.deleteSelected') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="deleteSelectedBtn"
                            class="bg-red-500 text-white px-4 py-3 rounded-lg text-sm hover:bg-red-600 transition hidden">
                            Delete Selected
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ⚠️ Active Budget Warning --}}
        @if ($activeBudget)
            <div
                class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-5 py-4 rounded-xl mb-6 shadow-sm flex items-start sm:items-center gap-3">
                <span class="text-xl">⚠️</span>
                <p class="text-sm md:text-base leading-snug">
                    A proposal is currently active for
                    <strong>{{ $activeBudget->year }}</strong>. Please end it before starting a new one.
                </p>
            </div>
        @endif

        {{-- 💰 Budgets Table --}}
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
                                <input type="checkbox" name="selected[]" value="{{ $budget->id }}"
                                    class="budget-checkbox">
                            </td>
                            <td class="px-5 py-3 text-gray-800 font-medium">{{ $budget->year }}</td>
                            <td class="px-5 py-3 text-green-600 font-semibold">
                                ₱{{ number_format($budget->amount, 2) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if (!$budget->is_ended)
                                    <span
                                        class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">Active</span>
                                @else
                                    <span
                                        class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm">Ended</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 🧾 Add Budget Modal --}}
        <div id="budgetModal"
            class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 px-4">
            <div
                class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md relative animate-fadeIn border border-gray-200">
                <button onclick="closeModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

                <h3 class="text-xl sm:text-2xl font-semibold mb-6 text-gray-800 text-center">Start Project Proposal</h3>

                <form id="budgetForm" action="{{ route('budget.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label for="milestone_date" class="block mb-2 font-medium text-gray-700">Year</label>
                        <input type="number" name="milestone_date" id="milestone_date" class="modern-input" min="2000"
                            max="2100" value="{{ now()->year }}" oninput="validateYear(this)" required>
                    </div>

                    <div>
                        <label for="amount" class="block mb-2 font-medium text-gray-700">Budget Amount</label>
                        <input type="text" name="amount" id="amount" class="modern-input" required
                            oninput="formatNumberInput(this)">
                    </div>

                    <button type="submit" class="save-budget-btn w-full">Save Budget</button>
                </form>
            </div>
        </div>

    </div>

    {{-- 🎨 Styles --}}
    <style>
        .start-proposal-btn {
            background: #10b981;
            color: white;
            padding: 9px 16px;
            border-radius: 8px;
            font-weight: 620;
            transition: 0.2s;
        }

        .start-proposal-btn:hover {
            background: #059669;
            transform: scale(1.05);
        }

        .end-proposal-btn {
            background: #ef4444;
            color: white;
            padding: 9px 16px;
            border-radius: 8px;
            font-weight: 620;
            transition: 0.2s;
        }

        .end-proposal-btn:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .save-budget-btn {
            background: #2563eb;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
        }

        .save-budget-btn:hover {
            background: #1e40af;
            transform: scale(1.05);
        }

        .modern-input {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 14px;
            width: 100%;
            font-size: 15px;
            background: #f9fafb;
        }

        .modern-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
            background: white;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        @media (max-width: 640px) {
            .flex-col {
                flex-direction: column !important;
            }

            .start-proposal-btn,
            .end-proposal-btn,
            #deleteSelectedBtn {
                width: 100%;
            }

            .flex-col.items-end {
                align-items: flex-start !important;
            }
        }
    </style>

    {{-- ⚙️ Scripts --}}
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
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('endProposalForm').submit();
            });
        }

        function validateYear(input) {
            if (input.value.length > 4) input.value = input.value.slice(0, 4);
        }

        function formatNumberInput(input) {
            let value = input.value.replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) value = parts[0] + '.' + parts[1];
            if (parts[1]) parts[1] = parts[1].slice(0, 2);
            input.value = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (parts[1] ? '.' + parts[1] : '');
        }

        document.getElementById('budgetForm').addEventListener('submit', function() {
            document.getElementById('amount').value = document.getElementById('amount').value.replace(/,/g, '');
        });

        const selectAllCheckbox = document.getElementById('selectAll');
        const budgetCheckboxes = document.querySelectorAll('.budget-checkbox');
        const deleteBtn = document.getElementById('deleteSelectedBtn');

        selectAllCheckbox.addEventListener('change', () => {
            budgetCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            toggleDeleteBtn();
        });
        budgetCheckboxes.forEach(cb => cb.addEventListener('change', toggleDeleteBtn));

        function toggleDeleteBtn() {
            const anyChecked = Array.from(budgetCheckboxes).some(cb => cb.checked);
            deleteBtn.classList.toggle('hidden', !anyChecked);
        }

        document.getElementById('deleteSelectedForm').addEventListener('submit', function(e) {
            const anyChecked = Array.from(budgetCheckboxes).some(cb => cb.checked);
            if (!anyChecked) {
                e.preventDefault();
                alert('Please select at least one budget to delete.');
            } else {
                e.preventDefault();
                Swal.fire({
                    title: 'Confirm Delete?',
                    text: "You are about to delete selected budgets.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete them!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('deleteSelectedForm');
                        budgetCheckboxes.forEach(cb => {
                            if (cb.checked) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'selected[]';
                                input.value = cb.value;
                                form.appendChild(input);
                            }
                        });
                        form.submit();
                    }
                });
            }
        });

        // SweetAlert Toast Notifications
        document.addEventListener("DOMContentLoaded", function() {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 2500 });
            @endif
        });
    </script>

</x-layouts.app>
