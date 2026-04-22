<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

$count = 0;
foreach(Category::whereNotNull('image')->get() as $c) {
    if(!str_contains($c->image, 'uploads/')) {
        $c->image = 'uploads/' . $c->image;
        $c->save();
        $count++;
    }
}
echo "Updated $count categories.";
