<x-layouts.app :title="'Principal View'">
<div class="container mx-auto px-4 py-6">
    <h2 class="ppmp-header">
        <span>Project Plan Approval</span>

        @if ($latestBudget)
            <span class="budget-status 
                {{ $ppmpTotal > $latestBudget->amount ? 'over-budget' : 
                    ($ppmpTotal == $latestBudget->amount ? 'exact-budget' : 'within-budget') }}">
                Total Budget: ₱{{ number_format($latestBudget->amount, 2) }}
            </span>
        @else
            <span class="no-budget">No budget set</span>
        @endif
    </h2>

    <p class="ppmp-subtotal">
        Total Submitted: ₱{{ number_format($ppmpTotal, 0) }}
    </p>

    <style>
        .ppmp-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .budget-status {
            font-size: 0.875rem;
            padding: 4px 12px;
            border-radius: 6px;
        }
        .within-budget {
            background-color: #d1fae5;
            color: #065f46;
        }
        .over-budget {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .exact-budget {
            background-color: #bfdbfe;
            color: #1d4ed8;
        }
        .no-budget {
            font-size: 0.875rem;
            color: #dc2626;
        }
        .ppmp-subtotal {
            font-size: 0.875rem;
            color: #4b5563;
            margin-bottom: 1rem;
        }
        .action-button {
            background-color: #3b82f6;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .action-button:hover {
            background-color: #2563eb;
        }
        .approve-btn {
            background-color: #16a34a;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
        }
        .approve-btn:hover {
            background-color: #15803d;
        }

        /* Excel Table Style */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        .excel-table th, .excel-table td {
            border: 1px solid #cbd5e0;
            padding: 6px 10px;
            text-align: left;
            background-color: white;
        }

        .excel-table th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: bold;
        }

        .excel-table tr:hover td {
            background-color: #f9fafb;
        }

        .disabled-btn {
            background-color: #9ca3af !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }
    </style>

    <div class="flex justify-end gap-2 mb-4">
        @if ($latestBudget && $ppmpTotal == $latestBudget->amount)
            <form method="POST" action="{{ route('ppmp.batchApprove') }}" onsubmit="return confirm('Approve all submitted Project Plan?');">
                @csrf
                @foreach ($ppmps as $ppmp)
                    <input type="hidden" name="ppmp_ids[]" value="{{ $ppmp->id }}">
                @endforeach
                <button type="submit" class="approve-btn">Approve All</button>
            </form>
        @else
            <button type="button" class="approve-btn disabled-btn" disabled title="Approval is only allowed if the total amount exactly matches the allocated budget.">
                Approve All
            </button>
        @endif

        <a href="{{ route('ppmp.editDepartmentQuantities', 'all') }}" class="action-button">
            Edit Quantities
        </a>
    </div>

    <div class="overflow-x-auto">
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
                @foreach ($ppmps as $ppmp)
                    <tr>
                        <td>{{ $ppmp->classification }}</td>
                        <td>{{ $ppmp->description }}</td>
                        <td>{{ $ppmp->unit }}</td>
                        <td>{{ $ppmp->quantity }}</td>
                        <td>{{ number_format($ppmp->price, 2) }}</td>
                        <td>{{ number_format($ppmp->estimated_budget, 2) }}</td>
                        <td>{{ $ppmp->mode_of_procurement }}</td>
                        <td>{{ \Carbon\Carbon::parse($ppmp->milestone_date)->format('F d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-layouts.app>
