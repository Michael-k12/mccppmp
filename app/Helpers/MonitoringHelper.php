<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class MonitoringHelper
{
    /**
     * Log suspicious events (unauthorized access, unusual activity, etc.)
     */
    public static function logEvent($eventType, $description)
    {
        // Log file path inside storage/logs/
        $logPath = storage_path('logs/monitoring.log');

        $user = Auth::check() ? Auth::user()->email : 'Guest';
        $ip = request()->ip();
        $time = now()->toDateTimeString();

        $entry = "[$time] [$eventType] User: $user | IP: $ip | $description" . PHP_EOL;

        // Append the event to the monitoring.log file
        File::append($logPath, $entry);
    }

    /**
     * Read log entries from the file (for displaying in the Monitoring page)
     */
    public static function readLogs()
    {
        $logPath = storage_path('logs/monitoring.log');

        if (!File::exists($logPath)) {
            return [];
        }

        // Read log lines, newest first
        $lines = array_reverse(file($logPath, FILE_IGNORE_NEW_LINES));
        return $lines;
    }
}
