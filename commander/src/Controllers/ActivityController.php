<?php

namespace Olu\Commander\Controllers;

class ActivityController {
    
    public function index() {
        $logFile = __DIR__ . '/../../storage/api_debug.log';
        $logs = [];

        if (file_exists($logFile)) {
            // Read file in reverse or tail it
            // For simplicity, read all but show last 100 lines
            $file = file($logFile);
            $lines = array_slice($file, -100);
            $lines = array_reverse($lines); // Newest first

            foreach ($lines as $line) {
                // Parse simple log format: YYYY-MM-DD HH:MM:SS - Message
                // Or: YYYY-MM-DD HH:MM:SS [Level] Message
                // Our current format is a bit mixed, mostly: YYYY-MM-DD HH:MM:SS [AutoUpdate] Message
                
                // Regex to capture timestamp and message
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.*)$/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $message = trim($matches[2]);
                    
                    // Try to extract level from message if present [Level]
                    $level = 'INFO';
                    if (preg_match('/^\[(.*?)\]/', $message, $levelMatch)) {
                        $level = $levelMatch[1];
                        $message = trim(str_replace($levelMatch[0], '', $message));
                    }

                    $logs[] = [
                        'timestamp' => $timestamp,
                        'level' => $level,
                        'message' => $message
                    ];
                } else {
                    // Fallback for lines that don't match standard format (e.g. stack traces)
                    $logs[] = [
                        'timestamp' => '-',
                        'level' => 'RAW',
                        'message' => $line
                    ];
                }
            }
        }

        view('logs/index', [
            'title' => 'System Activity Logs',
            'logs' => $logs
        ]);
    }
}
