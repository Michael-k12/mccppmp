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
            <div class="container" style="max-width:500px; margin:50px auto; font-family:Arial,sans-serif;">
    <h2 style="text-align:center; margin-bottom:20px;">Edit User</h2>

    <form method="POST" action="/update-user" style="background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <!-- CSRF -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="PUT">

        <!-- Name -->
        <div style="margin-bottom:15px;">
            <label for="name" style="display:block; margin-bottom:5px; font-weight:bold;">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
        </div>

        <!-- Email (readonly) -->
        <div style="margin-bottom:15px;">
            <label for="email_display" style="display:block; margin-bottom:5px; font-weight:bold;">Email</label>
            <input type="text" id="email_display" value="{{ $user->email }}" readonly
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; background:#f0f0f0;">
            <!-- Hidden input to submit email -->
            <input type="hidden" name="email" value="{{ $user->email }}">
        </div>

        <!-- Role (readonly) -->
        <div style="margin-bottom:15px;">
            <label for="role_display" style="display:block; margin-bottom:5px; font-weight:bold;">Department / Role</label>
            <input type="text" id="role_display" value="{{ $user->role }}" readonly
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; background:#f0f0f0;">
            <input type="hidden" name="role" value="{{ $user->role }}">
        </div>

        <!-- Password -->
        <div style="margin-bottom:15px;">
            <label for="password" style="display:block; margin-bottom:5px; font-weight:bold;">New Password</label>
            <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
            <small style="color:#666; font-size:12px;">Minimum 12 characters, including uppercase, lowercase, number & symbol</small>
        </div>

        <!-- Confirm Password -->
        <div style="margin-bottom:20px;">
            <label for="password_confirmation" style="display:block; margin-bottom:5px; font-weight:bold;">Confirm New Password</label>
            <input type="password" name="password_confirmation" placeholder="Confirm New Password"
                style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
        </div>

        <!-- Buttons -->
        <div style="text-align:right;">
            <button type="submit" style="background:#1D4ED8; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
                Update User
            </button>
            <a href="{{ route('users.index') }}" style="background:#e5e7eb; color:#111; padding:10px 20px; border-radius:5px; text-decoration:none; margin-left:10px;">
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
