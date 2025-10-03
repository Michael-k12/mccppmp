<?php

namespace App\Http\Controllers;

use App\Models\Ppmp;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();

    // ✅ Make sure user is logged in
    if (!$user) {
        return redirect()->route('login')->withErrors('Please log in to access the dashboard.');
    }

    // ✅ Make sure user has a role
    if (!$user->role) {
        abort(403, 'User role not found.');
    }

    switch ($user->role) {
        case 'principal':
            $years = Ppmp::where('status', 'Approved')
                ->selectRaw('YEAR(milestone_date) as year')
                ->distinct()
                ->pluck('year')
                ->sortDesc();

            $selectedYear = $request->get('year') ?? $years->first();

            $departments = ['NURSE', 'LIBRARY', 'BSIT', 'BSHM', 'BSBA', 'BSED'];

            $approvedByDepartmentRaw = Ppmp::where('status', 'Approved')
                ->whereYear('milestone_date', $selectedYear)
                ->select('department', DB::raw('SUM(estimated_budget) as total'))
                ->groupBy('department')
                ->pluck('total', 'department')
                ->toArray();

            $chartLabels = [];
            $chartData = [];

            foreach ($departments as $dept) {
                $chartLabels[] = strtoupper($dept);
                $chartData[] = $approvedByDepartmentRaw[$dept] ?? 0;
            }

            $submittedCount = Ppmp::where('status', 'Submitted')->count();
            $approvedCount = Ppmp::where('status', 'Approved')->count();
            $latestBudget = Budget::latest()->first();

            return view('dashboards.principal', compact(
                'submittedCount',
                'latestBudget',
                'approvedCount',
                'years',
                'selectedYear',
                'chartLabels',
                'chartData'
            ));

        case 'BSIT':
        case 'BSBA':
        case 'BSHM':
        case 'BSED':
        case 'NURSE':
        case 'LIBRARY':

            $department = $user->role;

            $years = Ppmp::where('department', $department)
                ->selectRaw('YEAR(milestone_date) as year')
                ->distinct()
                ->pluck('year')
                ->sortDesc();

            $selectedYear = $request->get('year') ?? $years->first();

            $submittedCount = Ppmp::where('department', $department)
                ->where('status', 'Submitted')
                ->whereYear('milestone_date', $selectedYear)
                ->count();

            $approvedCount = Ppmp::where('department', $department)
                ->where('status', 'Approved')
                ->whereYear('milestone_date', $selectedYear)
                ->count();

            $estimatedTotal = Ppmp::where('department', $department)
                ->where('status', 'Approved')
                ->whereYear('milestone_date', $selectedYear)
                ->sum('estimated_budget');

            $ppmps = Ppmp::where('department', $department)
                ->whereYear('milestone_date', $selectedYear)
                ->latest()
                ->take(5)
                ->get();

            $itemCount = Ppmp::where('department', $department)
                ->whereYear('milestone_date', $selectedYear)
                ->count();

            $recentPpmps = Ppmp::where('department', $department)
                ->whereYear('milestone_date', $selectedYear)
                ->latest()
                ->take(5)
                ->get();

            $approvedPpmps = Ppmp::where('department', $department)
                ->where('status', 'Approved')
                ->selectRaw('YEAR(milestone_date) as year, SUM(estimated_budget) as total')
                ->groupBy('year')
                ->orderBy('year')
                ->get();

            $chartLabels = $approvedPpmps->pluck('year')->toArray();
            $chartData = $approvedPpmps->pluck('total')->toArray();

            $latestBudget = Budget::orderBy('created_at', 'desc')->first();

            $departments = ['BSIT', 'BSBA', 'BSED', 'BSHM', 'NURSE', 'LIBRARY'];
            $departmentBudgets = [];

            if ($latestBudget && $latestBudget->amount > 0) {
                $equalShare = $latestBudget->amount / count($departments);
                foreach ($departments as $dept) {
                    $departmentBudgets[$dept] = round($equalShare, 2);
                }
            } else {
                foreach ($departments as $dept) {
                    $departmentBudgets[$dept] = 0;
                }
            }

            return view('dashboards.' . strtolower($department), compact(
                'department',
                'submittedCount',
                'approvedCount',
                'estimatedTotal',
                'ppmps',
                'itemCount',
                'recentPpmps',
                'chartLabels',
                'chartData',
                'selectedYear',
                'years',
                'latestBudget',
                'departmentBudgets'
            ));

        default:
            abort(403, 'Unauthorized access');
    }
}


}


