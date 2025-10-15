<?php

namespace App\Http\Controllers;

use App\Helpers\MonitoringHelper;

class SecurityMonitoringController extends Controller
{
    public function index()
    {
        $logs = MonitoringHelper::getLogs();
        return view('security.index', compact('logs'));
    }
}
