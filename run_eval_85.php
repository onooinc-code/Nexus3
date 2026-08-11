<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$task = App\Models\AgentTask::find(85);
if ($task) {
    // Reset status to pending so evaluateStep can run cleanly if it failed
    if ($task->status === 'failed') {
        $task->update(['status' => 'pending', 'result_data' => null]);
    }

    $proofs = $task->execution_proof ?? [];
    $obs = $proofs[1]['observation_received'] ?? [
        'status' => 'SUCCESS',
        'result' => [
            'snapshotText' => "[2] a: "موقع اليوم السابع"\n[4] a: "خبر عاجل: السيسي يوجه بتطوير الخدمات الصحية"",
            'interactiveMap' => [
                ['id' => 2, 'tag' => 'a', 'text' => 'موقع اليوم السابع', 'href' => 'https://www.youm7.com/home/index'],
                ['id' => 4, 'tag' => 'a', 'text' => 'خبر عاجل: السيسي يوجه بتطوير الخدمات الصحية', 'href' => 'https://www.youm7.com/story/123']
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
        'execution_proof' => $task->execution_proof,
        'result_data' => $task->result_data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
