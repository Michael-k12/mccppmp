<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Ppmp;

class PdfExportController extends Controller
{
    public function download(Request $request)
{
    $year = $request->input('year', now()->year); // Use current year if not selected

    $ppmps = Ppmp::where('status', 'approved')
                ->whereYear('milestone_date', $year)
                ->get();

    $pdf = Pdf::loadView('ppmp.pdf', compact('ppmps', 'year'))
              ->setPaper('A4', 'landscape');

    return $pdf->download("Anual_Project_Plan_{$year}.pdf");
}

}

