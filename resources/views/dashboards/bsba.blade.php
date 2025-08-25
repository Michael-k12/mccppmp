<x-layouts.app :title="'BSBA Dashboard'">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-section {
            padding: 2rem;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
        }

        .header-left h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            height: 80px;
            width: 80px;
            border-radius: 8px;
            object-fit: contain;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            color: #ffffff;
        }

        .dashboard-card:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .card-blue {
            background: linear-gradient(135deg,rgb(0, 0, 0),rgb(30, 255, 0));
        }

        .card-green {
            background: linear-gradient(135deg,rgb(0, 0, 0),rgb(30, 255, 0));
        }

        .card-purple {
            background: linear-gradient(135deg,rgb(0, 0, 0),rgb(30, 255, 0));
        }

        .card-yellow {
            background: linear-gradient(135deg,rgb(0, 0, 0),rgb(30, 255, 0));
            color:rgb(255, 255, 255);
        }

        .dashboard-card h3 {
            margin-bottom: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .dashboard-card p {
            font-size: 2rem;
            font-weight: bold;
        }

        .chart-container,
        .table-container {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
        }

        .chart-container h2,
        .table-container h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .excel-table th,
        .excel-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .excel-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .excel-table tr:hover {
            background-color: #f1f5f9;
        }

        .form-select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
            font-size: 0.875rem;
        }
    </style>

    <div class="dashboard-section">

        {{-- Header --}}
        <div class="header-flex">
            <div class="header-left">
                <h1>BSBA Dashboard</h1>
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
                <img src="{{ asset('images/bsba.jpg') }}" alt="BSIT Logo" class="logo-img">
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
             {{-- Allocated Budget --}}
   {{-- Allocated Budget --}}
<div class="dashboard-card card-purple">
    <h3>Allocated Budget</h3>
    <p>₱{{ number_format($departmentBudgets[$department] ?? 0) }}
</p>
</div>
        </div>

        {{-- Bar Chart --}}
        <div class="chart-container">
            <h2>Yearly Project Plan Cost</h2>
            <canvas id="budgetChart" height="100"></canvas>
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
                            <td colspan="9" class="text-center text-gray-500 py-4">No recent submissions.</td>
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
        gradient.addColorStop(0, 'rgb(43, 255, 0)');
        gradient.addColorStop(1, 'rgb(255, 255, 255)');

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
                    borderRadius: {
                        topLeft: 10,
                        topRight: 10
                    },
                    barThickness: 100// fixed bar width
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 1500,
                    easing: 'easeOutElastic'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => '₱' + ctx.formattedValue
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => '₱' + val
                        },
                        grid: {
                            color: '#e5e7eb'
                        }
                    },
                    x: {
                        ticks: { color: '#1f2937' },
                        grid: { display: false }
                    }
                }
            }
        });

        // Auto-update is disabled
    </script>
</x-layouts.app>
