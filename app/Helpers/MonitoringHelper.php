<?php

namespace App\Helpers;

class MonitoringHelper
{
    public static function logEvent($type, $message)
    {
        $filePath = storage_path('logs/monitoring.log');
        $logMessage = '[' . now()->format('Y-m-d H:i:s') . "] $type: $message" . PHP_EOL;

        file_put_contents($filePath, $logMessage, FILE_APPEND);
    }

    public static function getLogs()
    {
        $filePath = storage_path('logs/monitoring.log');

        if (!file_exists($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];

        foreach ($lines as $line) {
            if (preg_match('/\[(.*?)\]\s+(.*?):\s+(.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'type' => $matches[2],
                    'message' => $matches[3],
                ];
            } else {
                $logs[] = [
                    'timestamp' => null,
                    'type' => 'Unknown',
                    'message' => $line,
                ];
            }
        }

        return array_reverse($logs);
    }
}
