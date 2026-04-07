<?php

$dir = __DIR__ . '/resources/views/auth/';
$files = glob($dir . '*.blade.php');

foreach($files as $file) {
    if (!is_file($file)) continue;
    $content = file_get_contents($file);
    
    // Normalize line endings
    $content = str_replace("\r\n", "\n", $content);
    
    // 1. Hex Colors mapping
    $content = str_replace('#146AF5', '#1A73E8', $content);
    $content = str_replace('text-slate-800', 'text-[#1C1C1C]', $content);
    $content = str_replace('text-slate-900', 'text-[#1C1C1C]', $content);
    $content = str_replace('text-slate-500', 'text-[#6F6F6F]', $content);
    $content = str_replace('text-slate-700', 'text-[#1C1C1C]', $content); // Dark texts to 1C1C1C
    $content = str_replace('bg-slate-50', 'bg-[#F7F7F7]', $content);
    $content = str_replace('bg-[#F8FAFC]', 'bg-[#EEEEEE]', $content);
    $content = str_replace('border-slate-200', 'border-[#B0B0B0]', $content);
    $content = str_replace('text-slate-400', 'text-[#B0B0B0]', $content);
    
    // 2. Right Side panel mapping (taking advantage of normalized line endings)
    $target1 = "        <!-- Right Side: Modern Gradient -->\n        <div class=\"hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden flex-col items-center justify-center p-16 z-0\">\n            <!-- Modern Mesh Gradient Background -->\n            <div class=\"absolute inset-0 bg-gradient-to-br from-[#1A73E8] via-[#4F46E5] to-[#7C3AED] pointer-events-none z-0\"></div>\n            <div class=\"absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 opacity-40 blur-[100px] z-0 pointer-events-none\"></div>\n            <div class=\"absolute -bottom-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-tr from-purple-500 to-indigo-500 opacity-40 blur-[100px] z-0 pointer-events-none\"></div>";

    $target2 = "        <!-- Right Side: Modern Gradient -->\n        <div class=\"hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden flex-col items-center justify-center p-16 z-0\">\n            <!-- Modern Mesh Gradient Background -->\n            <div class=\"absolute inset-0 bg-gradient-to-br from-[#146AF5] via-[#4F46E5] to-[#7C3AED] pointer-events-none z-0\"></div>\n            <div class=\"absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 opacity-40 blur-[100px] z-0 pointer-events-none\"></div>\n            <div class=\"absolute -bottom-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-tr from-purple-500 to-indigo-500 opacity-40 blur-[100px] z-0 pointer-events-none\"></div>";

    $replacement = "        <!-- Right Side: Custom Branded Gradient -->\n        <div class=\"hidden lg:flex w-1/2 relative overflow-hidden flex-col items-center justify-center p-16 z-0\" style=\"background: radial-gradient(73.08% 188.96% at 56.27% 73.08%, #1A73E8 0%, #0E3E76 100%);\">";

    $content = str_replace($target1, $replacement, $content);
    $content = str_replace($target2, $replacement, $content);
    
    file_put_contents($file, $content);
}

echo "Tokens successfully injected to " . count($files) . " files.\n";
