<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::orderBy('year', 'desc')->get();
        $activeBudget = Budget::where('is_ended', false)->latest()->first();
        $recentEndedBudget = Budget::where('is_ended', true)->orderBy('year', 'desc')->first();

        return view('budget.index', compact('budgets', 'activeBudget', 'recentEndedBudget'));
    }

    // Store new budget (start proposal)
    public function store(Request $request)
    {
        $request->validate([
            'milestone_date' => 'required|numeric|min:2000|max:2100',
            'amount' => 'required|numeric|min:0',
        ]);

        // Prevent start if a proposal is still active
        $active = Budget::where('is_ended', false)->first();
        if ($active) {
            return redirect()->back()->with('warning', 'Cannot start a new proposal until the current one is ended.');
        }

        Budget::create([
            'year' => $request->milestone_date,
            'amount' => $request->amount,
            'is_ended' => false,
        ]);

        return redirect()->back()->with('success', 'Budget proposal started successfully.');
    }

    // End current proposal
    public function end($id)
    {
        $budget = Budget::findOrFail($id);
        $budget->is_ended = true;
        $budget->save();

        return redirect()->back()->with('success', 'Proposal Ended!');
    }

    // Delete selected budgets
    public function deleteSelected(Request $request)
    {
        $ids = $request->input('selected', []);
        if (empty($ids)) {
            return redirect()->back()->with('warning', 'No budgets selected.');
        }

        Budget::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', 'Selected budgets deleted successfully.');
    }
}
