<?php

$file = 'public/css/landing.css';
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "Searching for 'trend' in $file:\n";
foreach ($lines as $i => $line) {
    if (strpos($line, 'trend') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
