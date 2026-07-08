<?php
$file = 'c:/Users/eng.Hagar Ahmed/Mazad/resources/views/ads/show.blade.php';
$content = file_get_contents($file);
echo substr($content, 42000, 10000);
