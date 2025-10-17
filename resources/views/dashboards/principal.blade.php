<x-layouts.app :title="__('Principal Dashboard')">

<style>
    /* General container */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* Dashboard grid for cards */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Card style */
    .card-custom {
        border-radius: 12px;
        padding: 20px;
        color: white;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-custom:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
        word-break: break-word;
    }

    /* Chart container */
    .chart-section {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        margin-top: 1.5rem;
    }

    .chart-section h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        text-align: center;
    }

    /* Year selector styling */
    .year-selector {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .year-selector label {
        font-weight: 500;
    }

    .year-selector select {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1rem;
        cursor: pointer;
    }

    /* Chart responsiveness */
    canvas {
        width: 100% !important;
        height: auto !important;
        max-height: 400px;
    }

    /* Responsive text adjustments */
    @media (max-width: 768px) {
        .card-custom h3 {
            font-size: 0.9rem;
        }

        .card-custom p {
            font-size: 1.5rem;
        }

        .chart-section {
            padding: 15px;
        }

        .year-selector {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0.5rem;
        }

        .card-custom p {
            font-size: 1.3rem;
        }
    }
</style>


<div class="container">
    <!-- Top Stats -->
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
