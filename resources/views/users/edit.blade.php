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

    <div class="container mx-auto px-4 py-12 flex justify-center">
        <div class="bg-white shadow-lg rounded-2xl p-10 w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-semibold text-gray-800">Edit User</h1>
                <p class="text-gray-500 mt-1">Update user details securely</p>
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" placeholder="Full Name" required
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                </div>

                <!-- Email (readonly) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="email">Email</label>
                    <input type="email" value="{{ $user->email }}" readonly
                        class="w-full border border-gray-200 bg-gray-100 rounded-xl px-4 py-3 text-gray-500 cursor-not-allowed">
                </div>

                <!-- Role (readonly) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="role">Department / Role</label>
                    <input type="text" value="{{ $user->role }}" readonly
                        class="w-full border border-gray-200 bg-gray-100 rounded-xl px-4 py-3 text-gray-500 cursor-not-allowed">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="password">New Password</label>
                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                    <small class="text-gray-400 text-sm mt-1 block">
                        Minimum 12 characters, including uppercase, lowercase, number & symbol
                    </small>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm New Password"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                        Update User
                    </button>
                    <a href="{{ route('users.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-6 py-3 rounded-xl transition flex items-center justify-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        body {
            background-color: #f4f5f7;
            font-family: 'Inter', sans-serif;
        }
    </style>
</x-layouts.app>
