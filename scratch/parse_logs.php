<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit(1);
}

$lines = file($logFile);
echo "Total log lines: " . count($lines) . "\n";

$fcmLines = [];
foreach ($lines as $i => $line) {
    if (stripos($line, 'fcm') !== false || stripos($line, 'notification') !== false) {
        $fcmLines[] = ($i + 1) . ": " . trim($line);
    }
}

echo "Found " . count($fcmLines) . " lines matching 'fcm' or 'notification'.\n";
echo "Printing last 50 matches:\n";
foreach (array_slice($fcmLines, -50) as $l) {
    echo $l . "\n";
}
