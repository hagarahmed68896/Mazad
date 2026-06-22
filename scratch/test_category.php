<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;

try {
    $category = Category::with('pages.properties', 'properties')->find(2);
    if (!$category) {
        echo "Category 2 not found.\n";
        exit;
    }
    echo "Category: " . $category->name . "\n";
    echo "Pages:\n";
    foreach ($category->pages as $page) {
        echo "  Page: " . $page->name . "\n";
        foreach ($page->properties as $prop) {
            echo "    Property: ID={$prop->id} | Label={$prop->label} | Type={$prop->input_type}\n";
        }
    }
    echo "Global Category Properties:\n";
    foreach ($category->properties as $prop) {
        echo "  Property: ID={$prop->id} | Label={$prop->label} | Type={$prop->input_type}\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
