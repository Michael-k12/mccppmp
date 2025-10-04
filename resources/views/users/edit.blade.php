<x-layouts.app :title="'Edit User'">
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
                showConfirmButton: false
            });
        });
    </script>
    @endif

    <!-- SweetAlert Error -->
    @if($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
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
                confirmButtonText: 'OK'
            });
        });
    </script>
    @endif

    <div class="container mx-auto px-4 py-6">
        <div class="header-row">
            <h1>Edit User</h1>
            <a href="{{ route('users.index') }}" class="back-btn">← Back</a>
        </div>

        <div class="modal-content mx-auto">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Name" required>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email" required>

                <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
                <small class="text-gray-500 text-sm mb-2">
                    Password must be at least 12 characters and include uppercase, lowercase, numbers, and symbols.
                </small>
                <input type="password" name="password_confirmation" placeholder="Confirm New Password">

                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="BSED" {{ old('role', $user->role) == 'BSED' ? 'selected' : '' }}>BSED</option>
                    <option value="BSBA" {{ old('role', $user->role) == 'BSBA' ? 'selected' : '' }}>BSBA</option>
                    <option value="BSIT" {{ old('role', $user->role) == 'BSIT' ? 'selected' : '' }}>BSIT</option>
                    <option value="BSHM" {{ old('role', $user->role) == 'BSHM' ? 'selected' : '' }}>BSHM</option>
                    <option value="LIBRARY" {{ old('role', $user->role) == 'LIBRARY' ? 'selected' : '' }}>Library</option>
                    <option value="NURSE" {{ old('role', $user->role) == 'NURSE' ? 'selected' : '' }}>Nurse</option>
                </select>

                <div class="modal-actions">
                    <button type="submit" class="submit-btn">Update User</button>
                    <a href="{{ route('users.index') }}" class="cancel-btn">Cancel</a>
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
            margin-bottom: 25px;
        }
        .header-row h1 {
            font-size: 26px;
            font-weight: 700;
            color: #1f2937;
        }
        .back-btn {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .back-btn:hover {
            color: #1e40af;
        }
        .modal-content {
            background: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
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
            margin-top: 10px;
        }
        .submit-btn {
            background-color: #2563eb;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }
        .submit-btn:hover {
            background-color: #1e40af;
        }
        .cancel-btn {
            background-color: #e5e7eb;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            color: #111827;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .cancel-btn:hover {
            background-color: #d1d5db;
        }
        .modal-content small {
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</x-layouts.app>
