<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit(1);
}

$lines = file($logFile);
echo "Total lines: " . count($lines) . "\n";
echo "Printing last 100 lines of log:\n";
$last100 = array_slice($lines, -100);
foreach ($last100 as $line) {
    echo $line;
}
