<x-layouts.app :title="__('Principal Dashboard')">



<style>
    body {
        background: #eef2f7;
        font-family: 'Poppins', sans-serif;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: auto;
        padding: 2.5rem;
    }

    /* KPI Cards Row */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: white;
        padding: 1.7rem;
        border-radius: 18px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.05);
        transition: .25s ease;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }

    .kpi-icon {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        font-weight: bold;
    }

    .yellow { background: #f59e0b; }
    .blue { background: #3b82f6; }
    .green { background: #22c55e; }

    .kpi-details h4 {
        font-size: .9rem;
        color: #6b7280;
        margin: 0;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .kpi-details p {
        font-size: 2rem;
        margin: 0;
        font-weight: 700;
        color: #111827;
    }

    /* Chart Box */
    .chart-box {
        background: white;
        padding: 2rem;
        border-radius: 18px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
    }

    .chart-box h3 {
        text-align: center;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: #1f2937;
    }

    /* Year Filter */
    .filter-row {
        display: flex;
        justify-content: flex-end;
        margin-bottom: .5rem;
    }

    .filter-row select {
        padding: .7rem 1rem;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: #fff;
        font-size: .95rem;
        cursor: pointer;
    }
</style>

<div class="dashboard-container">

    <!-- KPI Cards -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon yellow">📄</div>
            <div class="kpi-details">
                <h4>Submitted</h4>
                <p>{{ $submittedCount ?? 0 }}</p>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon blue">💰</div>
            <div class="kpi-details">
                <h4>Budget</h4>
                <p>₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon green">✅</div>
            <div class="kpi-details">
                <h4>Approved</h4>
                <p>{{ $approvedCount ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Year Filter -->
    <form method="GET" class="filter-row">
        <select name="year" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
    </form>

    <!-- Chart -->
    <div class="chart-box">
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
            label: '₱ Total Cost',
            data: {!! json_encode($chartData) !!},
            backgroundColor: '#6366f1',
            borderRadius: 10,
            barThickness: 35
        }]
    },
    options: {
        plugins: { legend: { display: false }},
        scales: { y: { ticks: { callback: val => '₱' + val.toLocaleString() }}}
    }
});
</script>

</x-layouts.app>