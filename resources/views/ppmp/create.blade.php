<x-layouts.app :title="'Create PPMP'">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Success & Error Notifications --}}
@if(session('duplicate_error'))
<script>
Swal.fire({
    icon: 'warning',
    title: 'Duplicate Item',
    text: '{{ session("duplicate_error") }}'
});
</script>
@endif

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session("success") }}'
});
</script>
@endif

<style>
.container {
    max-width: 1100px;
    margin: auto;
    padding: 2rem;
    font-family: Arial, sans-serif;
}
.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
h2 {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}
.search-wrapper { position: relative; }
.search-bar {
    width: 600px;
    padding: 8px 35px 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
.dots-loader {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
}
.dots-loader span {
    display: inline-block;
    width: 6px;
    height: 6px;
    margin: 0 2px;
    background-color: #1e40af;
    border-radius: 50%;
    animation: bounce 0.6s infinite alternate;
}
.dots-loader span:nth-child(2) { animation-delay: 0.2s; }
.dots-loader span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { to { transform: translateY(-5px); opacity: 0.5; } }

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 6px;
    overflow: hidden;
}
th, td {
    padding: 10px 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 14px;
}
th { background-color: #f9fafb; font-weight: bold; }
.btn {
    padding: 6px 12px;
    border: none;
    background-color: #1e40af;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}
.btn:hover { background-color: #1d4ed8; }

.modal {
    display: none;
    position: fixed;
    z-index: 99;
    padding-top: 60px;
    left: 0; top: 0;
    width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fff;
    margin: auto;
    padding: 20px;
    width: 400px;
    border-radius: 8px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
}
.modal input, .modal select {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
.modal-footer { text-align: right; }
.close { cursor: pointer; font-size: 20px; }
</style>

<div class="container">
@if ($isProposalActive)
    <!-- Header Row -->
    <div class="header-row">
        <h2>Directory of Item's</h2>
        <div class="search-wrapper">
            <input type="text" class="search-bar" placeholder="Search..." onkeyup="filterTable()" id="searchInput">
            <div class="dots-loader" id="dotsLoader">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <!-- Item Table -->
    <table id="itemTable">
        <thead>
            <tr>
                <th>Classification</th>
                <th>Description</th>
                <th>Unit</th>
                <th>Price</th>
                <th style="width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->classification }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>₱{{ number_format($item->price, 2) }}</td>
                    <td>
                        <button class="btn" onclick="openModal('{{ $item->id }}', '{{ $item->description }}', '{{ $item->classification }}', '{{ $item->unit }}', '{{ $item->price }}')">Add</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="bg-red-100 border border-red-300 text-red-800 p-6 rounded text-center">
        <strong>Project Proposal has ended.</strong><br>
        You can no longer add Proposal items at this time.
    </div>
@endif
</div>

<!-- Add to PPMP Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            Add Item
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="{{ route('ppmp.store') }}" id="addPPMPForm">
            @csrf
            <input type="hidden" id="remainingBudget" value="{{ $remainingBudget }}">

            <input type="hidden" name="classification" id="modal_classification">
            <input type="hidden" name="description" id="modal_description">
            <input type="hidden" name="unit" id="modal_unit">
            <input type="hidden" name="price" id="modal_price">

            <!-- Quantity -->
<label>Quantity</label>
<input type="text" name="quantity" id="modal_quantity" required oninput="calculateBudget(this)">

<!-- Estimated Budget -->
<label>Estimated Budget</label>
<input type="text" name="estimated_budget" id="modal_budget" readonly>


            <!-- Mode of Procurement Dropdown -->
            <label>Mode of Procurement</label>
            <select name="mode_of_procurement" id="modal_procurement" required>
                <option value="">Select Mode</option>
                <option value="Small Value Procurement">Small Value Procurement</option>
                <option value="Shopping">Shopping</option>
                <option value="Bidding">Bidding</option>
            </select>

            <!-- Schedule / Milestone -->
<label>Schedule / Milestone</label>
<select name="milestone_month" id="modal_milestone_date" class="form-control" required>
    <option value="">Select Month</option>
    @foreach ([
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December'
    ] as $num => $month)
        <option value="{{ $num }}">{{ $month }}</option>
    @endforeach
</select>


            <div class="modal-footer">
                <button type="button" class="btn" style="background-color: #6b7280;" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Add New Item Modal -->
<div id="addItemModal" class="modal">
    <div class="modal-content">
        <h2>Add New Item</h2>
        <form method="POST" action="{{ route('items.store') }}">
            @csrf
            <input id="classification" name="classification" placeholder="Classification" required autocomplete="off">
            <input type="text" name="description" placeholder="General Description" required autocomplete="off">
            <input id="unit" name="unit" placeholder="Unit" required autocomplete="off">
            <input type="number" step="0.01" name="price" placeholder="Price" required autocomplete="off">

            <div class="modal-footer">
                <button type="button" class="btn" style="background-color: #6b7280;" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn">Add</button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
function openModal(id, description, classification, unit, price) {
    document.getElementById('modal_description').value = description;
    document.getElementById('modal_classification').value = classification;
    document.getElementById('modal_unit').value = unit;
    document.getElementById('modal_price').value = price;
    document.getElementById('modal_quantity').value = '';
    document.getElementById('modal_budget').value = '';
    document.getElementById('modal_procurement').value = '';
    document.getElementById('modal_milestone_date').value = localStorage.getItem('selectedMonth') || '';
    document.getElementById('addModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addModal').style.display = 'none';
}

function calculateBudget() {
    const quantity = parseFloat(document.getElementById('modal_quantity').value) || 0;
    const price = parseFloat(document.getElementById('modal_price').value) || 0;
    const budget = quantity * price;
    const remainingBudget = parseFloat(document.getElementById('remainingBudget').value);

    document.getElementById('modal_budget').value = budget.toFixed(2);

    

    const addButton = document.querySelector('#addModal button[type="submit"]');

    if (budget > remainingBudget) {
        addButton.disabled = true;
        addButton.style.backgroundColor = '#9ca3af';
        document.getElementById('modal_budget').style.borderColor = 'red';

        Swal.fire({
            icon: 'error',
            title: 'Budget Exceeded',
            text: 'The item exceeds your remaining budget of ₱' + remainingBudget.toFixed(2)
        });
    } else {
        addButton.disabled = false;
        addButton.style.backgroundColor = '#1e40af';
        document.getElementById('modal_budget').style.borderColor = '#ccc';
    }
}

document.getElementById('modal_milestone_date').addEventListener('change', function() {
    localStorage.setItem('selectedMonth', this.value);
});

let searchTimeout;
function filterTable() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#itemTable tbody tr");
    const loader = document.getElementById('dotsLoader');

    if (input.trim() === "") {
        clearTimeout(searchTimeout);
        loader.style.display = 'none';
        rows.forEach(row => row.style.display = '');
        return;
    }

    clearTimeout(searchTimeout);
    loader.style.display = 'inline-block';
    rows.forEach(row => row.style.display = 'none');

    searchTimeout = setTimeout(() => {
        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(input)) {
                row.style.display = '';
                visibleCount++;
            }
        });
        loader.style.display = 'none';

        // Only prompt to add item if nothing is visible
        if (visibleCount === 0) {
            Swal.fire({
                icon: 'question',
                title: 'No item found',
                text: 'Would you like to add this item?',
                showCancelButton: true,
                confirmButtonText: 'Yes, add item',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) openAddModal(input);
                else if (result.dismiss === Swal.DismissReason.cancel) {
                    document.getElementById("searchInput").value = "";
                    rows.forEach(row => row.style.display = '');
                }
            });
        }
    }, 500);
}

