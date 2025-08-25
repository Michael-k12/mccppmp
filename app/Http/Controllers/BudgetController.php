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
    // Prevent start if a proposal is still active
    $active = Budget::where('is_ended', false)->first();
    if ($active) {
        return redirect()->back()->with('success', 'Cannot start a new proposal until the current one is ended.');
    }

    $year = date('Y', strtotime($request->milestone_date));

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



}


