<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class SecurityMonitorController extends Controller
{
    public function index()
    {
        $reportPath = public_path('wapiti-reports'); // store Wapiti reports here

        // Ensure directory exists
        if (!File::exists($reportPath)) {
            File::makeDirectory($reportPath, 0755, true);
        }

        // Get files and sort by modification time descending
        $reports = collect(File::files($reportPath))
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        return view('principal.security', compact('reports'));
    }
}
