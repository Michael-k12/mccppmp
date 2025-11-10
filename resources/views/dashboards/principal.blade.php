<x-layouts.app :title="__('Principal Dashboard')">

<div class="min-h-screen bg-gray-50 font-sans antialiased">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <header class="mb-8 border-b pb-4 border-gray-200">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">
                📊 Principal Dashboard Overview
            </h1>
        </header>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-10">

            <div class="bg-white overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition duration-300 ease-in-out border-l-4 border-blue-600">
                <div class="p-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                        Submitted PPMPs
                    </p>
                    <p class="mt-1 text-4xl font-bold text-blue-600">
                        {{ $submittedCount ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="bg-white overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition duration-300 ease-in-out border-l-4 border-green-500">
                <div class="p-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                        Approved PPMPs
                    </p>
                    <p class="mt-1 text-4xl font-bold text-green-500">
                        {{ $approvedCount ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="bg-white overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition duration-300 ease-in-out border-l-4 border-yellow-500">
                <div class="p-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                        Allocated Budget
                    </p>
                    <p class="mt-1 text-4xl font-bold text-yellow-600">
                        ₱{{ number_format($latestBudget->amount ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <form method="GET" class="flex justify-end mb-8">
            <label for="year-select" class="sr-only">Select Fiscal Year</label>
            <select name="year" id="year-select" onchange="this.form.submit()"
                class="block w-full sm:w-auto pl-4 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm transition duration-150 ease-in-out"
            >
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                        Fiscal Year {{ $year }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8 border-t-4 border-blue-600">
            <h3 class="text-xl font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">
                Total PPMP Cost by Department ({{ $selectedYear }})
            </h3>
            <div class="h-96"> <canvas id="ppmpBarChart"></canvas>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Define Tailwind colors for Chart.js
    const primaryColor = '#2563eb'; // blue-600
    const gridColor = '#e5e7eb';    // gray-200

    new Chart(document.getElementById('ppmpBarChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: '₱ Total Cost',
                data: {!! json_encode($chartData) !!},
                backgroundColor: primaryColor,
                borderColor: primaryColor,
                borderWidth: 1,
                borderRadius: 4, // Slightly rounded bars
                barPercentage: 0.8,
                categoryPercentage: 0.7
            }]
        },
        options: {
            maintainAspectRatio: false, // Allows the chart to fill the container
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)', // gray-900 with transparency
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 14 },
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
                    grid: { display: false },
                    ticks: { color: '#4b5563' } // gray-600
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => '₱' + val.toLocaleString(),
                        color: '#4b5563' // gray-600
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