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

    <div class="flex justify-center items-center min-h-screen bg-gray-100">
        <div class="bg-white shadow-xl rounded-2xl w-full max-w-2xl p-8">
            
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edit User</h2>
                <p class="text-gray-500 text-sm">Update the user's information below</p>
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name" class="font-semibold text-gray-700 text-sm">Name</label>
                        <input type="text" name="name" id="name" 
                               value="{{ old('name', $user->name) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>

                    <!-- Email (readonly) -->
                    <div class="form-group">
                        <label for="email_display" class="font-semibold text-gray-700 text-sm">Email</label>
                        <input type="text" id="email_display" 
                               value="{{ $user->email }}" readonly
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500">
                        <input type="hidden" name="email" value="{{ $user->email }}">
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="font-semibold text-gray-700 text-sm">New Password</label>
                        <input type="password" name="password" id="password" 
                               placeholder="Leave blank to keep current"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <small class="text-xs text-gray-500">Minimum 12 characters, with uppercase, lowercase, number & symbol</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="font-semibold text-gray-700 text-sm">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               placeholder="Confirm new password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>

                    <!-- Role (readonly full width) -->
                    <div class="form-group md:col-span-2">
                        <label for="role_display" class="font-semibold text-gray-700 text-sm">Department / Role</label>
                        <input type="text" id="role_display" 
                               value="{{ $user->role }}" readonly
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500">
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('users.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        input[readonly] {
            cursor: not-allowed;
        }
    </style>
</x-layouts.app>
