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
        <div class="flex flex-col lg:flex-row gap-8 justify-center">

            <!-- Left Panel: Info / Illustration -->
            <div class="lg:w-1/3 flex flex-col justify-center items-center text-center bg-gradient-to-br from-blue-50 to-white rounded-2xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Edit User</h2>
                <p class="text-gray-500 text-sm mb-4">Update user credentials safely</p>
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Edit User" class="w-24 h-24 object-contain">
            </div>

            <!-- Right Panel: Form -->
            <div class="lg:w-2/3 bg-white shadow-lg rounded-2xl p-6">
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1" for="name">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" placeholder="Full Name" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm transition">
                    </div>

                    <!-- Email (readonly) -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1" for="email">Email</label>
                        <input type="email" value="{{ $user->email }}" readonly
                            class="w-full border border-gray-200 bg-gray-100 rounded-lg px-3 py-2 text-gray-500 cursor-not-allowed text-sm">
                    </div>

                    <!-- Role (readonly) -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1" for="role">Department / Role</label>
                        <input type="text" value="{{ $user->role }}" readonly
                            class="w-full border border-gray-200 bg-gray-100 rounded-lg px-3 py-2 text-gray-500 cursor-not-allowed text-sm">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1" for="password">New Password</label>
                        <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm transition">
                        <small class="text-gray-400 text-xs mt-1 block">
                            Minimum 12 characters, including uppercase, lowercase, number & symbol
                        </small>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-1" for="password_confirmation">Confirm New Password</label>
                        <input type="password" name="password_confirmation" placeholder="Confirm New Password"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm transition">
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col md:flex-row justify-end gap-3 mt-4">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg text-sm transition">
                            Update User
                        </button>
                        <a href="{{ route('users.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-5 py-2 rounded-lg text-sm flex items-center justify-center transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Inter', sans-serif;
        }
    </style>
</x-layouts.app>
