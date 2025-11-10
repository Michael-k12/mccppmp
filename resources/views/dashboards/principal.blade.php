<x-layouts.app :title="__('Principal Dashboard')">

<style>
    body {
        background: #eef2f7;
        font-family: 'Poppins', sans-serif;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2.5rem 1rem;
    }

    /* KPI CARDS ROW */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .kpi-card {
        position: relative;
        background: linear-gradient(135deg, #0057ff, #00c6ff);
        color: #fff;
        border-radius: 16px;
        padding: 2rem 2rem 2rem 3rem;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: default;
    }

    .kpi-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0, 87, 255, 0.3);
    }

    .kpi-card .icon {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 3rem;
        opacity: 0.2;
    }

    .kpi-card h4 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-card p {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    /* YEAR FILTER */
    .filter-row {
        text-align: right;
        margin-bottom: 2.5rem;
    }

    .filter-row select {
        padding: 0.6rem 1.2rem;
        border-radius: 12px;
        border: 1px solid #ced4da;
        background: #ffffff;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.25s;
    }

    .filter-row select:hover {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.15);
    }

    /* CHART CONTAINER */
    .chart-box {
        background: #fff;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        border-top: 6px solid #007bff;
    }

    .chart-box h3 {
        margin-bottom: 1.8rem;
        font-size: 1.35rem;
        font-weight: 600;
        color: #00244a;
    }

    /* RESPONSIVE */
    @media (max-width: 992px) {
        .kpi-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-container">

    <!-- KPI CARDS -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="icon">📄</div>
            <h4>Submitted PPMPs</h4>
            <p>{{ $submittedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <div class="icon">✅</div>
            <h4>Approved PPMPs</h4>
            <p>{{ $approvedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <div class="icon">💰</div>
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
const ctx = document.getElementById('ppmpBarChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: '₱ Total Cost',
            data: {!! json_encode($chartData) !!},
            backgroundColor: '#007bff',
            borderRadius: 10,
            barThickness: 40
        }]
    },
    options: {
        plugins: { 
            legend: { display: false }
        },
        scales: {
            y: {
                ticks: { callback: val => '₱' + val.toLocaleString() },
                grid: { color: '#e0e0e0' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>

</x-layouts.app>
