<?php
$file = 'c:/Users/eng.Hagar Ahmed/Mazad/resources/views/ads/show.blade.php';
$content = file_get_contents($file);

$words = ['toggle-visibility', 'reply', 'answer', 'تعديل الرد', 'حالة السؤال'];
foreach ($words as $word) {
    $pos = 0;
    while (($pos = strpos($content, $word, $pos)) !== false) {
        echo "Found '{$word}' at {$pos}\n";
        echo "Context: " . substr($content, max(0, $pos - 150), 300) . "\n\n";
        $pos += strlen($word);
    }
}
