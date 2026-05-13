<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ad;
use App\Models\AdProperty;
use App\Models\Property;

$propIds = Property::where('label', 'وقت المعاينة')->pluck('id')->toArray();
if (!empty($propIds)) {
    $adProps = AdProperty::whereIn('property_id', $propIds)->get();
    foreach ($adProps as $ap) {
        $ad = Ad::find($ap->ad_id);
        if ($ad) {
            $ad->update(['inspection_info' => $ap->value]);
            echo "Migrated Ad {$ad->id}\n";
        }
    }
}
echo "Migration complete.\n";
