<x-layouts.app :title="'Manage Users'">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <div class="container mx-auto px-4 py-6">
        <div class="header-row">
            <h1>Manage Users</h1>
            <button class="add-button" onclick="openModal()">+ Add User</button>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td class="action-buttons">
                        <a href="{{ route('users.edit', $user) }}" class="edit-btn">Edit</a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this user?')" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="addUserModal" class="modal hidden">
        <div class="modal-content">
            <h2>Add New User</h2>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
<small class="text-gray-500 text-sm">
    Password must be at least 12 characters and include uppercase, lowercase, numbers, and symbols.
</small>

<input type="password" name="password_confirmation" placeholder="Confirm Password" required>


                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="BSED">BSED</option>
                    <option value="BSBA">BSBA</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSHM">BSHM</option>
                    <option value="LIBRARY">Library</option>
                    <option value="NURSE">Nurse</option>
                </select>

                <div class="modal-actions">
                    <button type="submit" class="submit-btn">Add</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Style -->
    <style>
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-row h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .add-button {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .add-button:hover {
            background-color: #1d4ed8;
        }

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

        .user-table thead {
            background-color: #6c9dffff;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-btn {
            background-color: #3b82f6;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }

        .delete-btn {
            background-color: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .modal-content small {
    display: block;
    margin-top: -5px;
    margin-bottom: 10px;
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

        .modal.hidden {
            display: none;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .modal-content h2 {
            margin-bottom: 10px;
        }

        .modal-content input, .modal-content select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

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
    </style>

    <!-- Script -->
    <script>
        function openModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }
    </script>
</x-layouts.app>
