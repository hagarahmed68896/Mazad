<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

foreach (Category::whereNull('parent_id')->with('pages')->get() as $cat) {
    echo "Category: {$cat->name} (ID: {$cat->id})" . PHP_EOL;
    if ($cat->pages->isEmpty()) {
        echo "  - No pages" . PHP_EOL;
    } else {
        foreach ($cat->pages as $page) {
            echo "  - Page ID: {$page->id}, Name: '{$page->name}'" . PHP_EOL;
        }
    }
}
