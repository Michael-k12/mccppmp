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

    <div class="container mx-auto px-4 py-10 flex justify-center">
        <div class="card w-full max-w-lg bg-white shadow-md rounded-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Edit User</h1>
                <a href="{{ route('users.index') }}" class="back-btn text-blue-500 hover:text-blue-700 font-medium">&larr; Back</a>
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-gray-700 font-medium mb-1" for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" placeholder="Full Name" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1" for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" placeholder="Email Address" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1" for="password">New Password</label>
                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                    <small class="text-gray-500 text-sm">Minimum 12 characters, including uppercase, lowercase, number & symbol</small>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1" for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm New Password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1" for="role">Department / Role</label>
                    <select name="role" id="role" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                        <option value="">-- Select Role --</option>
                        <option value="BSED" {{ old('role', $user->role) == 'BSED' ? 'selected' : '' }}>BSED</option>
                        <option value="BSBA" {{ old('role', $user->role) == 'BSBA' ? 'selected' : '' }}>BSBA</option>
                        <option value="BSIT" {{ old('role', $user->role) == 'BSIT' ? 'selected' : '' }}>BSIT</option>
                        <option value="BSHM" {{ old('role', $user->role) == 'BSHM' ? 'selected' : '' }}>BSHM</option>
                        <option value="LIBRARY" {{ old('role', $user->role) == 'LIBRARY' ? 'selected' : '' }}>Library</option>
                        <option value="NURSE" {{ old('role', $user->role) == 'NURSE' ? 'selected' : '' }}>Nurse</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <button type="submit"
                        class="bg-blue-500 text-white font-medium px-6 py-3 rounded-lg hover:bg-blue-600 transition">Update User</button>
                    <a href="{{ route('users.index') }}"
                        class="bg-gray-200 text-gray-700 font-medium px-6 py-3 rounded-lg hover:bg-gray-300 transition flex items-center justify-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Minimal custom styles -->
    <style>
        body {
            background-color: #f9fafb;
        }
    </style>
</x-layouts.app>
