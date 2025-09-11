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
    .add-item-btn {
    background-color: #007bff; /* Blue */
    color: white;
    font-size: 14px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transition: background-color 0.3s ease, transform 0.2s ease;
}
.add-item-btn:hover {
    background-color: #0056b3; /* Darker Blue */
    transform: scale(1.05);
}
.add-item-btn:active {
    transform: scale(0.98);
}
.modal {
    display: none;
    position: fixed;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-in-out;
}
.modal-content {
    background: #fff;
    padding: 24px;
    width: 420px;
    border-radius: 12px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
    animation: scaleIn 0.25s ease-in-out;
    position: relative;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.modal-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
}
.close-btn {
    background: transparent;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #555;
    transition: 0.2s;
}
.close-btn:hover {
    color: #e74c3c;
}

.modal-subtitle {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}
.quantity-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.quantity-buttons {
    display: flex;
    gap: 12px;
}
.quantity-buttons .btn {
    padding: 8px 18px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: 0.2s ease-in-out;
}
.add-btn {
    background-color: #28a745;
    color: white;
}
.add-btn:hover {
    background-color: #218838;
}
.subtract-btn {
    background-color: #e74c3c;
    color: white;
}
.subtract-btn:hover {
    background-color: #c0392b;
}
#quantity-input {
    width: 100px;
    text-align: center;
    padding: 10px;
    font-size: 22px;
    font-weight: bold;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #f8f9fa;
    color: #555;
    transition: 0.2s;
}
#quantity-input:enabled {
    background-color: #fff;
    color: #000;
    border-color: #3b82f6;
}
.info-box {
    background-color: #f7f9fc;
    padding: 12px;
    margin-top: 16px;
    border: 1px solid #e0e6ef;
    border-radius: 8px;
    font-size: 14px;
    color: #444;
}
.info-box p {
    display: flex;
    justify-content: space-between;
    margin: 6px 0;
}
.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 18px;
}
.action-buttons .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s;
}
.cancel-btn {
    background-color: #e5e7eb;
    color: #333;
}
.cancel-btn:hover {
    background-color: #d1d5db;
}
.save-btn {
    background-color: #3b82f6;
    color: white;
}
.save-btn:hover {
    background-color: #2563eb;
}
.swal2-container {
    z-index: 20000 !important;
}
</style>
<div class="flex justify-between items-center mb-4">
    <!-- Left Side: Title -->
    <h1 class="text-2xl font-bold">Manage Project Procurement Plan</h1>

    <!-- Right Side: Budgets + Button -->
    <div class="flex items-center gap-6">
        <p class="text-sm text-gray-600">
            Allocated Budget:
            <span id="allocatedBudget" class="font-semibold text-green-600">
                ₱{{ number_format($allocatedBudget, 2) }}
            </span> |
            Remaining Budget:
            <span id="remainingBudget" class="font-semibold {{ $remainingBudget < 0 ? 'text-red-600' : 'text-blue-600' }}">
                ₱{{ number_format($remainingBudget, 2) }}
            </span>
        </p>

        <a href="{{ route('ppmp.create') }}" class="add-item-btn">
            + Add New Item
        </a>
    </div>
</div>


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
    <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('Y F') }}
    </td>
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
<div id="quantityModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Quantity</h2>
            <button class="close-btn" onclick="closeModal()">×</button>
        </div>
        <p id="modal-item-name" class="modal-subtitle"></p>
        <form id="update-form" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="current_quantity" id="modal-quantity-value">
            <input type="hidden" id="modal-price-value">
            <input type="hidden" id="modal-allocated-budget" value="0">

            <div class="quantity-container">
                <div class="quantity-buttons">
    <button type="button" class="btn add-btn" onclick="setQuantityMode('add')">+ Add</button>
    <button type="button" class="btn subtract-btn" onclick="setQuantityMode('subtract')">− Subtract</button>
