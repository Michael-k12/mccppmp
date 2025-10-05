<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class SecurityMonitorController extends Controller
{
    public function index()
    {
        $reportPath = public_path('wapiti-reports'); // folder containing reports

        // Ensure directory exists
        if (!File::exists($reportPath)) {
            File::makeDirectory($reportPath, 0755, true);
        }

        // Get files and sort by modification time descending
        $reports = collect(File::files($reportPath))
            ->sortByDesc(fn($file) => $file->getMTime());

        return view('security.security', compact('reports'));
    }
}
