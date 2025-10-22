<x-layouts.app :title="'Budget'">

    @push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endpush

    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-6 mb-6">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Budget Management</h2>
            @if (!$activeBudget)
                <button onclick="openModal()" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition w-full sm:w-auto">
                    ➕ Add Budget
                </button>
            @endif
        </div>

        <!-- Active Budget Warning -->
        @if ($activeBudget)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6 shadow-sm">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <p class="text-sm md:text-base">
                        A proposal is currently active for <strong>{{ $activeBudget->year }}</strong>.
                        Please end it before starting a new one.
                    </p>
                </div>
                <form id="endProposalForm" action="{{ route('budget.end', $activeBudget->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="button" onclick="confirmEndProposal()" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg shadow w-full sm:w-auto transition">
                        End Proposal
                    </button>
                </form>
            </div>
        @endif

        <!-- Modal -->
        <div id="budgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 sm:p-8 relative animate-fadeIn border border-gray-200">
                <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                <h3 class="text-xl sm:text-2xl font-semibold mb-6 text-center text-gray-800">Start Project Proposal</h3>
                <form id="budgetForm" action="{{ route('budget.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="milestone_date" class="block mb-1 font-medium text-gray-700">Year</label>
                        <input type="number" name="milestone_date" id="milestone_date" min="2000" max="2100" value="{{ now()->year }}" oninput="validateYear(this)" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="amount" class="block mb-1 font-medium text-gray-700">Budget Amount</label>
                        <input type="text" name="amount" id="amount" required oninput="formatNumberInput(this)"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">Save Budget</button>
                </form>
            </div>
        </div>

        <!-- Previous Budgets -->
        <div class="mt-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-800">Previous Budgets</h3>
                <form id="deleteSelectedForm" method="POST" action="{{ route('budget.deleteSelected') }}" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="deleteSelectedBtn" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg font-medium transition hidden w-full sm:w-auto">
                        Delete Selected
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($budgets as $budget)
                    <div class="bg-white border border-gray-200 rounded-lg shadow p-4 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800 text-lg">{{ $budget->year }}</h4>
                            <input type="checkbox" name="selected[]" value="{{ $budget->id }}" class="budget-checkbox">
                        </div>
                        <p class="text-green-600 font-semibold text-lg mb-2">₱{{ number_format($budget->amount, 2) }}</p>
                        <span class="self-start px-3 py-1 rounded-full text-sm
                            {{ $budget->is_ended ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-700' }}">
                            {{ $budget->is_ended ? 'Ended' : 'Active' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openModal() { document.getElementById('budgetModal').classList.remove('hidden'); }
        function closeModal() { document.getElementById('budgetModal').classList.add('hidden'); }

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
            }).then(result => { if(result.isConfirmed) document.getElementById('endProposalForm').submit(); });
        }

        function validateYear(input) {
            if (input.value.length > 4) input.value = input.value.slice(0, 4);
        }

        function formatNumberInput(input) {
            let value = input.value.replace(/[^0-9.]/g,'');
            const parts = value.split('.');
            if(parts.length > 2) value = parts[0] + '.' + parts[1];
            if(parts[1]) parts[1] = parts[1].slice(0,2);
            let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            let decimalPart = parts[1] ? '.' + parts[1] : '';
            input.value = integerPart + decimalPart;
        }

        document.getElementById('budgetForm').addEventListener('submit', e => {
            document.getElementById('amount').value = document.getElementById('amount').value.replace(/,/g,'');
        });

        const checkboxes = document.querySelectorAll('.budget-checkbox');
        const deleteBtn = document.getElementById('deleteSelectedBtn');

        checkboxes.forEach(cb => cb.addEventListener('change', () => {
            deleteBtn.classList.toggle('hidden', !Array.from(checkboxes).some(c => c.checked));
        }));

        document.getElementById('deleteSelectedForm').addEventListener('submit', e => {
            if (!Array.from(checkboxes).some(c => c.checked)) {
                e.preventDefault();
                alert('Please select at least one budget to delete.');
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            @if(session('success'))
                Swal.fire({ toast:true, position:'top-end', icon:'success', title:@json(session('success')), showConfirmButton:false, timer:2500 });
            @endif
        });
    </script>

    <style>
        @keyframes fadeIn { from {opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    </style>

</x-layouts.app>
