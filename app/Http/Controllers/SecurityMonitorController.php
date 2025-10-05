<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class SecurityMonitorController extends Controller
{
    public function index()
    {
        $reportPath = public_path('wapiti-reports');

        // Ensure the directory exists
        if (!File::exists($reportPath)) {
            File::makeDirectory($reportPath, 0755, true);
        }

        // Recursively get all HTML files
        $reports = collect(File::allFiles($reportPath))
            ->filter(fn($file) => $file->getExtension() === 'html')
            ->sortByDesc(fn($file) => $file->getMTime());

        return view('principal.security', compact('reports'));
    }
}
