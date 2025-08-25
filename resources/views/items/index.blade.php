<x-layouts.app :title="'Manage Items'">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Success Alert --}}
    @if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                title: 'Success!',
                text: @json(session('success')),
                icon: 'success',
                timer: 1000,
                showConfirmButton: false
            });
        });
    </script>
    @endif

    {{-- Error Alert --}}
    @if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        });
    </script>
    @endif

    <div class="container mx-auto px-4 py-6">
        <div class="header-row">
            <h1>Manage Items</h1>
            <button class="add-button" onclick="openAddModal()">+ Add Item</button>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->classification }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>₱{{ number_format($item->price, 2) }}</td>
                    <td class="action-buttons">
                        <button class="edit-btn"
                            onclick="openEditModal({{ $item->id }}, '{{ $item->classification }}', '{{ $item->description }}', '{{ $item->unit }}', '{{ $item->price }}')">
                            Update
                        </button>
                        <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this item?')" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Add Item Modal --}}
    <div id="addItemModal" class="modal hidden">
        <div class="modal-content">
            <h2>Add New Item</h2>
            <form method="POST" action="{{ route('items.store') }}">
                @csrf

                {{-- Classification --}}
                <div class="dropdown-wrapper">
                    <input type="text" name="classification" placeholder="Classification"
                        autocomplete="off" spellcheck="false"
                        required onfocus="showDropdown(this, 'classification-list')"
                        oninput="filterDropdown(this, 'classification-list')">
                    <div id="classification-list" class="dropdown-list"></div>
                </div>

                {{-- Description --}}
                <div class="dropdown-wrapper">
                    <input type="text" name="description" placeholder="General Description"
                        value="{{ old('description') }}" autocomplete="off" spellcheck="false"
                        required onfocus="showDropdown(this, 'description-list')"
                        oninput="filterDropdown(this, 'description-list')">
                    <div id="description-list" class="dropdown-list"></div>
                </div>
                @error('description')
                    <p style="color:red; font-size:14px;">{{ $message }}</p>
                @enderror

                {{-- Unit --}}
                <div class="dropdown-wrapper">
                    <input type="text" name="unit" placeholder="Unit"
                        autocomplete="off" spellcheck="false"
                        required onfocus="showDropdown(this, 'unit-list')"
                        oninput="filterDropdown(this, 'unit-list')">
                    <div id="unit-list" class="dropdown-list"></div>
                </div>

                {{-- Price --}}
                <input type="number" step="0.01" name="price" placeholder="Price" required>

                <div class="modal-actions">
                    <button type="submit" class="submit-btn">Add</button>
                    <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

   {{-- Edit Price Modal --}}
<div id="editItemModal" class="modal hidden">
    <div class="modal-content">
        <h2>Edit Item Price</h2>
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id">

            <div>
                <label>Classification</label>
                <input type="text" id="edit-classification" disabled>
            </div>

            <div>
                <label>Description</label>
                <input type="text" id="edit-description" disabled>
            </div>

            <div>
                <label>Unit</label>
                <input type="text" id="edit-unit" disabled>
            </div>

            <div>
                <label>Price</label>
                <input type="number" step="0.01" name="price" id="edit-price" required>
            </div>

            <div class="modal-actions">
                <button type="submit" class="submit-btn">Update Price</button>
                <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>


    <style>
        /* Header Row */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-row h1 { font-size: 24px; font-weight: bold; }
        .add-button {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .add-button:hover { background-color: #1d4ed8; }

        /* Table */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .user-table th, .user-table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .user-table thead { background-color: #dbeafe; }
        .action-buttons { display: flex; gap: 10px; }
        .edit-btn {
            background-color: #3b82f6;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .delete-btn {
            background-color: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 50;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .modal.hidden { display: none; }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .modal-content h2 { margin-bottom: 10px; }
        .modal-content input {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 100%;
            margin-bottom: 10px;
        }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
        .submit-btn {
            background-color: #22c55e;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .cancel-btn {
            background-color: #e5e7eb;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        /* Dropdown */
        .dropdown-wrapper { position: relative; width: 100%; }
        .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            border: 1px solid #ccc;
            border-top: none;
            background: white;
            max-height: 150px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-sizing: border-box;
        }
        .dropdown-list div {
            padding: 8px;
            cursor: pointer;
        }
        .dropdown-list div:hover { background-color: #f0f0f0; }
    </style>

    <script>
        function openAddModal() {
            document.getElementById('addItemModal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('addItemModal').classList.add('hidden');
        }
        function openEditModal(id, classification, description, unit, price) {
            const form = document.getElementById('editForm');
            form.action = `/items/${id}`;
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-classification').value = classification;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-unit').value = unit;
            document.getElementById('edit-price').value = price;
            document.getElementById('editItemModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editItemModal').classList.add('hidden');
        }

        // Dropdown logic
        const options = {
            classification: ["Office Supplies", "IT Equipment", "Furniture", "Cleaning Materials"],
            description: ["Ballpen", "Laptop", "Office Chair", "Printer Ink"],
            unit: ["pcs", "box", "set", "ream","sets","unit","roll","pack","bottle","gallon","piece","pair","kit","case","bag"]
        };
        function showDropdown(input, listId) {
            filterDropdown(input, listId);
        }
        function filterDropdown(input, listId) {
            const list = document.getElementById(listId);
            const field = listId.split('-')[0];
            const value = input.value.toLowerCase();
            const filtered = options[field].filter(opt => opt.toLowerCase().includes(value));
            list.innerHTML = filtered.map(opt => `<div onclick="selectOption('${opt.replace(/'/g, "\\'")}', '${input.name}')">${opt}</div>`).join('');
            list.style.display = filtered.length ? 'block' : 'none';
        }
        function selectOption(value, fieldName) {
            const input = document.querySelector(`input[name="${fieldName}"]`);
            input.value = value;
            document.querySelectorAll('.dropdown-list').forEach(dl => dl.style.display = 'none');
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-wrapper')) {
                document.querySelectorAll('.dropdown-list').forEach(dl => dl.style.display = 'none');
            }
        });
    </script>
</x-layouts.app>
