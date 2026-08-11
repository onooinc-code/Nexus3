<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Usage Logs Count: '.DB::table('usage_logs')->count()."\n";
echo 'Audit Trails Count: '.DB::table('ai_audit_trails')->count()."\n";

print_r(DB::table('usage_logs')->get()->toArray());
print_r(DB::table('ai_audit_trails')->get()->toArray());
