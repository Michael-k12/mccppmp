<x-layouts.app :title="'Submit'">
    @if(session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted!',
                    text: "{{ session('success') }}",
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    <style>
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .excel-table th,
        .excel-table td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
            background-color: white;
        }
        .excel-table th {
            background-color:rgb(154, 169, 255);
            text-transform: uppercase;
            font-size: 11px;
        }
        .excel-table tr:hover td {
            background-color: #f9fafb;
        }
        .submit-btn {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #1d4ed8;
        }
    </style>

    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Project Procurement Plan</h1>

        <form id="submitToPrincipalForm" action="{{ route('ppmp.submitToPrincipal') }}" method="POST">
            @csrf

            <div class="overflow-x-auto bg-white shadow border border-gray-200 rounded-lg">
                <table class="excel-table">
                    <thead>
                        <tr>
                            <th>Classification</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Estimated Budget</th>
                            <th>Mode of Procurement</th>
                            <th>Schedule/Milestone of Activities</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ppmps as $ppmp)
                            <tr>
                                <td>{{ $ppmp->classification }}</td>
                                <td>{{ $ppmp->description }}</td>
                                <td>{{ $ppmp->unit }}</td>
                                <td>{{ $ppmp->quantity }}</td>
                                <td>{{ number_format($ppmp->price, 2) }}</td>
                                <td>{{ number_format($ppmp->estimated_budget, 2) }}</td>
                                <td>{{$ppmp->mode_of_procurement}}</td>
                                <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('Y F') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-gray-500 py-4">No Project Plan to Submit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" id="submitPrincipalBtn" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('submitToPrincipalForm');
            const submitBtn = document.getElementById('submitPrincipalBtn');

            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            });
        });
    </script>
</x-layouts.app>
