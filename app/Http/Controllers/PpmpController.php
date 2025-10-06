<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ppmp; // Make sure you have this model
use Illuminate\Support\Facades\Auth;
use App\Models\Request as PPMPRequest;
use App\Models\Budget;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use App\Models\Item;


class PpmpController extends Controller
{
    public function index()
    {
        $items = Item::all();
        return view('items.index', compact('items'));
    }
public function manage()
    {
        $userDepartment = Auth::user()->role;

        $ppmps = Ppmp::where('department', $userDepartment)
                    ->whereNotIn('status', ['Submitted', 'Approved'])
                    ->get();

        $activeBudget = Budget::where('is_ended', false)->latest()->first();
        $departments = ['BSIT', 'BSBA', 'BSED', 'BSHM', 'NURSE', 'LIBRARY'];

        $allocatedBudget = 0;
        $remainingBudget = 0;

        if ($activeBudget && $activeBudget->amount > 0) {
            $allocatedBudget = round($activeBudget->amount / count($departments), 2);
            $spent = Ppmp::where('department', $userDepartment)->sum('estimated_budget');
            $remainingBudget = $allocatedBudget - $spent;
        }

        foreach ($ppmps as $ppmp) {
            $ppmp->allocated_budget = $allocatedBudget;
        }

        return view('ppmp.manage', compact('ppmps', 'allocatedBudget', 'remainingBudget'));
    }

// AJAX endpoint for updating remaining budget dynamically
public function getRemainingBudget()
    {
        $userDepartment = Auth::user()->role;

        $activeBudget = Budget::where('is_ended', false)->latest()->first();
        $departments = ['BSIT', 'BSBA', 'BSED', 'BSHM', 'NURSE', 'LIBRARY'];

        if (!$activeBudget) {
            return response()->json([
                'allocatedBudget' => 0,
                'remainingBudget' => 0
            ]);
        }

        $allocatedBudget = round($activeBudget->amount / count($departments), 2);
        $spent = Ppmp::where('department', $userDepartment)->sum('estimated_budget');
        $remainingBudget = $allocatedBudget - $spent;

        return response()->json([
            'allocatedBudget' => $allocatedBudget,
            'remainingBudget' => $remainingBudget
        ]);
    }
 public function edit($id)
    {
        $ppmp = Ppmp::findOrFail($id); // ✅ fixed
        return view('ppmp.edit', compact('ppmp'));
    }

