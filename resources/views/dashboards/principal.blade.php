<x-layouts.app :title="__('Principal Dashboard')">

    <!-- Prevent zooming -->
    @push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endpush

    <style>
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 1.5rem;
        font-family: 'Inter', sans-serif;
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Professional Cards */
    .stat-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1.8rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    /* Accent line on top */
    .stat-card.border-yellow { border-top: 6px solid #f59e0b; }
    .stat-card.border-blue { border-top: 6px solid #3b82f6; }
    .stat-card.border-green { border-top: 6px solid #22c55e; }

    .stat-card h3 {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: .4rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .stat-card p {
        font-size: 2.1rem;
        font-weight: 700;
        margin: 0;
        color: #111827;
    }

    /* Chart Section */
    .chart-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.8rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
    }

    .chart-section h3 {
        text-align: center;
        font-weight: 600;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        color: #111827;
    }

    /* Year selector */
    .year-selector select {
        padding: .6rem 1rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background: #fff;
        font-size: 1rem;
        cursor: pointer;
    }

    canvas {
        width: 100% !important;
        max-height: 420px;
    }
</style>


    <div class="container">
        <!-- Top Stats -->
        <div class="dashboard-grid">
    <div class="stat-card border-yellow">
        <h3>Submitted</h3>
        <p>{{ $submittedCount ?? 0 }}</p>
    </div>

    <div class="stat-card border-blue">
        <h3>Budget</h3>
        <p>₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
    </div>

    <div class="stat-card border-green">
        <h3>Approved</h3>
        <p>{{ $approvedCount ?? 0 }}</p>
    </div>
</div>

        <!-- Year Filter -->
        <form method="GET" class="year-selector">
            <label for="year">Select Year:</label>
            <select name="year" id="year" onchange="this.form.submit()">
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </form>

        <!-- Chart Section -->
        <div class="chart-section">
            <h3>Total Cost by Department ({{ $selectedYear }})</h3>
            <canvas id="ppmpBarChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const barCtx = document.getElementById('ppmpBarChart').getContext('2d');

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Total Cost (₱)',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + parseFloat(context.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: { size: 12 },
                            color: '#333'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            },
                            font: { size: 12 },
                            color: '#333'
                        }
                    }
                }
            }
        });
    </script>

</x-layouts.app>
