<?php
$file = 'c:/Users/eng.Hagar Ahmed/Mazad/resources/views/ads/show.blade.php';
if (!file_exists($file)) {
    echo "File not found!\n";
    exit(1);
}
$content = file_get_contents($file);
echo "File size: " . strlen($content) . " bytes\n";

$keywords = ['question', 'questions', 'answer', 'answers', 'سؤال', 'الأسئلة', 'الرد', 'الردود'];
foreach ($keywords as $word) {
    $pos = strpos($content, $word);
    if ($pos !== false) {
        echo "Found word '{$word}' at position {$pos}\n";
        // print a small preview
        echo "Preview: " . substr($content, max(0, $pos - 100), 200) . "\n\n";
    }
}
