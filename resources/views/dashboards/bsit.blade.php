<x-layouts.app :title="'BSIT Dashboard'">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-section {
            padding: 1rem;
        }

        /* Header */
        .header-flex {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
            gap: 1rem;
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }

        .header-right {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .header-right form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-img {
            height: 60px;
            width: 60px;
            border-radius: 8px;
            object-fit: contain;
        }

        /* Dashboard cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            border-radius: 14px;
            padding: 1rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
            text-align: center;
            transition: all 0.3s ease;
            color: #fff;
        }

        .dashboard-card:hover {
            transform: translateY(-4px) scale(1.02);
        }

        .card-blue { background: linear-gradient(135deg,rgb(0, 0, 0),rgb(250, 24, 24)); }
        .card-green { background: linear-gradient(135deg,rgb(0, 0, 0),rgb(250, 24, 24)); }
        .card-purple { background: linear-gradient(135deg,rgb(0, 0, 0),rgb(250, 24, 24)); }
        .card-yellow { background: linear-gradient(135deg,rgb(0, 0, 0),rgb(250, 24, 24)); color:#fff; }

        .dashboard-card h3 {
            margin-bottom: 0.3rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .dashboard-card p {
            font-size: 1.6rem;
            font-weight: bold;
        }

        /* Chart & Table containers */
        .chart-container, .table-container {
            background: #ffffff;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .chart-container h2, .table-container h2 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #1f2937;
        }

        /* Excel-style table */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            min-width: 800px; /* ensure horizontal scroll on small screens */
        }

        .excel-table th, .excel-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
        }

        .excel-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .excel-table tr:hover {
            background-color: #f1f5f9;
        }

        /* Form select */
        .form-select {
            padding: 0.4rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
            font-size: 0.85rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-left h1 { font-size: 1.3rem; }
            .logo-img { height: 50px; width: 50px; }
            .dashboard-card p { font-size: 1.3rem; }
            .dashboard-cards { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
        }

        @media (max-width: 480px) {
            .header-flex { flex-direction: column; align-items: flex-start; }
            .header-right { gap: 0.5rem; }
            .header-right form { width: 100%; }
            .dashboard-cards { grid-template-columns: 1fr; }
        }
    </style>

    <div class="dashboard-section">
        {{-- Header --}}
        <div class="header-flex">
            <div class="header-left">
                <h1>BSIT Dashboard</h1>
            </div>

            <div class="header-right">
                <form method="GET">
                    <label for="year" class="text-sm text-gray-700 mr-2">Year:</label>
                    <select name="year" id="year" onchange="this.form.submit()" class="form-select">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <img src="{{ asset('images/bsit.jpg') }}" alt="BSIT Logo" class="logo-img">
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="dashboard-cards">
            <div class="dashboard-card card-blue">
                <h3>Submitted</h3>
                <p>{{ $submittedCount }}</p>
            </div>
            <div class="dashboard-card card-green">
                <h3>Approved</h3>
                <p>{{ $approvedCount }}</p>
            </div>
            <div class="dashboard-card card-yellow">
                <h3>Total Items</h3>
                <p>{{ $itemCount }}</p>
            </div>
            @php
                $allocated = ($activeBudget && !$activeBudget->is_ended) ? ($departmentBudgets[$department] ?? 0) : 0;
            @endphp
            <div class="dashboard-card card-purple">
                <h3>Allocated Budget</h3>
                <p>₱{{ number_format($allocated, 2) }}</p>
                @if (!$activeBudget || $activeBudget->is_ended)
                    <small class="block mt-1 text-sm text-white/80">
                        Waiting for a new budget allocation...
                    </small>
                @endif
            </div>
        </div>

        {{-- Bar Chart --}}
        <div class="chart-container" style="max-height:400px;">
    <h2>Yearly Project Plan Cost</h2>
    <canvas id="budgetChart"></canvas>
</div>
        {{-- Excel Table --}}
        <div class="table-container">
            <h2>Recent Submissions</h2>
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Classification</th>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Estimated Budget</th>
                        <th>Status</th>
                        <th>Milestone</th>
                        <th>Updated at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPpmps as $ppmp)
                        <tr>
                            <td>{{ $ppmp->classification }}</td>
                            <td>{{ $ppmp->department }}</td>
                            <td>{{ $ppmp->description }}</td>
                            <td>{{ $ppmp->unit }}</td>
                            <td>{{ $ppmp->quantity }}</td>
                            <td>₱{{ number_format($ppmp->price, 2) }}</td>
                            <td>₱{{ number_format($ppmp->estimated_budget, 2) }}</td>
                            <td>{{ $ppmp->status }}</td>
                            <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('F d, Y') }}</td>
                            <td>{{ $ppmp->updated_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-gray-500 py-4">No recent submissions.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(190, 0, 0, 0.9)');
        gradient.addColorStop(1, 'rgba(126, 126, 126, 0.3)');

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Estimated Budget (₱)',
                    data: @json($chartData),
                    backgroundColor: gradient,
                    borderColor: 'rgb(0, 0, 0)',
                    borderWidth: 2,
                    borderRadius: { topLeft: 10, topRight: 10 },
                    barThickness: 'flex'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1200, easing: 'easeOutCubic' },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => '₱' + ctx.formattedValue } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: val => '₱' + val }, grid: { color: '#e5e7eb' } },
                    x: { ticks: { color: '#1f2937' }, grid: { display: false } }
                }
            }
        });
    </script>
</x-layouts.app>
