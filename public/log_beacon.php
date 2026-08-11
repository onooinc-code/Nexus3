<?php

header('Content-Type: application/json');
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (! $data || ! isset($data['id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$file = __DIR__.'/../storage/logs/tracker.json';
if (! file_exists($file)) {
    echo json_encode(['status' => 'no_log_file']);
    exit;
}

$content = file_get_contents($file);
$logs = json_decode($content, true) ?? [];

foreach ($logs as &$entry) {
    if ($entry['id'] === $data['id']) {
        $entry['js_captured'] = true;
        $entry['screen'] = $data['screen'] ?? 'unknown';
        $entry['language'] = $data['language'] ?? 'unknown';
        $entry['connection_type'] = $data['connection_type'] ?? 'unknown';
        $entry['effective_type'] = $data['effective_type'] ?? 'unknown';
        $entry['battery_level'] = $data['battery_level'] ?? 'unknown';
        $entry['charging'] = $data['charging'] ?? 'unknown';
        break;
    }
}

file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['status' => 'ok']);
