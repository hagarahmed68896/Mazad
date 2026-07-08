<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit(1);
}

$content = file_get_contents($logFile);
echo "Has 422: " . (strpos($content, '422') !== false ? 'YES' : 'NO') . "\n";
echo "Has 504: " . (strpos($content, '504') !== false ? 'YES' : 'NO') . "\n";
echo "Has aaaaaaa: " . (strpos($content, 'aaaaaaa') !== false ? 'YES' : 'NO') . "\n";

// Let's print any lines matching aaaaaaa
$lines = file($logFile);
foreach ($lines as $i => $line) {
    if (strpos($line, 'aaaaaaa') !== false || strpos($line, 'aaaaaa') !== false) {
        echo "Line " . ($i+1) . ": " . trim($line) . "\n";
    }
}
