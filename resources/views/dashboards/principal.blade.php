<x-layouts.app :title="__('Principal Dashboard')">

    <!-- Prevent zooming -->
    @push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @endpush

    <style>
        /* General container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        /* Dashboard Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0 0.5rem;
        }

        .dashboard-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #111827;
        }

        .dashboard-logo {
            height: 80px;
            width: auto;
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

        /* Gradient card colors */
        .card-yellow {
            background: linear-gradient(135deg, #000000, #FFD700); /* Black to gold */
            color: #ffffff;
        }

        .card-blue {
            background: linear-gradient(135deg, #4B4B4B, #FFD700); /* Dark gray to gold */
            color: #ffffff;
        }

        .card-green {
            background: linear-gradient(135deg, #333333, #FFD700); /* Slightly darker gray to gold */
            color: #ffffff;
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
            background-color: #8fb1a865;
            border: 1px solid #000000ff;
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

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0.5rem;
            }

            .card-custom p {
                font-size: 1.3rem;
            }

            .dashboard-header h2 {
                font-size: 1.4rem;
            }

            .dashboard-logo {
                height: 40px;
            }
        }
    </style>

    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h2>Principal Dashboard</h2>
            <img src="{{ asset('logo/logo-mcc.png') }}" alt="Logo" class="dashboard-logo">

        </div>

        <!-- Top Stats -->
        <div class="dashboard-grid">
            <div class="card-custom card-yellow">
                <h3>Submitted</h3>
                <p>{{ $submittedCount ?? 0 }}</p>
            </div>

            <div class="card-custom card-blue">
                <h3>Budget</h3>
                <p>₱{{ number_format($latestBudget->amount ?? 0, 2) }}</p>
            </div>

            <div class="card-custom card-green">
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
