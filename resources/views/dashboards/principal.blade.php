<x-layouts.app :title="__('Principal Dashboard')">

<div class="min-h-screen bg-gray-50 font-sans antialiased">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <header class="mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                📊 Financial Oversight Dashboard
            </h1>
            <p class="text-lg text-gray-500 mt-1">Review PPMP status and budget allocation for the current period.</p>
        </header>

        <div class="grid grid-cols-1 gap-7 sm:grid-cols-3 mb-12">

            <div class="bg-white rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                <div class="p-6 border-l-4 border-blue-500">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                        Submitted PPMPs
                    </p>
                    <p class="mt-2 text-4xl font-extrabold text-gray-900">
                        {{ $submittedCount ?? 0 }}
                    </p>
                    <p class="text-sm text-blue-500 mt-1">Total count awaiting action</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                <div class="p-6 border-l-4 border-green-500">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                        Approved PPMPs
                    </p>
                    <p class="mt-2 text-4xl font-extrabold text-gray-900">
                        {{ $approvedCount ?? 0 }}
                    </p>
                    <p class="text-sm text-green-500 mt-1">Ready for Procurement</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                <div class="p-6 border-l-4 border-indigo-500">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                        Allocated Budget
                    </p>
                    <p class="mt-2 text-4xl font-extrabold text-gray-900">
                        <span class="text-2xl font-bold align-top mr-1">₱</span>{{ number_format($latestBudget->amount ?? 0, 2) }}
                    </p>
                    <p class="text-sm text-indigo-500 mt-1">Current Fiscal Year Limit</p>
                </div>
            </div>
        </div>

        <form method="GET" class="flex justify-end mb-8">
            <label for="year-select" class="text-sm font-medium text-gray-700 mr-3 self-center hidden sm:block">Filter by Year:</label>
            <select name="year" id="year-select" onchange="this.form.submit()"
                class="block pl-4 pr-10 py-2 text-base font-medium border-gray-300 rounded-lg shadow-sm
                       focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out
                       hover:border-gray-400 cursor-pointer w-full sm:w-auto"
            >
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                        Fiscal Year {{ $year }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8 border border-gray-100">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-100">
                Total PPMP Cost by Department <span class="text-base font-normal text-gray-500">({{ $selectedYear }})</span>
            </h3>
            <div class="h-96">
                <canvas id="ppmpBarChart"></canvas>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Consistent color mapping for Chart.js
    const chartColor = '#3b82f6'; // blue-500
    const gridColor = '#f3f4f6';    // gray-100

    new Chart(document.getElementById('ppmpBarChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: '₱ Total Cost',
                data: {!! json_encode($chartData) !!},
                backgroundColor: chartColor,
                hoverBackgroundColor: '#2563eb', // Darker blue on hover
                borderRadius: 6,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 14 },
                    padding: 12,
                    displayColors: false, // Cleaner look without color square
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (context.parsed.y !== null) {
                                label = '₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#6b7280' } // gray-500
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => '₱' + val.toLocaleString('en-US', { notation: 'compact', compactDisplay: 'short' }), // Use compact notation (e.g., 1M) for professional charts
                        color: '#6b7280' // gray-500
                    },
                    grid: {
                        color: gridColor
                    }
                }
            }
        }
    });
</script>

</x-layouts.app>