<x-layouts.app :title="'Manage'">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

<style>
    .excel-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .excel-table th, .excel-table td {
        border: 1px solid #cbd5e0;
        padding: 6px 10px;
        text-align: left;
        background-color: white;
    }
    .excel-table th {
        background-color: rgb(154, 169, 255);
        font-size: 14px;
    }
    .excel-table tr:hover td {
        background-color: #f9fafb;
    }
    .action-btn {
        padding: 4px 10px;
        font-size: 12px;
        border-radius: 4px;
        font-weight: 500;
    }
    .btn-update {
        background-color: #3b82f6;
        color: white;
    }
    .btn-update:hover {
        background-color: #2563eb;
    }
    .btn-delete {
        background-color: #ef4444;
        color: white;
    }
    .btn-delete:hover {
        background-color: #dc2626;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.3);
        justify-content: center;
        align-items: center;
    }
    .modal-content {
        background: white;
        padding: 20px;
        width: 300px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        text-align: center;
    }
    .quantity-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 15px 0;
    }
    .quantity-controls button {
        background-color: #3b82f6;
        color: white;
        padding: 6px 12px;
        font-size: 16px;
        border: none;
        border-radius: 4px;
        margin: 0 10px;
    }
    .quantity-controls span {
        font-size: 18px;
        min-width: 40px;
        display: inline-block;
    }
</style>

<div class="max-w-6xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Manage Project Procurement Plan</h1>

    <div class="overflow-x-auto bg-white shadow border border-gray-200 rounded-lg">
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Estimated Budget</th>
                    <th>Mode of Procurement</th>    
                    <th>Schedule/Milestone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ppmps as $ppmp)
                    @if($ppmp->status !== 'submitted')
<tr>
    <td>{{ $ppmp->classification }}</td>
    <td>{{ $ppmp->description }}</td>
    <td>{{ $ppmp->unit }}</td>
    <td>{{ $ppmp->quantity }}</td>
    <td>{{ number_format($ppmp->price, 2) }}</td>
    <td>{{ number_format($ppmp->estimated_budget, 2) }}</td>
    <td>{{ $ppmp->mode_of_procurement }}</td>
    <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('Y') }}</td>
    <td class="text-center whitespace-nowrap">
        <button class="action-btn btn-update"
            onclick="openQuantityModal(
                {{ $ppmp->id }},
                {{ $ppmp->quantity }},
                '{{ addslashes($ppmp->description) }}',
                {{ $ppmp->price }},
                {{ $ppmp->allocated_budget ?? 0 }}
            )">
            Update
        </button>

        <form id="delete-form-{{ $ppmp->id }}" action="{{ route('ppmps.destroy', $ppmp->id) }}" method="POST" class="inline-block">
            @csrf @method('DELETE')
            <button type="button" class="action-btn btn-delete" onclick="submitDelete({{ $ppmp->id }})">Delete</button>
        </form>
    </td>
</tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-gray-500 py-4">Create new Project Plan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Quantity Update Modal -->
<div id="quantityModal" class="modal">
    <div class="modal-content">
        <h2 class="text-lg font-semibold mb-2">Update Quantity</h2>

        <p id="modal-item-name" class="text-gray-700 text-sm mb-4 font-medium"></p>

        <form id="update-form" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="current_quantity" id="modal-quantity-value">
            <input type="hidden" id="modal-price-value">
            <input type="hidden" id="modal-allocated-budget" value="0">

            <div class="quantity-controls">
                <button type="button" onclick="enableQuantityInput()">Add</button>

                <input type="number"
                    id="quantity-input"
                    name="adjustment"
                    value="0"
                    oninput="calculateEstimates()"
                    disabled
                    class="w-16 text-center border border-gray-300 rounded px-2 py-1 mx-2 text-gray-500 bg-gray-100 cursor-not-allowed"
                />

                <button type="button" onclick="enableQuantityInput()">Subtract</button>
            </div>

            <p class="text-sm text-left mb-1">Estimated Budget: ₱<span id="budget-estimate">0.00</span></p>
            <p class="text-sm text-left mb-4">Mode of Procurement: <strong id="mode-of-procurement">N/A</strong></p>

            <div class="flex justify-center mt-4 space-x-2">
                <button type="submit" class="btn-update action-btn">Save</button>
                <button type="button" class="btn-delete action-btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuantityModal(id, currentQty, itemName = '', price = 0, allocatedBudget = 0) {
    const quantityInput = document.getElementById('quantity-input');
    const quantityHiddenInput = document.getElementById('modal-quantity-value');
    const itemNameField = document.getElementById('modal-item-name');
    const priceInput = document.getElementById('modal-price-value');
    const allocatedBudgetInput = document.getElementById('modal-allocated-budget');
    const form = document.getElementById('update-form');

    quantityInput.value = 0;
    quantityHiddenInput.value = currentQty;
    priceInput.value = price;
    allocatedBudgetInput.value = allocatedBudget;

    quantityInput.disabled = true;
    quantityInput.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
    quantityInput.classList.remove('bg-white', 'text-black', 'cursor-text');

    itemNameField.textContent = `Editing: ${itemName}`;
    form.action = `/ppmps/${id}/update-quantity`;

    document.getElementById('budget-estimate').textContent = '0.00';
    document.getElementById('mode-of-procurement').textContent = 'N/A';

    document.getElementById('quantityModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('quantityModal').style.display = 'none';
}

function enableQuantityInput() {
    const quantityInput = document.getElementById('quantity-input');
    quantityInput.disabled = false;
    quantityInput.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
    quantityInput.classList.add('bg-white', 'text-black', 'cursor-text');
    quantityInput.focus();
    calculateEstimates();
}

function calculateEstimates() {
    const currentQty = parseFloat(document.getElementById('modal-quantity-value').value);
    const adjustment = parseFloat(document.getElementById('quantity-input').value);
    const price = parseFloat(document.getElementById('modal-price-value').value);
    const allocatedBudget = parseFloat(document.getElementById('modal-allocated-budget').value);

    if (isNaN(adjustment)) return;

    const newQty = currentQty + adjustment;
    const estimatedBudget = newQty * price;

    if (estimatedBudget > allocatedBudget) {
        Swal.fire({
            icon: 'warning',
            title: 'Budget Exceeded',
            text: `You cannot exceed the allocated budget of ₱${allocatedBudget.toLocaleString()}.`
        });
        document.getElementById('quantity-input').value = 0;
        document.getElementById('budget-estimate').textContent = (currentQty * price).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return;
    }

    document.getElementById('budget-estimate').textContent = estimatedBudget.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    let mode = '';
    if (estimatedBudget <= 50000) {
        mode = 'Shopping';
    } else if (estimatedBudget <= 250000) {
        mode = 'Small Value Procurement';
    } else {
        mode = 'Public Bidding';
    }

    document.getElementById('mode-of-procurement').textContent = mode;
}

function submitDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the PPMP item.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    });
}
</script>
</x-layouts.app>