    public function destroy($id)
    {
        $ppmp = Ppmp::findOrFail($id); // ✅ fixed
        $ppmp->delete();

        return redirect()->route('ppmp.manage')->with('success', 'Deleted successfully.');
    }

public function updateQuantity(Request $request, $id)
{
    $request->validate([
        'adjustment' => 'required|numeric',
        'current_quantity' => 'required|numeric',
        'mode' => 'required|in:add,subtract',
    ]);

    $ppmp = Ppmp::findOrFail($id);
    $department = $ppmp->department;

    // Get the latest active budget
    $activeBudget = Budget::where('is_ended', false)->latest()->first();
    $departments = ['BSIT','BSBA','BSED','BSHM','NURSE','LIBRARY'];

    $allocatedBudget = 0;
    if ($activeBudget && $activeBudget->amount > 0) {
        $allocatedBudget = round($activeBudget->amount / count($departments), 2);
    }

    // Calculate total spent so far for this department (excluding current item)
    $spent = Ppmp::where('department', $department)
                ->where('id', '!=', $ppmp->id)
                ->sum('estimated_budget');

    // Handle add or subtract mode ✅
    $adjustment = abs($request->adjustment); // Always positive
    if ($request->mode === 'add') {
        $newQty = $request->current_quantity + $adjustment;
    } else {
        $newQty = $request->current_quantity - $adjustment;
        if ($newQty < 0) $newQty = 0; // Prevent negative quantity
    }

    // New estimated budget
    $newEstimatedBudget = $newQty * $ppmp->price;

    // Prevent exceeding allocated budget
    if (($spent + $newEstimatedBudget) > $allocatedBudget) {
        return redirect()->back()->with('duplicate_error', 
            "Cannot update. Estimated budget ₱{$newEstimatedBudget} exceeds remaining department budget ₱" . 
            number_format($allocatedBudget - $spent, 2)
        );
    }

    // Update PPMP
    $ppmp->quantity = $newQty;
    $ppmp->estimated_budget = $newEstimatedBudget;
    $ppmp->save();

    return redirect()->back()->with('success', 'Quantity updated and budget recalculated.');
}

    
public function create()
{
    $items = Item::all(); 
    $activeBudget = Budget::where('is_ended', false)->latest()->first();

    $departments = ['BSIT', 'BSBA', 'BSED', 'BSHM', 'NURSE', 'LIBRARY'];
    $departmentBudgets = [];

    // Calculate per-department budget share
    if ($activeBudget && $activeBudget->amount > 0) {
        $equalShare = $activeBudget->amount / count($departments);

        foreach ($departments as $dept) {
            $departmentBudgets[$dept] = round($equalShare, 2);
        }
    } else {
        foreach ($departments as $dept) {
            $departmentBudgets[$dept] = 0;
        }
    }

    $userDepartment = auth()->user()->role;

    // Calculate how much this department has already spent
    $spent = Ppmp::where('department', $userDepartment)
                 ->whereYear('milestone_date', $activeBudget?->year ?? now()->year)
                 ->sum('estimated_budget');

    // Remaining budget for this department
    $remainingBudget = max(($departmentBudgets[$userDepartment] ?? 0) - $spent, 0);

    return view('ppmp.create', [
        'items' => $items,
        'isProposalActive' => $activeBudget !== null,
        'remainingBudget' => $remainingBudget
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'classification' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'unit' => 'required|string|max:50',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:1',
        'estimated_budget' => 'required|numeric|min:0',
        'mode_of_procurement' => 'required|string|max:255',
        'milestone_month' => 'required|string'
    ]);

    $department = auth()->user()->role;
    $activeBudget = Budget::where('is_ended', false)->latest()->first();

    if (!$activeBudget) {
        return redirect()->back()->withErrors(['milestone' => 'No active budget found.']);
    }

    // Combine active budget year + selected month
    $milestoneDate = $activeBudget->year . '-' . $request->milestone_month;

    // Duplicate check
    $existing = Ppmp::where('description', $request->description)
        ->where('department', $department)
        ->where('milestone_date', $milestoneDate)
        ->exists();

    if ($existing) {
        return redirect()->back()->with('duplicate_error', 
            'Duplicate Item. You already added this item. Go to Manage if you want to add more.'
        );
    }

    // Remaining budget calculation per department
    $departments = ['BSIT', 'BSBA', 'BSED', 'BSHM', 'NURSE', 'LIBRARY'];
    $equalShare = $activeBudget->amount / count($departments);

    $departmentRemaining = $equalShare -
        Ppmp::where('department', $department)
            ->whereYear('milestone_date', $activeBudget->year)
            ->sum('estimated_budget');

    if ($request->estimated_budget > $departmentRemaining) {
        return redirect()->back()->with('duplicate_error', 
            "Cannot add item. Estimated budget ₱{$request->estimated_budget} exceeds your remaining budget ₱{$departmentRemaining}."
        );
    }

    // Save PPMP
    Ppmp::create([
        'classification' => $request->classification,
        'description' => $request->description,
        'unit' => $request->unit,
        'price' => $request->price,
        'quantity' => $request->quantity,
        'estimated_budget' => $request->estimated_budget,
        'mode_of_procurement' => $request->mode_of_procurement,
        'milestone_date' => $milestoneDate,
        'department' => $department,
    ]);

    // ✅ Redirect to manage page after success
    return redirect()->route('ppmp.manage')
        ->with('success', 'Item added successfully!');
}










    
public function principalview()
{
    if (Auth::user()->role !== 'principal') {
        abort(403, 'Unauthorized');
    }

    $ppmps = Ppmp::where('status', 'Submitted')
                 ->orderBy('department')
                 ->get();

    $latestBudget = \App\Models\Budget::latest()->first();

    // Sum of estimated_budget from submitted PPMPs
    $ppmpTotal = $ppmps->sum('estimated_budget');

    return view('ppmp.principalview', compact('ppmps', 'latestBudget', 'ppmpTotal'));
}


public function submitToPrincipal()
{
    $userDepartment = Auth::user()->role;
    $userName = Auth::user()->name;

    // Get all unsubmitted PPMPs for this department
    $ppmps = Ppmp::where('department', $userDepartment)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'Pending');
                })
                ->get();

    foreach ($ppmps as $ppmp) {
        // Save submission to requests table
        PPMPRequest::create([
            'ppmp_id' => $ppmp->id,
            'submitted_by' => $userName,
            'status' => 'Submitted',
        ]);

        // Optionally update status in ppmps table
        $ppmp->status = 'Submitted';
        $ppmp->save();
    }

    return redirect()->route('ppmp.view')->with('success', 'Submitted to Principal!.');
}