function openAddModal(prefill = '') {
    document.getElementById('addItemModal').style.display = 'block';
    if (prefill) document.querySelector('#addItemModal input[name="description"]').value = prefill;
}
function closeAddModal() { document.getElementById('addItemModal').style.display = 'none'; }

window.onclick = function(event) {
    if (event.target === document.getElementById("addModal")) closeModal();
    if (event.target === document.getElementById("addItemModal")) closeAddModal();
};

document.addEventListener("DOMContentLoaded", function () {
    new TomSelect("#classification", {
        create: true,
        sortField: { field: "text", direction: "asc" },
        options: [
            {value: "Office Supplies", text: "Office Supplies"},
            {value: "Equipment", text: "Equipment"},
            {value: "Furniture", text: "Furniture"},
            {value: "IT Equipment", text: "IT Equipment"},
            {value: "Other", text: "Other"}
        ]
    });

    new TomSelect("#unit", {
        create: true,
        sortField: { field: "text", direction: "asc" },
        options: [
            {value: "pcs", text: "pcs"},
            {value: "box", text: "box"},
            {value: "ream", text: "ream"},
            {value: "set", text: "set"},
            {value: "unit", text: "unit"},
            {value: "pack", text: "pack"},
            {value: "lot", text: "lot"},
            {value: "roll", text: "roll"}
        ]
    });
});
function formatNumber(value) {
    const parts = value.toString().split('.');
    let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    let decimalPart = parts[1] ? '.' + parts[1].slice(0,2) : '';
    return integerPart + decimalPart;
}

function calculateBudget(quantityInput = null) {
    const quantity = parseFloat(document.getElementById('modal_quantity').value.replace(/,/g, '')) || 0;
    const price = parseFloat(document.getElementById('modal_price').value) || 0;
    const budget = quantity * price;
    const remainingBudget = parseFloat(document.getElementById('remainingBudget').value);

    // Update Estimated Budget with formatted number
    document.getElementById('modal_budget').value = formatNumber(budget);

    // Disable add button if budget exceeded
    const addButton = document.querySelector('#addModal button[type="submit"]');
    if (budget > remainingBudget) {
        addButton.disabled = true;
        addButton.style.backgroundColor = '#9ca3af';
        document.getElementById('modal_budget').style.borderColor = 'red';

        Swal.fire({
            icon: 'error',
            title: 'Budget Exceeded',
            text: 'The item exceeds your remaining budget of ₱' + formatNumber(remainingBudget)
        });
    } else {
        addButton.disabled = false;
        addButton.style.backgroundColor = '#1e40af';
        document.getElementById('modal_budget').style.borderColor = '#ccc';
    }

    // Format quantity input with commas while typing
    const quantityField = document.getElementById('modal_quantity');
    if(quantityField.value) {
        let numericValue = quantityField.value.replace(/,/g, '');
        if(!isNaN(numericValue) && numericValue !== '') {
            quantityField.value = formatNumber(numericValue);
        }
    }
}
document.getElementById('addPPMPForm').addEventListener('submit', function(e) {
    // Remove commas from quantity and estimated budget before submitting
    const quantityInput = document.getElementById('modal_quantity');
    const budgetInput = document.getElementById('modal_budget');

    quantityInput.value = quantityInput.value.replace(/,/g, '');
    budgetInput.value = budgetInput.value.replace(/,/g, '');
});


</script>
</x-layouts.app>