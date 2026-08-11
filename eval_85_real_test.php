<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$task = App\Models\AgentTask::find(85);
if ($task) {
    $proofs = $task->execution_proof ?? [];
    $obs = [
        'status' => 'SUCCESS',
        'result' => [
            'snapshotText' => "[2] a: "موقع اليوم السابع"\n[4] a: "خبر عاجل: الرئيس يوجه بتطوير القطاع الصحي"",
            'interactiveMap' => [
                ['id' => 2, 'tag' => 'a', 'text' => 'خبر عاجل: الرئيس يوجه بتطوير القطاع الصحي', 'href' => 'https://www.youm7.com/story/1234']
            ]
        ]
    ];
    
    $engine = new App\Services\ReActAgentEngine();
    $engine->evaluateStep($task, $obs);
    $task->refresh();
    
    echo json_encode([
        'id' => $task->id,
        'status' => $task->status,
        'plan' => $task->plan,
        'proofs' => $task->execution_proof,
        'result_data' => $task->result_data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
