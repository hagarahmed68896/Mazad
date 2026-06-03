<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tblKey = "Tables_in_" . $dbName;

foreach ($tables as $tableInfo) {
    $tableName = $tableInfo->$tblKey;
    
    // Skip some system tables to be fast
    if (in_array($tableName, ['migrations', 'personal_access_tokens', 'failed_jobs', 'password_reset_tokens'])) {
        continue;
    }
    
    try {
        $columns = Schema::getColumnListing($tableName);
        foreach ($columns as $column) {
            $results = DB::table($tableName)
                ->where($column, 'like', '%2pm%')
                ->orWhere($column, 'like', '%10pm%')
                ->orWhere($column, 'like', '%2pm to 10pm%')
                ->get();
                
            if ($results->count() > 0) {
                echo "Table: $tableName | Column: $column\n";
                print_r($results->toArray());
            }
        }
    } catch (\Exception $e) {
        // ignore errors
    }
}