public function view()
{
    $userDepartment = Auth::user()->role;

    // Only show unsubmitted PPMPs
    $ppmps = Ppmp::where('department', $userDepartment)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'Pending');
                })
                ->get();

    return view('ppmp.view', compact('ppmps'));
}

public function batchApprove(Request $request)
{
    // Get latest budget
    $latestBudget = \App\Models\Budget::latest()->first();
    $ppmpTotal = \App\Models\Ppmp::sum('estimated_budget');

    // Check if there’s no budget
    if (!$latestBudget) {
        return redirect()->back()->with('error', 'No allocated budget set.');
    }

    // Check if budgets mismatch
    if ($ppmpTotal != $latestBudget->amount) {
        return redirect()->back()->with('error', 'Approval denied! Purpose budget must match allocated budget.');
    }

    // Approve all PPMPs
    \App\Models\Ppmp::whereIn('id', $request->ppmp_ids)
        ->update(['status' => 'approved']);

    // If approved, set remaining budget to zero
    $latestBudget->amount = $ppmpTotal; // allocated equals purpose
    $latestBudget->save();

    return redirect()->back()->with('approved', true);
}


public function realign(Request $request)
{
    try {
        // Validate input
        $request->validate([
            'adjustment' => 'required|numeric',
        ]);

        // Get latest budget
        $latestBudget = \App\Models\Budget::latest()->first();
        if (!$latestBudget) {
            return response()->json(['status' => 'error', 'message' => 'No budget set'], 404);
        }

        // Update allocated budget
        $latestBudget->amount += $request->adjustment;
        $latestBudget->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Budget realigned successfully!',
            'new_budget' => number_format($latestBudget->amount, 2)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong. ' . $e->getMessage()
        ], 500);
    }
}

public function approved(Request $request)
{
    $selectedYear = $request->get('year');

    $query = Ppmp::where('status', 'Approved');

    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $ppmps = $query->get();

    $availableYears = Ppmp::where('status', 'Approved')
        ->whereNotNull('milestone_date')
        ->selectRaw('YEAR(milestone_date) as year')
        ->groupBy('year')
        ->orderBy('year', 'desc')
        ->pluck('year');

    return view('ppmp.approved', compact('ppmps', 'availableYears', 'selectedYear'));
}

 public function editDepartmentQuantities($department)
    {
        if ($department === 'all') {
            $ppmps = Ppmp::where('status', 'submitted')->get(); // ✅ fixed
        } else {
            $ppmps = Ppmp::where('department', $department)->get(); // ✅ fixed
        }

        return view('ppmp.edit-department-quantities', compact('ppmps', 'department'));
    }

 public function updateDepartmentQuantities(Request $request, $department)
    {
        foreach ($request->ppmp_ids as $index => $id) {
            $ppmp = Ppmp::findOrFail($id); // ✅ fixed
            $newQty = $request->quantities[$index];
            $ppmp->quantity = $newQty;
            $ppmp->estimated_budget = $newQty * $ppmp->price;

            if ($ppmp->estimated_budget < 100000) {
                $ppmp->mode_of_procurement = "Small Value Procurement";
            } elseif ($ppmp->estimated_budget < 500000) {
                $ppmp->mode_of_procurement = "Shopping";
            } else {
                $ppmp->mode_of_procurement = "Bidding";
            }

            $ppmp->save();
        }

        return redirect()->route('ppmp.principalview')->with('success', 'Quantities updated successfully.');
    }

