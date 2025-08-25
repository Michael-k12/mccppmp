<x-layouts.app :title="'Edit'">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            font-size: 14px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }

        .excel-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        .excel-table input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            background-color: #f9f9f9;
        }

        .alert {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid #fca5a5;
        }

        h2 {
            font-size: 22px;
            font-weight: bold;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
        }

        .btn-update {
            background-color: #2563eb;
        }

        .btn-update:hover {
            background-color: #1d4ed8;
        }

        .btn-cancel {
            background-color: #6b7280;
        }

        .btn-cancel:hover {
            background-color: #4b5563;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-add {
            background-color: #22c55e;
        }

        .btn-add:hover {
            background-color: #16a34a;
        }

        .btn-decrease {
            background-color: #facc15;
            color: black;
        }

        .btn-decrease:hover {
            background-color: #eab308;
        }

        .flex-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>

    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="flex-header">
            <h2>Edit Project Procurement Plan</h2>
            <div class="action-buttons">
                <button type="button" onclick="adjust('add')" class="btn btn-add">Add</button>
                <button type="button" onclick="adjust('decrease')" class="btn btn-decrease">Subtract</button>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="edit-ppmp-form" action="{{ route('ppmps.update', $ppmp->id) }}" method="POST">
            @csrf
            @method('PUT')

            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Classification</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Estimated Budget</th>
                        <th>Mode of Procurement</th>
                        <th>Milestone</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" value="{{ $ppmp->classification }}" readonly></td>
                        <td><input type="text" value="{{ $ppmp->description }}" readonly></td>
                        <td><input type="text" value="{{ $ppmp->unit }}" readonly></td>

                        <td id="quantity_cell">
                            <input type="number" id="quantity_display" value="{{ $ppmp->quantity }}" readonly>
                            <input type="hidden" id="original_quantity" value="{{ $ppmp->quantity }}">
                        </td>
                        <td id="price_cell">
                            <input type="number" step="0.01" id="price_display" value="{{ $ppmp->price }}" readonly>
                            <input type="hidden" id="original_price" value="{{ $ppmp->price }}">
                        </td>
                        <td>
                            <input type="number" id="estimated_budget" readonly>
                        </td>
                        <td>
                            <input type="text" name="mode_of_procurement" id="mode_of_procurement" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('Y, F j') }}" readonly>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Final values to be submitted -->
            <input type="hidden" name="quantity" id="final_quantity">
            <input type="hidden" name="price" id="final_price">

            <div class="button-group justify-end">
                <button type="submit" class="btn btn-update">Update</button>
                <a href="{{ route('ppmp.manage') }}" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
  
    <script>
        let adjustmentMode = 'add'; // Default mode

        function adjust(action) {
            adjustmentMode = action;

            const quantityCell = document.getElementById('quantity_cell');
            const priceCell = document.getElementById('price_cell');

            if (!document.getElementById('adjust_quantity')) {
                const qtyInput = document.createElement('input');
                qtyInput.type = 'number';
                qtyInput.id = 'adjust_quantity';
                qtyInput.placeholder = "";
                qtyInput.style.marginTop = "6px";
                qtyInput.style.backgroundColor = "#ffffff";
                quantityCell.appendChild(qtyInput);
                qtyInput.addEventListener('input', calculateBudget);
            }

            if (!document.getElementById('adjust_price')) {
                const priceInput = document.createElement('input');
                priceInput.type = 'number';
                priceInput.step = "0.01";
                priceInput.id = 'adjust_price';
                priceInput.placeholder = "";
                priceInput.style.marginTop = "6px";
                priceInput.style.backgroundColor = "#ffffff";
                priceCell.appendChild(priceInput);
                priceInput.addEventListener('input', calculateBudget);
            }

            document.getElementById('adjust_quantity').focus();
        }

        function calculateBudget() {
            const originalQty = parseFloat(document.getElementById('original_quantity').value) || 0;
            const inputQty = parseFloat(document.getElementById('adjust_quantity')?.value) || 0;
            const adjustQty = adjustmentMode === 'decrease' ? -Math.abs(inputQty) : Math.abs(inputQty);
            const finalQty = originalQty + adjustQty;

            const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
            const inputPrice = parseFloat(document.getElementById('adjust_price')?.value) || 0;
            const adjustPrice = adjustmentMode === 'decrease' ? -Math.abs(inputPrice) : Math.abs(inputPrice);
            const finalPrice = originalPrice + adjustPrice;

            const total = finalQty * finalPrice;

            document.getElementById('final_quantity').value = Math.max(0, finalQty);
            document.getElementById('final_price').value = Math.max(0, finalPrice.toFixed(2));
            document.getElementById('estimated_budget').value = total.toFixed(2);

            const modeInput = document.getElementById('mode_of_procurement');
            if (total < 100000) {
                modeInput.value = "Small Value Procurement";
            } else if (total >= 100000 && total < 500000) {
                modeInput.value = "Shopping";
            } else {
                modeInput.value = "Bidding";
            }
        }

        document.getElementById('edit-ppmp-form').addEventListener('submit', calculateBudget);
        window.addEventListener('DOMContentLoaded', calculateBudget);
    </script>
</x-layouts.app>
