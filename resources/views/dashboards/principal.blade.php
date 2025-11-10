<x-layouts.app :title="__('Principal Dashboard')">

<style>
    body {
        background: #f4f7fb;
        font-family: 'Poppins', sans-serif;
    }

    .dashboard-container {
        max-width: 1350px;
        margin: 0 auto;
        padding: 2.5rem 1rem;
    }

    /* KPI ROW */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.8rem;
        margin-bottom: 2.8rem;
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1.8rem;
        transition: 0.25s;
        border-left: 6px solid #0057ff; /* Royal Blue Accent */
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    .kpi-card h4 {
        color: #00306f;
        margin-bottom: .4rem;
        font-size: 1.05rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .kpi-card p {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 700;
        color: #0057ff;
    }

    /* YEAR FILTER */
    .filter-row {
        text-align: right;
        margin-bottom: 2rem;
    }

    .filter-row select {
        padding: .6rem 1rem;
        border-radius: 8px;
        border: 1px solid #ced4da;
        background: #ffffff;
        font-size: .95rem;
        cursor: pointer;
        transition: .25s;
    }

    .filter-row select:hover {
        border-color: #0057ff;
        box-shadow: 0 0 0 2px rgba(0,87,255,0.15);
    }

    /* CHART CONTAINER */
    .chart-box {
        background: #ffffff;
        border-radius: 14px;
        padding: 2.2rem;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        border-top: 4px solid #0057ff;
    }

    .chart-box h3 {
        margin-bottom: 1.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #00244a;
    }
</style>


<div class="dashboard-container">

    <div class="kpi-row">
        <div class="kpi-card">
            <h4>Submitted PPMPs</h4>
            <p>{{ $submittedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <h4>Approved PPMPs</h4>
            <p>{{ $approvedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <h4>Allocated Budget</h4>
            <p>₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
        </div>
    </div>

    <form method="GET" class="filter-row">
        <select name="year" onchange="this.form.submit()">
            @foreach ($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </form>

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
            backgroundColor: '#007bff',
            borderRadius: 8,
            barThickness: 38
        }]
    },
    options: {
        plugins: { 
            legend: { display: false }
        },
        scales: {
            y: {
                ticks: { callback: val => '₱' + val.toLocaleString() }
            }
        }
    }
});
</script>

</x-layouts.app>
