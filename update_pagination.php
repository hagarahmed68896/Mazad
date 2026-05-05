<?php

$files = [
    'resources/views/admin/cities/index.blade.php' => 'regions',
    'resources/views/admin/categories/index.blade.php' => 'categories',
    'resources/views/admin/banners/index.blade.php' => 'banners',
    'resources/views/admin/ads/tenders.blade.php' => 'ads',
    'resources/views/admin/ads/services.blade.php' => 'services',
    'resources/views/admin/ads/incomplete.blade.php' => 'ads',
    'resources/views/admin/ads/direct_purchase.blade.php' => 'ads'
];

foreach($files as $f => $var) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $c = preg_replace('/\{\{\s*\$'.$var.'->links\(\)\s*\}\}/', '<x-custom-pagination :paginator="$'.$var.'" />', $c);
    file_put_contents($f, $c);
    echo "Updated $f\n";
}