public function exportPdf()
{
    // Get selected year from the request, or fallback to current year
    $year = request('year', now()->year);

    // Get PPMPs for the selected year and approved status
    $ppmps = Ppmp::where('status', 'approved')
                ->whereYear('milestone_date', $year)
                ->get();

    $grouped = $ppmps->groupBy('classification');

    // Load the PDF view with data
    $pdf = Pdf::loadView('ppmp.pdf', [
        'ppmps' => $ppmps,
        'grouped' => $grouped,
        'year'   => $year, // Optional: Pass year to display it in the PDF
    ])->setPaper('a4', 'portrait');

    return $pdf->download("Approved_PPMPs_{$year}.pdf");
}
public function bsit(Request $request)
{
    $selectedYear = $request->input('year');

    // Base query for BSIT + approved
    $query = Ppmp::where('department', 'BSIT')
                 ->where('status', 'approved');

    // Apply year filter if selected
    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $bsitPpmps = $query->get();

    // Get unique years from BSIT approved PPMPs
    $availableYears = Ppmp::where('department', 'BSIT')
                          ->where('status', 'approved')
                          ->selectRaw('YEAR(milestone_date) as year')
                          ->distinct()
                          ->orderBy('year', 'desc')
                          ->pluck('year');

    return view('ppmp.bsit', compact('bsitPpmps', 'availableYears', 'selectedYear'));
}

public function bsed(Request $request)
{
    $selectedYear = $request->input('year');

    $availableYears = Ppmp::where('department', 'BSED')
        ->where('status', 'approved')
        ->pluck('milestone_date')
        ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('Y'))
        ->unique()
        ->sortDesc();

    $query = Ppmp::where('department', 'BSED')->where('status', 'approved');

    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $bsedPpmps = $query->get();

    return view('ppmp.bsed', compact('bsedPpmps', 'availableYears', 'selectedYear'));
}


public function bsba(Request $request)
{
    $selectedYear = $request->input('year');

    // Get distinct milestone years for BSBA
    $availableYears = Ppmp::where('department', 'BSBA')
        ->where('status', 'approved')
        ->selectRaw('YEAR(milestone_date) as year')
        ->distinct()
        ->pluck('year');

    // Filter based on selected year
    $query = Ppmp::where('department', 'BSBA')->where('status', 'approved');

    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $bsbaPpmps = $query->get();

    return view('ppmp.bsba', compact('bsbaPpmps', 'availableYears', 'selectedYear'));
}

public function nurse(Request $request)
{
    $selectedYear = $request->input('year');

    $availableYears = Ppmp::where('department', 'Nurse')
        ->where('status', 'approved')
        ->pluck('milestone_date')
        ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('Y'))
        ->unique()
        ->sortDesc();

    $query = Ppmp::where('department', 'Nurse')->where('status', 'approved');

    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $nursePpmps = $query->get();

    return view('ppmp.nurse', compact('nursePpmps', 'availableYears', 'selectedYear'));
}

public function bshm(Request $request)
{
    $selectedYear = $request->input('year');

    // Get unique years where BSHM PPMPs exist
    $availableYears = Ppmp::where('department', 'BSHM')
        ->where('status', 'approved')
        ->pluck('milestone_date')
        ->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('Y');
        })
        ->unique()
        ->sortDesc();

    // Filter PPMPs by selected year if provided
    $query = Ppmp::where('department', 'BSHM')->where('status', 'approved');

    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $bshmPpmps = $query->get();

    return view('ppmp.bshm', compact('bshmPpmps', 'availableYears', 'selectedYear'));
}


public function library(Request $request)
{
    $selectedYear = $request->input('year');

    $availableYears = Ppmp::where('department', 'Library')
        ->where('status', 'approved')
        ->pluck('milestone_date')
        ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('Y'))
        ->unique()
        ->sortDesc();

    $query = Ppmp::where('department', 'Library')->where('status', 'approved');

    if ($selectedYear) {
        $query->whereYear('milestone_date', $selectedYear);
    }

    $libraryPpmps = $query->get();

    return view('ppmp.library', compact('libraryPpmps', 'availableYears', 'selectedYear'));
}


