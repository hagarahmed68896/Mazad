<?php
$lines = file(__DIR__.'/../resources/views/ads/my-sales.blade.php');
foreach ($lines as $i => $line) {
    if (stripos($line, 'seed') !== false || stripos($line, 'توليد') !== false) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
