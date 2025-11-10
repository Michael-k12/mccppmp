<x-layouts.app :title="__('Principal Dashboard')">

<style>
/* ===== DASHBOARD GLOBAL STYLE ===== */
.dashboard-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background: #f9fafc;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    font-family: 'Inter', sans-serif;
}

/* ===== KPI ROW ===== */
.kpi-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
    justify-content: space-between;
}

.kpi-card {
    flex: 1 1 calc(33% - 1rem);
    background: linear-gradient(145deg, #ffffff, #f0f2f5);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}

.kpi-card h4 {
    color: #374151;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

.kpi-card p {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2563eb;
    margin: 0;
}

/* ===== FILTER ROW ===== */
.filter-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 2rem;
}

.filter-row select {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-row select:hover {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* ===== CHART BOX ===== */
.chart-box {
    background: #ffffff;
    padding: 1.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.chart-box h3 {
    color: #1f2937;
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border-left: 4px solid #2563eb;
    padding-left: 0.75rem;
}

@media (max-width: 768px) {
    .kpi-card {
        flex: 1 1 100%;
    }
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
            <p style="color:#16a34a;">{{ $approvedCount ?? 0 }}</p>
        </div>

        <div class="kpi-card">
            <h4>Allocated Budget</h4>
            <p style="color:#dc2626;">₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
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
const ctx = document.getElementById('ppmpBarChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: '₱ Total Cost',
            data: {!! json_encode($chartData) !!},
            backgroundColor: [
                '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'
            ],
            borderRadius: 8,
            barThickness: 40
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
                        return '₱' + context.parsed.y.toLocaleString();
                    }
                },
                backgroundColor: 'rgba(31, 41, 55, 0.9)',
                titleFont: { weight: 'bold' },
                cornerRadius: 6
            }
        },
        scales: {
            y: {
                ticks: { callback: val => '₱' + val.toLocaleString() },
                grid: { color: '#e5e7eb' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#374151' }
            }
        }
    }
});
</script>

</x-layouts.app>
