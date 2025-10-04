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

    <div class="container mx-auto px-6 py-10">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">

            <h2 class="text-2xl font-semibold text-center mb-6">Edit User</h2>

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium mb-1">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
                </div>

                <!-- Email (readonly) -->
                <div class="mb-4">
                    <label for="email_display" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="text" id="email_display" value="{{ $user->email }}" readonly
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 text-sm">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                </div>

                <!-- Role (readonly) -->
                <div class="mb-4">
                    <label for="role_display" class="block text-gray-700 font-medium mb-1">Department / Role</label>
                    <input type="text" id="role_display" value="{{ $user->role }}" readonly
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 text-sm">
                    <input type="hidden" name="role" value="{{ $user->role }}">
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-1">New Password</label>
                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
                    <small class="text-gray-500 text-xs">Minimum 12 characters, including uppercase, lowercase, number & symbol</small>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm New Password"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm">
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-2">
                    <a href="{{ route('users.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Inter', sans-serif;
        }
    </style>
</x-layouts.app>
