<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Services\AI\SkillDiscoveryService;
use Illuminate\Contracts\Console\Kernel;

try {
    $service = app(SkillDiscoveryService::class);
    echo "Service resolved successfully.\n";
    $result = $service->findRelevantSkills('clean input data', 1);
    echo 'Result from RAGFlow: '.json_encode($result)."\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
