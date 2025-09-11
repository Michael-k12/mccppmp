<x-layouts.app :title="'Principal View'">
    
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="ppmp-header">
        <h2 class="title">Project Plan Approval</h2>

        @if ($latestBudget)
       <div class="budget-info">
    <span id="allocatedLabel" 
        class="budget-status {{ session('approved') ? 'allocated-red' : ($ppmpTotal > $latestBudget->amount ? 'over-budget' : ($ppmpTotal == $latestBudget->amount ? 'exact-budget' : 'within-budget')) }}">
        Allocated: ₱{{ number_format($latestBudget->amount, 2) }}
    </span>

    <span class="ppmp-subtotal highlight-budget">
        Purpose: ₱{{ number_format($ppmpTotal, 2) }}
    </span>

    <span id="remainingLabel" class="ppmp-subtotal remaining-budget">
        Remaining: ₱{{ session('approved') ? '0.00' : number_format($latestBudget->amount - $ppmpTotal, 2) }}
    </span>
</div>

        @else
            <span class="no-budget">No budget set</span>
        @endif
    </div>

   <!-- Buttons -->
<div class="flex justify-end gap-3 mb-5">
    @if ($latestBudget)
        <form method="POST" action="{{ route('ppmp.batchApprove') }}" 
              onsubmit="return confirm('Approve all submitted Project Plan?');">
            @csrf
            @foreach ($ppmps as $ppmp)
                <input type="hidden" name="ppmp_ids[]" value="{{ $ppmp->id }}">
            @endforeach

            <button type="submit" 
                class="approve-btn {{ $ppmpTotal != $latestBudget->amount ? 'disabled-btn' : '' }}"
                {{ $ppmpTotal != $latestBudget->amount ? 'disabled' : '' }}>
                Approve All
            </button>
        </form>

        <!-- Realignment Button -->
        <button type="button" class="realign-btn" 
            onclick="openRealignModal({{ $latestBudget->amount }}, {{ $ppmpTotal }})">
            Realignment
        </button>
    @else
        <button type="button" class="approve-btn disabled-btn" disabled>Approve All</button>
        <button type="button" class="realign-btn disabled-btn" disabled>Realignment</button>
    @endif

    <!-- Edit Quantities Button -->
    <a href="{{ route('ppmp.editDepartmentQuantities', 'all') }}" class="action-button">
        Edit Quantities
    </a>
</div>


    <!-- Table -->
    <div class="overflow-x-auto shadow rounded-lg border border-gray-200">
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Budget</th>
                    <th>Mode of Procurement</th>
                    <th>Schedule/Milestone</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ppmps as $ppmp)
                <tr>
                    <td>{{ $ppmp->classification }}</td>
                    <td>{{ $ppmp->description }}</td>
                    <td>{{ $ppmp->unit }}</td>
                    <td>{{ $ppmp->quantity }}</td>
                    <td>₱{{ number_format($ppmp->price, 2) }}</td>
                    <td>₱{{ number_format($ppmp->estimated_budget, 2) }}</td>
                    <td>{{ $ppmp->mode_of_procurement }}</td>
                    <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('F d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Realignment Modal -->
<div id="realignModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Budget Realignment</div>
        <div class="modal-body">
            <p><strong>Allocated Budget:</strong> ₱<span id="allocated"></span></p>
            <p><strong>Purpose Budget:</strong> ₱<span id="purpose"></span></p>
            <p><strong>Remaining Budget:</strong> ₱<span id="remaining"></span></p>

            <div class="mt-3">
                <label for="adjustment" class="block font-semibold mb-1">Adjust Budget:</label>
                <input type="number" id="adjustment" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300" placeholder="Enter amount">
                <small class="text-gray-500">Enter positive value to add or negative to deduct.</small>
            </div>
        </div>

        <div class="modal-actions">
            <button class="cancel-btn" onclick="closeRealignModal()">Cancel</button>
            <button class="save-btn" onclick="saveRealignment()">Save</button>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    /* Header */
    .ppmp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }
    .budget-info {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Budget Tags */
    .budget-status, .ppmp-subtotal {
        font-size: 0.9rem;
        padding: 4px 12px;
        border-radius: 6px;
        font-weight: 600;
    }
    .within-budget { background-color: #d1fae5; color: #065f46; }
    .over-budget { background-color: #fee2e2; color: #b91c1c; }
    .exact-budget { background-color: #bfdbfe; color: #1d4ed8; }
    .highlight-budget { background-color: #e0f2fe; color: #0369a1; }
    .remaining-budget { background-color: #dcfce7; color: #166534; }
    .no-budget { color: #dc2626; font-weight: 600; }

    /* Buttons */
    .action-button, .approve-btn, .realign-btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        transition: 0.3s;
    }
    .action-button { background-color: #3b82f6; color: #fff; }
    .action-button:hover { background-color: #2563eb; }
    .approve-btn { background-color: #16a34a; color: white; border: none; }
    .approve-btn:hover { background-color: #15803d; }
    .realign-btn { background-color: #f59e0b; color: white; border: none; }
    .realign-btn:hover { background-color: #d97706; }
    .disabled-btn { background-color: #9ca3af !important; cursor: not-allowed !important; opacity: 0.7; }

    /* Table */
    .excel-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .excel-table th, .excel-table td {
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
        text-align: left;
    }
    .excel-table th {
        background-color: #f3f4f6;
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
    }
    .excel-table tr:hover td { background-color: #f9fafb; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 50;
        background-color: rgba(0, 0, 0, 0.4);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 12px;
        width: 420px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .modal-header {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #1f2937;
    }
    .modal-body p {
        margin: 5px 0;
        font-size: 0.95rem;
        color: #374151;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 15px;
    }
    .cancel-btn {
        background-color: #9ca3af;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }
    .cancel-btn:hover { background-color: #6b7280; }
    .save-btn {
        background-color: #16a34a;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }
    .save-btn:hover { background-color: #15803d; }
    .allocated-red {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    font-weight: bold;
    border: 1px solid #f87171;
}

</style>

<!-- JS -->
<script>
    function openRealignModal(allocated, purpose) {
        document.getElementById('allocated').innerText = allocated.toLocaleString();
        document.getElementById('purpose').innerText = purpose.toLocaleString();
        document.getElementById('remaining').innerText = (allocated - purpose).toLocaleString();
        document.getElementById('realignModal').style.display = 'flex';
    }
    function closeRealignModal() {
        document.getElementById('realignModal').style.display = 'none';
    }
    function saveRealignment() {
        const adjustment = parseFloat(document.getElementById('adjustment').value);
        if (isNaN(adjustment)) {
            alert("Please enter a valid adjustment amount.");
            return;
        }
        fetch("{{ route('ppmp.realign') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ adjustment })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") window.location.reload();
        })
        .catch(error => console.error("Error saving realignment:", error));
    }
</script>
</x-layouts.app>