public function downloadBsit(Request $request)
{
    $year = $request->input('year');

    $query = Ppmp::where('department', 'BSIT')
        ->where('status', 'approved');

    if ($year) {
        $query->whereYear('milestone_date', $year);
    }

    $ppmps = $query->orderBy('classification')->get();

    if ($ppmps->isEmpty()) {
        return back()->with('error', 'No approved BSIT Project Plan available for download' . ($year ? " in $year." : '.'));
    }

    $pdf = Pdf::loadView('ppmp.pdf.bsit', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('BSIT_PPMP_' . ($year ?? now()->format('Y')) . '.pdf');
}

public function downloadBsba(Request $request)
{
    $year = $request->input('year');

    $query = Ppmp::where('department', 'BSBA')
        ->where('status', 'approved');

    if ($year) {
        $query->whereYear('milestone_date', $year);
    }

    $ppmps = $query->orderBy('classification')->get();

    if ($ppmps->isEmpty()) {
        return back()->with('error', 'No approved BSBA Project Plan available for download' . ($year ? " in $year." : '.'));
    }

    $pdf = Pdf::loadView('ppmp.pdf.bsba', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('BSBA_PPMP_' . ($year ?? now()->format('Y')) . '.pdf');
}

public function downloadBsed(Request $request)
{
    $year = $request->input('year');

    $query = Ppmp::where('department', 'BSED')
        ->where('status', 'approved');

    if ($year) {
        $query->whereYear('milestone_date', $year);
    }

    $ppmps = $query->orderBy('classification')->get();

    if ($ppmps->isEmpty()) {
        return back()->with('error', 'No approved BSED Project Plan available for download' . ($year ? " in $year." : '.'));
    }

    $pdf = Pdf::loadView('ppmp.pdf.bsed', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('BSED_PPMP_' . ($year ?? now()->format('Y')) . '.pdf');
}

public function downloadBshm(Request $request)
{
    $year = $request->input('year');

    $query = Ppmp::where('department', 'BSHM')
        ->where('status', 'approved');

    if ($year) {
        $query->whereYear('milestone_date', $year);
    }

    $ppmps = $query->orderBy('classification')->get();

    if ($ppmps->isEmpty()) {
        return back()->with('error', 'No approved BSHM Project Plan available for download' . ($year ? " in $year." : '.'));
    }

    $pdf = Pdf::loadView('ppmp.pdf.bshm', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('BSHM_PPMP_' . ($year ?? now()->format('Y')) . '.pdf');
}

public function downloadLibrary(Request $request)
{
    $year = $request->input('year');

    $query = Ppmp::where('department', 'LIBRARY')
        ->where('status', 'approved');

    if ($year) {
        $query->whereYear('milestone_date', $year);
    }

    $ppmps = $query->orderBy('classification')->get();

    if ($ppmps->isEmpty()) {
        return back()->with('error', 'No approved LIBRARY Project Plan available for download' . ($year ? " in $year." : '.'));
    }

    $pdf = Pdf::loadView('ppmp.pdf.library', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('LIBRARY_PPMP_' . ($year ?? now()->format('Y')) . '.pdf');
}

public function downloadNurse(Request $request)
{
    $year = $request->input('year');

    $query = Ppmp::where('department', 'NURSE')
        ->where('status', 'approved');

    if ($year) {
        $query->whereYear('milestone_date', $year);
    }

    $ppmps = $query->orderBy('classification')->get();

    if ($ppmps->isEmpty()) {
        return back()->with('error', 'No approved NURSE Project Plan available for download' . ($year ? " in $year." : '.'));
    }

    $pdf = Pdf::loadView('ppmp.pdf.nurse', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('NURSE_PPMP_' . ($year ?? now()->format('Y')) . '.pdf');
}

public function deleteYear(Request $request)
{
    $year = $request->input('year');

    if (!$year) {
        return back()->with('error', 'No year selected.');
    }

    // Make sure milestone_date is used for filtering
    $deleted = \App\Models\Ppmp::whereYear('milestone_date', $year)->delete();

    if ($deleted > 0) {
        return back()->with('success', "All PPMP records for year {$year} have been deleted.");
    } else {
        return back()->with('error', "No records found for year {$year}.");
    }
}

}
