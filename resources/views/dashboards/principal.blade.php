<x-layouts.app :title="__('Principal Dashboard')">

<style>
    /* VARIABLES: Define once, use everywhere */
    :root {
        --color-primary: #0056b3; /* Darker Royal Blue */
        --color-secondary: #00bcd4; /* Accent Teal/Cyan */
        --color-text-dark: #212529;
        --color-text-muted: #6c757d;
        --color-background: #f8f9fa; /* Lighter background */
        --color-card-bg: #ffffff;
        --shadow-elevation-1: 0 4px 16px rgba(0, 0, 0, 0.06);
        --shadow-elevation-2: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    body {
        background: var(--color-background);
        font-family: 'Inter', sans-serif; /* Using a more modern font for professionalism */
        color: var(--color-text-dark);
    }

    .dashboard-container {
        max-width: 1400px; /* Slightly wider */
        margin: 0 auto;
        padding: 3rem 2rem; /* More generous padding */
    }

    /* HEADER STYLING */
    .dashboard-header {
        margin-bottom: 2.5rem;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1.5rem;
    }

    .dashboard-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-primary);
        margin: 0;
    }

    /* KPI ROW */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem; /* Increased gap */
        margin-bottom: 3.5rem;
    }

    .kpi-card {
        background: var(--color-card-bg);
        border-radius: 12px;
        padding: 2rem;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border-left: 5px solid var(--color-primary); /* Primary Accent */
        box-shadow: var(--shadow-elevation-1);
        position: relative; /* For the subtle icon */
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: -30px;
        right: -30px;
        width: 100px;
        height: 100px;
        background: rgba(0, 86, 179, 0.05); /* Very faint primary background */
        border-radius: 50%;
        z-index: 0;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-elevation-2);
        border-left-color: var(--color-secondary); /* Accent color on hover */
    }

    .kpi-card h4 {
        color: var(--color-text-muted); /* Muted label */
        margin-bottom: .6rem;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        position: relative;
        z-index: 1;
    }

    .kpi-card p {
        margin: 0;
        font-size: 2.2rem; /* Larger value */
        font-weight: 800;
        color: var(--color-primary);
        position: relative;
        z-index: 1;
    }
    
    .kpi-card:nth-child(2) p { color: #28a745; } /* Green for 'Approved' */
    .kpi-card:nth-child(3) p { color: #ffc107; } /* Yellow/Orange for 'Budget' */

    /* FILTER & CHART CONTAINER */
    .content-area {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    /* YEAR FILTER */
    .filter-row {
        display: flex;
        justify-content: flex-end; /* Align to the right */
        margin-bottom: 2rem;
    }

    .filter-row select {
        padding: .75rem 1.25rem;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        background: var(--color-card-bg);
        font-size: 1rem;
        font-weight: 500;
        color: var(--color-text-dark);
        cursor: pointer;
        transition: .3s;
        min-width: 150px;
    }

    .filter-row select:focus,
    .filter-row select:hover {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        outline: none;
    }

    /* CHART CONTAINER */
    .chart-box {
        background: var(--color-card-bg);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: var(--shadow-elevation-1);
        border-top: 4px solid var(--color-secondary); /* Use the secondary color for a different accent */
    }

    .chart-box h3 {
        margin-bottom: 2rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary);
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }
</style>
<div class="dashboard-container">

    <div class="dashboard-header">
        <h1>📊 Principal Dashboard Overview</h1>
    </div>

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

    <div class="content-area">
        <form method="GET" class="filter-row">
            <select name="year" onchange="this.form.submit()">
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                        Fiscal Year {{ $year }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="chart-box">
            <h3>Total PPMP Cost by Department ({{ $selectedYear }})</h3>
            <canvas id="ppmpBarChart"></canvas>
        </div>
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
            /* Update to match the new secondary accent color for professionalism */
            backgroundColor: '#00bcd4', 
            borderRadius: 6,
            barPercentage: 0.7, /* Gives more space between bars */
            categoryPercentage: 0.8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { 
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += '₱' + context.parsed.y.toLocaleString();
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: { 
                    callback: val => '₱' + val.toLocaleString() 
                },
                grid: { color: '#e9ecef' } /* Lighter grid lines */
            }
        }
    }
});
</script>

</x-layouts.app>