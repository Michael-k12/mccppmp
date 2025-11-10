<x-layouts.app :title="__('Principal Dashboard')">

@push('head')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<!-- Modern Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@endpush

<style>
    body {
        background: #f4f7fb;
        font-family: 'Inter', sans-serif;
    }

    .container {
        max-width: 1350px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Modern Tech Stat Cards */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(14px);
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(220, 220, 220, 0.4);
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }

    .stat-title {
        font-size: .9rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: .4rem;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    .stat-value {
        font-size: 2.6rem;
        font-weight: 700;
        color: #111827;
    }

    /* Accent Gradients */
    .yellow { border-top: 6px solid #f59e0b; }
    .blue { border-top: 6px solid #3b82f6; }
    .green { border-top: 6px solid #22c55e; }

    /* Year Selector */
    .year-selector {
        margin-bottom: 1.4rem;
        font-size: .95rem;
        display: flex;
        align-items: center;
        gap: .7rem;
    }

    .year-selector select {
        padding: .7rem 1rem;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: white;
        font-size: .95rem;
    }

    /* Modern Chart Panel */
    .chart-section {
        background: white;
        border-radius: 18px;
        padding: 2rem;
        border: 1px solid rgba(220, 220, 220, 0.4);
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        margin-top: 1rem;
    }

    .chart-section h3 {
        text-align: center;
        font-weight: 600;
        margin-bottom: 1.2rem;
        font-size: 1.25rem;
        color: #111827;
    }
</style>

<div class="container">

    <!-- Modern Statistic Cards -->
    <div class="dashboard-grid">
        <div class="stat-card yellow">
            <div class="stat-title">Submitted</div>
            <div class="stat-value">{{ $submittedCount ?? 0 }}</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-title">Budget</div>
            <div class="stat-value">₱{{ number_format($latestBudget->amount ?? 0, 2) }}</div>
        </div>

        <div class="stat-card green">
            <div class="stat-title">Approved</div>
            <div class="stat-value">{{ $approvedCount ?? 0 }}</div>
        </div>
    </div>

    <!-- Year Dropdown -->
    <form method="GET" class="year-selector">
        <label for="year">Select Year:</label>
        <select name="year" id="year" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
    </form>

    <!-- Chart Panel -->
    <div class="chart-section">
        <h3>Total Cost by Department ({{ $selectedYear }})</h3>
        <canvas id="ppmpBarChart"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('ppmpBarChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Total Cost (₱)',
                data: {!! json_encode($chartData) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    ticks: {
                        callback: value => '₱' + value.toLocaleString()
                    }
                }
            }
        }
    });
</script>

</x-layouts.app>
