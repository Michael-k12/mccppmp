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
    public static function getLogs()
{
    $filePath = storage_path('logs/monitoring.log');

    if (!file_exists($filePath)) {
        return [];
    }

    // Read all lines and parse them into structured data
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logs = [];

    foreach ($lines as $line) {
        // Expected format: [2025-10-15 12:30:00] TYPE: message
        if (preg_match('/\[(.*?)\]\s+(.*?):\s+(.*)/', $line, $matches)) {
            $logs[] = [
                'timestamp' => $matches[1],
                'type' => $matches[2],
                'message' => $matches[3],
            ];
        } else {
            // If format doesn’t match, still include raw line
            $logs[] = [
                'timestamp' => null,
                'type' => 'Unknown',
                'message' => $line,
            ];
        }
    }

    return array_reverse($logs); // latest on top
}


  
}
