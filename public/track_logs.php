<?php
$key = $_GET['key'] ?? '';
if ($key !== 'souly2026') {
    http_response_code(403);
    exit('Unauthorized access');
}

$file = __DIR__.'/../storage/logs/tracker.json';
$logs = [];
if (file_exists($file)) {
    $logs = json_decode(file_get_contents($file), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Souly Link Tracker Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; padding: 25px; }
.card { background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; margin-bottom: 15px; }
.badge-wifi { background-color: #0284c7; color: white; }
.badge-cellular { background-color: #16a34a; color: white; }
.badge-unknown { background-color: #64748b; color: white; }
.ip-code { font-family: monospace; color: #38bdf8; font-weight: bold; }
</style>
</head>
<body>
<div class="container-fluid max-w-1200">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🎯 Souly Tracker Dashboard</h2>
    <span class="badge bg-primary fs-6">Total Clicks: <?php echo count($logs); ?></span>
</div>

<div class="card p-3 mb-4">
    <h5>🔗 How to use:</h5>
    <p class="mb-1 text-slate-300">Send any link using this format:</p>
    <code>https://n.soulyeg.online/t.php?u=DESTINATION_URL</code>
    <div class="mt-2 text-muted small">Example: <code>https://n.soulyeg.online/t.php?u=https://instagram.com/reel/Cxxxx</code></div>
</div>

<?php if (empty($logs)) { ?>
    <div class="alert alert-info">No click logs recorded yet.</div>
<?php } else { ?>
    <div class="row">
    <?php foreach ($logs as $log) { ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card p-3">
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="fw-bold text-info"><?php echo htmlspecialchars($log['time']); ?></span>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($log['country']); ?></span>
                </div>
                <div><strong>IP:</strong> <span class="ip-code"><?php echo htmlspecialchars($log['ip']); ?></span></div>
                <div><strong>Effective Connection:</strong> 
                    <span class="badge <?php echo ($log['effective_type'] ?? '') === '4g' ? 'badge-cellular' : 'badge-wifi'; ?>">
                        <?php echo htmlspecialchars(strtoupper($log['effective_type'] ?? 'Unknown')); ?>
                    </span>
                </div>
                <div><strong>Battery:</strong> <?php echo htmlspecialchars($log['battery_level'] ?? 'N/A'); ?> (Charging: <?php echo htmlspecialchars($log['charging'] ?? 'N/A'); ?>)</div>
                <div><strong>Screen:</strong> <?php echo htmlspecialchars($log['screen'] ?? 'N/A'); ?></div>
                <div class="text-truncate text-muted small mt-2"><strong>Target:</strong> <?php echo htmlspecialchars($log['target']); ?></div>
                <div class="text-truncate text-muted small" title="<?php echo htmlspecialchars($log['ua']); ?>"><strong>UA:</strong> <?php echo htmlspecialchars($log['ua']); ?></div>
            </div>
        </div>
    <?php } ?>
    </div>
<?php } ?>

</div>
</body>
</html>
