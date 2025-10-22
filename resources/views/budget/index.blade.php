<x-layouts.app :title="'Budget'">

    @push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endpush

    <div class="container mx-auto px-4 py-8">

        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Budget Management</h1>
            @if(!$activeBudget)
            <button onclick="openModal()" 
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow-md transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Budget
            </button>
            @endif
        </div>

        <!-- Active Budget Alert -->
        @if($activeBudget)
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 px-6 py-4 rounded-lg shadow mb-8">
            <div class="flex items-start md:items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <p>A proposal is currently active for <strong>{{ $activeBudget->year }}</strong>. End it before starting a new one.</p>
            </div>
            <form id="endProposalForm" action="{{ route('budget.end', $activeBudget->id) }}" method="POST" class="mt-4 md:mt-0">
                @csrf
                <button type="button" onclick="confirmEndProposal()" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
                    End Proposal
                </button>
            </form>
        </div>
        @endif

        <!-- Budget Modal -->
        <div id="budgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative animate-fadeIn">
                <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                <h2 class="text-2xl font-semibold text-gray-900 text-center mb-6">Start New Budget</h2>
                <form id="budgetForm" action="{{ route('budget.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="milestone_date" class="block text-gray-700 font-medium mb-1">Year</label>
                        <input type="number" name="milestone_date" id="milestone_date" min="2000" max="2100" value="{{ now()->year }}" oninput="validateYear(this)" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label for="amount" class="block text-gray-700 font-medium mb-1">Budget Amount</label>
                        <input type="text" name="amount" id="amount" required oninput="formatNumberInput(this)"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
                        Save Budget
                    </button>
                </form>
            </div>
        </div>

        <!-- Previous Budgets -->
        <div class="mt-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-4">
                <h2 class="text-2xl font-semibold text-gray-900">Previous Budgets</h2>
                <form id="deleteSelectedForm" method="POST" action="{{ route('budget.deleteSelected') }}" class="w-full md:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="deleteSelectedBtn" class="hidden bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Delete Selected
                    </button>
                </form>
            </div>

            <!-- Grid Display -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($budgets as $budget)
                <div class="bg-white border border-gray-200 rounded-xl shadow hover:shadow-lg transition p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $budget->year }}</h3>
                        <input type="checkbox" name="selected[]" value="{{ $budget->id }}" class="budget-checkbox">
                    </div>
                    <p class="text-green-600 font-bold text-lg mb-3">₱{{ number_format($budget->amount, 2) }}</p>
                    <span class="self-start px-3 py-1 rounded-full text-sm font-medium
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
        function openModal(){ document.getElementById('budgetModal').classList.remove('hidden'); }
        function closeModal(){ document.getElementById('budgetModal').classList.add('hidden'); }

        function confirmEndProposal(){
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

        function validateYear(input){ if(input.value.length>4) input.value=input.value.slice(0,4); }

        function formatNumberInput(input){
            let value=input.value.replace(/[^0-9.]/g,'');
            const parts=value.split('.');
            if(parts.length>2) value=parts[0]+'.'+parts[1];
            if(parts[1]) parts[1]=parts[1].slice(0,2);
            let integerPart=parts[0].replace(/\B(?=(\d{3})+(?!\d))/g,',');
            let decimalPart=parts[1]?'.'+parts[1]:'';
            input.value=integerPart+decimalPart;
        }

        document.getElementById('budgetForm').addEventListener('submit',e=>{
            document.getElementById('amount').value=document.getElementById('amount').value.replace(/,/g,'');
        });

        const checkboxes=document.querySelectorAll('.budget-checkbox');
        const deleteBtn=document.getElementById('deleteSelectedBtn');
        checkboxes.forEach(cb=>cb.addEventListener('change',()=>{ deleteBtn.classList.toggle('hidden',!Array.from(checkboxes).some(c=>c.checked)); }));
        document.getElementById('deleteSelectedForm').addEventListener('submit',e=>{
            if(!Array.from(checkboxes).some(c=>c.checked)){ e.preventDefault(); alert('Please select at least one budget to delete.'); }
        });

        document.addEventListener("DOMContentLoaded",function(){
            @if(session('success'))
                Swal.fire({ toast:true, position:'top-end', icon:'success', title:@json(session('success')), showConfirmButton:false, timer:2500 });
            @endif
        });
    </script>

    <style>
        @keyframes fadeIn { from{opacity:0; transform:translateY(-10px);} to{opacity:1; transform:translateY(0);} }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    </style>

</x-layouts.app>
