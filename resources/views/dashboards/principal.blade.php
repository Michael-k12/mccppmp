<x-layouts.app :title="__('Principal Dashboard')">

<style>
    body {
        background: #ffffff;
        font-family: 'Poppins', sans-serif;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: auto;
        padding: 2.5rem;
    }

    /* KPI CARDS */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        background: linear-gradient(135deg, #545454, #ff7a00);
        text-align: center;
        padding: 2.2rem 1rem;
        border-radius: 16px;
        color: #fff;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }

    .kpi-card h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
        letter-spacing: .5px;
    }

    .kpi-card p {
        margin-top: .4rem;
        font-size: 2.4rem;
        margin-bottom: 0;
        font-weight: 700;
    }

    /* Year Filter */
    .filter-row {
        display: flex;
        justify-content: flex-end;
        margin-bottom: -0.5rem;
    }

    .filter-row select {
        padding: .7rem 1rem;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: #fff;
        font-size: .95rem;
        cursor: pointer;
    }

    /* Chart Section */
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
</style>

<div class="dashboard-container">

    <!-- KPI CARDS -->
    <div class="kpi-row">
        <div class="kpi-card">
            <h4>Submitted</h4>
            <p>{{ $submittedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <h4>Approved</h4>
            <p>{{ $approvedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <h4>Allocated Budget</h4>
            <p>₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- YEAR FILTER -->
    <form method="GET" class="filter-row">
        <select name="year" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </form>

    <!-- CHART -->
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
            backgroundColor: '#ff7a00',
            borderRadius: 10,
            barThickness: 35
        }]
    },
    options: {
        plugins: { legend: { display: false }},
        scales: {
            y: {
                ticks: { callback: val => '₱' + val.toLocaleString() }
            }
        }
    }
});
</script>

</x-layouts.app>
