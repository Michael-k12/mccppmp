<x-layouts.app :title="'Edit User'">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: 'Success!',
                    text: @json(session('success')),
                    icon: 'success',
                    timer: 800,
                    showConfirmButton: false,
                    backdrop: true
                });
            });
        </script>
    @endif

    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md mt-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Edit User: {{ $user->name }}</h1>

        <form method="POST" action="{{ route('users.update', $user) }}" id="editUserForm">

            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block mb-1 font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-5">
                <label class="block mb-1 font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="mb-5">
                <label class="block mb-1 font-medium text-gray-700">Password 
                    <small class="text-sm text-gray-500">(Leave blank to keep current password)</small>
                </label>
                <input type="password" name="password"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block mb-1 font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

             {{-- Custom button style --}}
            <style>
                .update-button {
                    background-color: #001affff;
                    color: white;
                    padding: 10px 16px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                }

                .update-button:hover {
                    background-color: #010399ff;
                }
            </style>

            <div class="flex space-x-2">
                <button type="submit" class="update-button">Update</button>
                <a href="{{ route('users.index') }}" class="bg-gray-300 px-4 py-2 rounded">Cancel</a>
            </div>
        </form>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("editUserForm");

        form.addEventListener("submit", function (e) {
            const password = form.querySelector('input[name="password"]').value;
            const confirm = form.querySelector('input[name="password_confirmation"]').value;

            // Only validate if one or both fields are filled
            if (password !== '' || confirm !== '') {
                if (password !== confirm) {
                    e.preventDefault(); // Stop form submission
                    Swal.fire({
                        title: 'Error!',
                        text: 'Passwords do not match.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        backdrop: true
                    });
                }
            }
        });
    });
</script>

</x-layouts.app>
