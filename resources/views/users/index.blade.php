<x-layouts.app :title="'Manage Users'">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- ✅ SweetAlert Success --}}
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

    {{-- ❌ SweetAlert Error --}}
    @if($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
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

        <div class="table-container">
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
                        <td data-label="Name">{{ $user->name }}</td>
                        <td data-label="Email">{{ $user->email }}</td>
                        <td data-label="Department">{{ $user->role }}</td>
                        <td data-label="Actions" class="action-buttons">
                            <a href="{{ route('users.edit', $user) }}" class="edit-btn">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-form inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="delete-btn" onclick="confirmDelete(this)">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-custom">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3">
        <form action="{{ route('users.store') }}" method="POST">
          @csrf
          <div class="mb-2">
            <label for="name" class="form-label mb-1">Full Name</label>
            <input type="text" name="name" id="name" class="form-control form-control-sm" required>
          </div>

          <div class="mb-2">
            <label for="email" class="form-label mb-1">Email</label>
            <input type="email" name="email" id="email" class="form-control form-control-sm" required>
          </div>

          <div class="mb-2">
            <label for="role" class="form-label mb-1">Role</label>
            <select name="role" id="role" class="form-select form-select-sm" required>
              <option value="">Select Role</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
          </div>

          <div class="mb-2">
            <label for="password" class="form-label mb-1">Password</label>
            <input type="password" name="password" id="password" class="form-control form-control-sm" required>
          </div>

          <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

    <style>
        /* ✅ Base */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header-row h1 {
            font-size: 1.8rem;
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

        /* ✅ Responsive Table */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 8px;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .user-table thead {
            background-color: #3b82f6;
            color: #fff;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .edit-btn {
            background-color: #3b82f6;
            color: #fff;
        }

        .edit-btn:hover {
            background-color: #1e40af;
        }

        .delete-btn {
            background-color: #ef4444;
            color: #fff;
        }

        .delete-btn:hover {
            background-color: #b91c1c;
        }

        /* ✅ Responsive Table (Stacked for small screens) */
        @media (max-width: 768px) {
            .user-table thead {
                display: none;
            }

            .user-table, .user-table tbody, .user-table tr, .user-table td {
                display: block;
                width: 100%;
            }

            .user-table tr {
                background: #fff;
                margin-bottom: 12px;
                border-radius: 8px;
                box-shadow: 0 1px 5px rgba(0,0,0,0.1);
                padding: 10px;
            }

            .user-table td {
                border: none;
                padding: 10px 10px 8px;
                position: relative;
                text-align: left;
            }

            .user-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #374151;
                display: block;
                margin-bottom: 4px;
                text-transform: capitalize;
            }

            .action-buttons {
                justify-content: flex-start;
            }
        }

        /* ✅ Modal Overlay */
.modal {
    position: fixed;
    inset: 0;
    z-index: 50;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
    transition: opacity 0.3s ease;
}

/* Hide modal */
.modal.hidden {
    display: none;
}

/* ✅ Modal Card */
.modal-content {
    background: #ffffff;
    padding: 2rem;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    position: relative;
    animation: slideDown 0.3s ease-out;
}

/* Slide-in animation */
@keyframes slideDown {
    from { transform: translateY(-40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ✅ Close Button */
.close-btn {
    position: absolute;
    top: 14px;
    right: 18px;
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

/* ✅ Header */
.modal-header {
    text-align: left;
    margin-bottom: 1rem;
}
.modal-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}
.modal-header .modal-subtitle {
    font-size: 0.9rem;
    color: #6b7280;
}

/* ✅ Form Layout */
.modal-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.form-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.modal-form label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}
.modal-form input,
.modal-form select {
    padding: 12px 14px;
    border-radius: 10px;
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

/* ✅ Buttons */
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
.btn-submit {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
}
.btn-submit:hover {
    background: linear-gradient(90deg, #2563eb, #1e40af);
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

/* ✅ Mobile Adjustments */
.modal-sm-custom {
    max-width: 420px; /* default desktop size */
  }

  @media (max-width: 576px) {
    .modal-sm-custom {
      max-width: 90% !important; /* smaller modal on mobile */
      margin: 0 auto;
    }
    .modal-content {
      border-radius: 10px;
      padding: 5px;
    }
  }
asd
    </style>

    <script>
        function openModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }
        function confirmDelete(button) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This user will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
</x-layouts.app>
