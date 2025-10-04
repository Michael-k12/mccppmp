<x-layouts.app :title="'Manage Users'">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SweetAlert Success -->
    @if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                title: 'Success!',
                text: @json(session('success')),
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
            });
        });
    </script>
    @endif

    <!-- SweetAlert Error -->
    @if($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Open modal first
            openModal();

            Swal.fire({
                title: 'Error!',
                html: `
                    <ul class="text-left">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                icon: 'error',
                confirmButtonText: 'OK',
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
                            <button type="submit" onclick="return confirm('Delete this user?')" class="delete-btn">Delete</button>
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
            <button class="close-btn" onclick="closeModal()">&times;</button>
            <h2>Add New User</h2>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <input type="password" name="password" placeholder="Password" required>
                <small class="text-gray-500 text-sm mb-2">
                    Password must be at least 12 characters and include uppercase, lowercase, numbers, and symbols.
                </small>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="BSED" {{ old('role') == 'BSED' ? 'selected' : '' }}>BSED</option>
                    <option value="BSBA" {{ old('role') == 'BSBA' ? 'selected' : '' }}>BSBA</option>
                    <option value="BSIT" {{ old('role') == 'BSIT' ? 'selected' : '' }}>BSIT</option>
                    <option value="BSHM" {{ old('role') == 'BSHM' ? 'selected' : '' }}>BSHM</option>
                    <option value="LIBRARY" {{ old('role') == 'LIBRARY' ? 'selected' : '' }}>Library</option>
                    <option value="NURSE" {{ old('role') == 'NURSE' ? 'selected' : '' }}>Nurse</option>
                </select>

                <div class="modal-actions">
                    <button type="submit" class="submit-btn">Add User</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Styles -->
    <style>
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-row h1 {
            font-size: 26px;
            font-weight: 700;
            color: #1f2937;
        }

        .add-button {
            background-color: #2563eb;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .add-button:hover {
            background-color: #1e40af;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .user-table th, .user-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .user-table thead {
            background-color: #3b82f6;
            color: #fff;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-btn {
            background-color: #3b82f6;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.2s;
        }

        .edit-btn:hover {
            background-color: #1e40af;
        }

        .delete-btn {
            background-color: #ef4444;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .delete-btn:hover {
            background-color: #b91c1c;
        }

        /* Modal */
        .modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .modal.hidden {
            display: none;
        }

        .modal-content {
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .modal-content h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .modal-content input, .modal-content select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            width: 100%;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 8px;
        }

        .submit-btn {
            background-color: #22c55e;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .submit-btn:hover {
            background-color: #15803d;
        }

        .cancel-btn {
            background-color: #e5e7eb;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .cancel-btn:hover {
            background-color: #d1d5db;
        }

        .close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 24px;
            font-weight: 700;
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            transition: 0.2s;
        }

        .close-btn:hover {
            color: #111827;
        }

        .modal-content small {
            color: #6b7280;
            font-size: 13px;
        }
    </style>

    <!-- Scripts -->
    <script>
        function openModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }
    </script>
</x-layouts.app>
