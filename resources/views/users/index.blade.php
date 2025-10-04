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

        <div class="modal-header">
            <h2>Add New User</h2>
            <p class="modal-subtitle">Fill in the details to create a new user</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="modal-form">
            @csrf

            <!-- Name & Email -->
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" placeholder="Enter full name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Enter email address" value="{{ old('email') }}" required>
                </div>
            </div>

            <!-- Password & Confirm Password -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                    <small>Password must be at least 12 characters, with uppercase, lowercase, number & symbol.</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm password" required>
                </div>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label for="role">Role / Department</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="BSED" {{ old('role') == 'BSED' ? 'selected' : '' }}>BSED</option>
                    <option value="BSBA" {{ old('role') == 'BSBA' ? 'selected' : '' }}>BSBA</option>
                    <option value="BSIT" {{ old('role') == 'BSIT' ? 'selected' : '' }}>BSIT</option>
                    <option value="BSHM" {{ old('role') == 'BSHM' ? 'selected' : '' }}>BSHM</option>
                    <option value="LIBRARY" {{ old('role') == 'LIBRARY' ? 'selected' : '' }}>Library</option>
                    <option value="NURSE" {{ old('role') == 'NURSE' ? 'selected' : '' }}>Nurse</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-submit">Add User</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
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

        /* Modal overlay */
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

/* Hide modal */
.modal.hidden {
    display: none;
}

/* Modal card */
.modal-content {
    background: #fff;
    padding: 2rem;
    border-radius: 16px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    position: relative;
    animation: slideDown 0.3s ease-out;
}

/* Slide animation */
@keyframes slideDown {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Close button */
.close-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    font-size: 26px;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    transition: 0.2s;
}
.close-btn:hover {
    color: #111827;
}

/* Header */
.modal-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}
.modal-header .modal-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 20px;
}

/* Form layout */
.modal-form .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.form-row .form-group {
    flex: 1;
    min-width: 240px;
}

.modal-form label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}
.modal-form input,
.modal-form select {
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 14px;
    transition: 0.2s;
}
.modal-form input:focus,
.modal-form select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59,130,246,0.2);
}
.modal-form small {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

/* Buttons */
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
.btn-submit {
    background: linear-gradient(90deg,#3b82f6,#2563eb);
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
}
.btn-submit:hover {
    background: linear-gradient(90deg,#2563eb,#1e40af);
}
.btn-cancel {
    background: #e5e7eb;
    color: #374151;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: 0.2s;
}
.btn-cancel:hover {
    background: #d1d5db;
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
