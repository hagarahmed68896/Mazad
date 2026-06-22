<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FCM Configuration Check ===" . PHP_EOL;

$projectId = config('services.firebase.project_id');
$credsPath  = config('services.firebase.service_account_path');

echo "PROJECT_ID   : " . ($projectId ?: 'NOT SET') . PHP_EOL;
echo "CREDS PATH   : " . $credsPath . PHP_EOL;
echo "CREDS EXISTS : " . (file_exists($credsPath) ? 'YES ✅' : 'NO ❌') . PHP_EOL;

// Check content of creds file
if (file_exists($credsPath)) {
    $creds = json_decode(file_get_contents($credsPath), true);
    echo "HAS client_email : " . (isset($creds['client_email']) ? 'YES ✅ (' . $creds['client_email'] . ')' : 'NO ❌') . PHP_EOL;
    echo "HAS private_key  : " . (isset($creds['private_key']) ? 'YES ✅' : 'NO ❌') . PHP_EOL;
    echo "HAS project_id   : " . ($creds['project_id'] ?? 'MISSING') . PHP_EOL;
}

echo PHP_EOL . "=== FCM Send Test ===" . PHP_EOL;
// Try to get access token only (no real device token needed)
try {
    // Test by sending to a clearly invalid token, which should fail at FCM level but means auth worked
    $result = \App\Services\FcmService::sendPush('invalid_test_token_12345', 'اختبار FCM', 'هذا اختبار من السيرفر');
    echo "sendPush() returned: " . ($result ? 'true (unexpected success)' : 'false') . PHP_EOL;

    // Check last log entry
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $last20 = array_slice($lines, -20);
        $fcmLines = array_filter($last20, fn($l) => stripos($l, 'fcm') !== false);
        echo PHP_EOL . "=== Recent FCM Log Entries ===" . PHP_EOL;
        foreach ($fcmLines as $line) {
            echo trim($line) . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}
