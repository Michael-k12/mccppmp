<x-layouts.app :title="__('Dashboard')">
    <style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .card-custom {
        border-radius: 12px;
        padding: 16px;
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .border-yellow {
        background-color: #f59e0b; /* amber-500 */
    }

    .border-blue {
        background-color: #3b82f6; /* blue-500 */
    }

    .border-green {
        background-color: #22c55e; /* green-500 */
    }

    .card-custom h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .card-custom p {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
    }

    .chart-section {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-top: 1.5rem;
    }

    .chart-section h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    canvas {
        width: 100% !important;
        height: 350px !important;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 1rem;
    }

    .year-selector {
        margin-bottom: 1rem;
    }

    .year-selector select {
        padding: 0.5rem;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
</style>


    <div class="container">
        <div class="dashboard-grid">
            <div class="card-custom border-yellow">
                <h3>Submitted</h3>
                <p>{{ $submittedCount ?? 0 }}</p>
            </div>

            <div class="card-custom border-blue">
                <h3>Budget</h3>
                <p>₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
            </div>

            <div class="card-custom border-green">
                <h3>Approved</h3>
                <p>{{ $approvedCount ?? 0 }}</p>
            </div>
        </div>

        <!-- Year Filter -->
        <form method="GET" class="year-selector">
            <label for="year">Select Year:</label>
            <select name="year" id="year" onchange="this.form.submit()">
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </form>

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
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>

</x-layouts.app>
