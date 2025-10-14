<x-layouts.app :title="'Principal View'">
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center sm:text-left">
            {{ session('success') }}
        </div>
    @endif

    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="ppmp-header">
            <h2 class="title">Project Plan Approval</h2>

            @if ($latestBudget)
                <div class="budget-info">
                    <span id="allocatedLabel"
                        class="budget-status {{ session('approved') ? 'allocated-red' : ($ppmpTotal > $latestBudget->amount ? 'over-budget' : ($ppmpTotal == $latestBudget->amount ? 'exact-budget' : 'within-budget')) }}">
                        Allocated: ₱{{ number_format($latestBudget->amount, 2) }}
                    </span>

                    <span class="ppmp-subtotal highlight-budget">
                        Purpose: ₱{{ number_format($ppmpTotal, 2) }}
                    </span>

                    <span id="remainingLabel" class="ppmp-subtotal remaining-budget">
                        Remaining: ₱{{ session('approved') ? '0.00' : number_format($latestBudget->amount - $ppmpTotal, 2) }}
                    </span>
                </div>
            @else
                <span class="no-budget">No budget set</span>
            @endif
        </div>

        <!-- Buttons -->
        <div class="action-bar">
            @if ($latestBudget)
                <!-- Approve All -->
                <form method="POST" action="{{ route('ppmp.batchApprove') }}"
                      onsubmit="return confirm('Approve all submitted Project Plan?');">
                    @csrf
                    @foreach ($ppmps as $ppmp)
                        <input type="hidden" name="ppmp_ids[]" value="{{ $ppmp->id }}">
                    @endforeach
                    <button type="submit"
                        class="approve-btn {{ $ppmpTotal != $latestBudget->amount ? 'disabled-btn' : '' }}"
                        {{ $ppmpTotal != $latestBudget->amount ? 'disabled' : '' }}>
                        Approve All
                    </button>
                </form>

                <!-- Delete All -->
                @if (count($ppmps) > 0)
                    <form method="POST" action="{{ route('ppmp.deleteAll') }}"
                          onsubmit="return confirm('Are you sure you want to delete all Project Plans?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn">Delete All</button>
                    </form>
                @else
                    <button type="button" class="delete-btn disabled-btn" disabled>Delete All</button>
                @endif
            @else
                <button type="button" class="approve-btn disabled-btn" disabled>Approve All</button>
                <button type="button" class="delete-btn disabled-btn" disabled>Delete All</button>
            @endif

            <!-- Realignment (renamed from Edit Quantities) -->
            <a href="{{ route('ppmp.editDepartmentQuantities', 'all') }}" class="action-button">
                Realignment
            </a>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>Classification</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Budget</th>
                        <th>Mode of Procurement</th>
                        <th>Schedule/Milestone</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ppmps as $ppmp)
                        <tr>
                            <td>{{ $ppmp->classification }}</td>
                            <td>{{ $ppmp->description }}</td>
                            <td>{{ $ppmp->unit }}</td>
                            <td>{{ $ppmp->quantity }}</td>
                            <td>₱{{ number_format($ppmp->price, 2) }}</td>
                            <td>₱{{ number_format($ppmp->estimated_budget, 2) }}</td>
                            <td>{{ $ppmp->mode_of_procurement }}</td>
                            <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('F d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500">No Project Plans Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Styles -->
    <style>
        /* Header */
        .ppmp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        .budget-info {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .budget-status, .ppmp-subtotal {
            font-size: 0.9rem;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 600;
        }
        .within-budget { background-color: #d1fae5; color: #065f46; }
        .over-budget { background-color: #fee2e2; color: #b91c1c; }
        .exact-budget { background-color: #bfdbfe; color: #1d4ed8; }
        .highlight-budget { background-color: #e0f2fe; color: #0369a1; }
        .remaining-budget { background-color: #dcfce7; color: #166534; }
        .no-budget { color: #dc2626; font-weight: 600; }

        /* Buttons Row */
        .action-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .action-button, .approve-btn, .delete-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: 0.3s;
            text-align: center;
        }
        .action-button { background-color: #f59e0b; color: #fff; } /* Realignment */
        .action-button:hover { background-color: #d97706; }
        .approve-btn { background-color: #16a34a; color: white; border: none; }
        .approve-btn:hover { background-color: #15803d; }
        .delete-btn { background-color: #dc2626; color: white; border: none; }
        .delete-btn:hover { background-color: #b91c1c; }
        .disabled-btn { background-color: #9ca3af !important; cursor: not-allowed !important; opacity: 0.7; }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Table */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 800px; /* Ensures horizontal scrolling */
        }
        .excel-table th, .excel-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
            white-space: nowrap;
        }
        .excel-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .excel-table tr:hover td { background-color: #f9fafb; }

        /* Allocated Red Label */
        .allocated-red {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            font-weight: bold;
            border: 1px solid #f87171;
        }

        /* 📱 Responsive Adjustments */
        @media (max-width: 1024px) {
            .budget-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .title {
                font-size: 1.25rem;
                text-align: center;
                width: 100%;
            }
            .ppmp-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .action-bar {
                justify-content: center;
                flex-direction: column;
                gap: 0.5rem;
            }
            .budget-info {
                width: 100%;
                justify-content: center;
            }
            .excel-table {
                font-size: 12px;
                min-width: 600px;
            }
        }

        @media (max-width: 480px) {
            .approve-btn, .delete-btn, .action-button {
                width: 100%;
            }
            .excel-table {
                min-width: 500px;
            }
        }
    </style>
</x-layouts.app>
