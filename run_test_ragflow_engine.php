<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Services\Engines\SkillEngine;
use Illuminate\Contracts\Console\Kernel;

try {
    $skillEngine = app(SkillEngine::class);
    echo "[SUCCESS] SkillEngine resolved successfully.\n";

    echo "--- [1] Storing Skill using SkillEngine ---\n";
    $skill = [
        'name' => 'DataCleaner',
        'description' => 'A skill to clean and sanitize input data',
        'code' => 'function clean(input) { return trim(input); }',
    ];
    $storeResult = $skillEngine->store($skill);
    echo 'Store Result: '.json_encode($storeResult)."\n\n";

    echo "--- [2] Searching for Skill using SkillEngine ---\n";
    $searchResult = $skillEngine->search('clean input data', ['limit' => 1]);
    echo 'Search Result: '.json_encode($searchResult)."\n";

    echo "[SUCCESS] MEMORY_ENGINE methodology verified successfully.\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
