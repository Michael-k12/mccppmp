<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\File;

class SecurityMonitoring extends Component
{
    public $reports = [];

    public function mount()
    {
        $this->loadReports();
    }

    public function loadReports()
    {
        $path = public_path('wapiti-reports');
        $files = File::exists($path) ? File::files($path) : [];
        // Sort by last modified descending
        $this->reports = collect($files)->sortByDesc(fn($file) => $file->getMTime());
    }

    public function render()
    {
        return view('livewire.security-monitoring');
    }
}
