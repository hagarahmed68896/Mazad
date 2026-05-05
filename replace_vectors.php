<?php

$dir = __DIR__ . '/resources/views/admin';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($files as $file) {
    if ($file->isDir()) continue;
    if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) !== 'php') continue;

    $content = file_get_contents($file->getPathname());
    $originalContent = $content;

    // Replace Vector.svg img tag
    $content = preg_replace(
        '/<img\s+src="\{\{\s*asset\(\'images\/Vector\.svg\'\)\s*\}\}"\s+class="([^"]+)"\s*alt="[^"]*"\s*>/',
        '<x-riyal-icon class="$1" />',
        $content
    );

    // Some might not have alt
    $content = preg_replace(
        '/<img\s+src="\{\{\s*asset\(\'images\/Vector\.svg\'\)\s*\}\}"\s+class="([^"]+)"\s*>/',
        '<x-riyal-icon class="$1" />',
        $content
    );

    // Replace explicit "ريال" words that are wrapped in spans without classes in some places, or just the explicit ones we know
    $content = str_replace(
        '<span class="text-xs font-black">ريال</span>',
        '<x-riyal-icon />',
        $content
    );
    $content = str_replace(
        '<span class="text-xs font-black text-slate-400">ريال</span>',
        '<x-riyal-icon class="text-slate-400" />',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file->getPathname(), $content);
        echo "Updated: " . $file->getPathname() . "\n";
    }
}