</div>
<input type="number" id="quantity-input" name="adjustment" value="0" oninput="calculateEstimates()" disabled>
<input type="hidden" id="quantity-mode" name="mode" value="add">
            </div>
            <div class="info-box">
                <p>
                    <span>Estimated Budget:</span>
                    <strong>₱<span id="budget-estimate">0.00</span></strong>
                </p>
            </div>
            <div class="action-buttons">
                <button type="button" class="btn cancel-btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn save-btn">Save Changes</button>
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

    quantityInput.value = ''; // Make it blank
    quantityHiddenInput.value = currentQty;
    priceInput.value = price;
    allocatedBudgetInput.value = allocatedBudget;

    quantityInput.disabled = true;
    quantityInput.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
    quantityInput.classList.remove('bg-white', 'text-black', 'cursor-text');

    itemNameField.textContent = `Editing: ${itemName}`;
    form.action = `/ppmps/${id}/update-quantity`;

    document.getElementById('budget-estimate').textContent = '0.00';
    document.getElementById('quantityModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('quantityModal').style.display = 'none';
}

let currentMode = 'add';
function setQuantityMode(mode) {
    currentMode = mode;
    const quantityInput = document.getElementById('quantity-input');
    const modeInput = document.getElementById('quantity-mode');

    quantityInput.disabled = false;
    quantityInput.value = ''; // Keep it blank
    modeInput.value = mode;

    if (mode === 'add') {
        quantityInput.style.borderColor = '#28a745'; // Green for add
    } else {
        quantityInput.style.borderColor = '#e74c3c'; // Red for subtract
    }
    quantityInput.focus();
    calculateEstimates();
}
function calculateEstimates() {
    const currentQty = parseFloat(document.getElementById('modal-quantity-value').value);
   const adjustment = document.getElementById('quantity-input').value === ''
    ? 0
    : Math.abs(parseFloat(document.getElementById('quantity-input').value));

    const price = parseFloat(document.getElementById('modal-price-value').value);
    const allocatedBudget = parseFloat(document.getElementById('modal-allocated-budget').value);
    const previewQty = currentMode === 'add' ? currentQty + adjustment : currentQty - adjustment;
    if (previewQty < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Quantity',
            text: 'Quantity cannot be less than 0.'
        });
        document.getElementById('quantity-input').value = 0;
        return;
    }
    const estimatedBudget = previewQty * price;
    if (estimatedBudget > allocatedBudget) {
        Swal.fire({
            icon: 'warning',
            title: 'Budget Exceeded',
            text: `You cannot exceed the allocated budget of ₱${allocatedBudget.toLocaleString()}.`
        });
        document.getElementById('quantity-input').value = ''; 
        return;
    }
    document.getElementById('budget-estimate').textContent = estimatedBudget.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
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
    function updateBudgets() {
    fetch("{{ route('ppmp.remaining-budget') }}")
        .then(response => response.json())
        .then(data => {
            const allocatedBudget = document.getElementById('allocatedBudget');
            const remainingBudget = document.getElementById('remainingBudget');

            // Update allocated budget
            allocatedBudget.textContent = `₱${parseFloat(data.allocatedBudget).toLocaleString()}`;

            // Update remaining budget
            remainingBudget.textContent = `₱${parseFloat(data.remainingBudget).toLocaleString()}`;

            // Change color based on remaining budget
            if (data.remainingBudget < 0) {
                remainingBudget.classList.remove('text-blue-600');
                remainingBudget.classList.add('text-red-600');
            } else {
                remainingBudget.classList.remove('text-red-600');
                remainingBudget.classList.add('text-blue-600');
            }
        })
        .catch(error => console.error('Error fetching budget:', error));
}

// Call this function whenever quantity or item is added
document.addEventListener('DOMContentLoaded', function() {
    // Update budgets every 3 seconds automatically
    setInterval(updateBudgets, 3000);
});

}
</script>
</x-layouts.app>