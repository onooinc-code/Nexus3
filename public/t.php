<?php
$target = isset($_GET['u']) ? $_GET['u'] : 'https://google.com';
if (! filter_var($target, FILTER_VALIDATE_URL)) {
    $target = 'https://google.com';
}
$click_id = uniqid('clk_', true);
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'Unknown';
$time = date('Y-m-d H:i:s');

$initial_log = [
    'id' => $click_id,
    'time' => $time,
    'ip' => $ip,
    'country' => $country,
    'ua' => $ua,
    'target' => $target,
    'js_captured' => false,
];

$file = __DIR__.'/../storage/logs/tracker.json';
$logs = [];
if (file_exists($file)) {
    $content = file_get_contents($file);
    $logs = json_decode($content, true) ?? [];
}
array_unshift($logs, $initial_log);
if (count($logs) > 200) {
    $logs = array_slice($logs, 0, 200);
}
file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redirecting...</title>
<style>
body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #0f172a; color: #f8fafc; }
.spinner { border: 3px solid rgba(255,255,255,0.1); border-top: 3px solid #38bdf8; border-radius: 50%; width: 30px; height: 30px; animation: spin 0.8s linear infinite; margin-bottom: 15px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.card { text-align: center; padding: 20px; }
</style>
</head>
<body>
<div class="card">
<div class="spinner"></div>
<p style="font-size:14px; color:#94a3b8;">Loading link...</p>
</div>
<script>
const clickId = "<?php echo $click_id; ?>";
const targetUrl = "<?php echo addslashes($target); ?>";

async function collectAndRedirect() {
    let payload = {
        id: clickId,
        screen: `${window.screen.width}x${window.screen.height}`,
        language: navigator.language,
        connection_type: 'unknown',
        effective_type: 'unknown',
        battery_level: 'unknown',
        charging: 'unknown'
    };

    if (navigator.connection) {
        payload.connection_type = navigator.connection.type || 'unknown';
        payload.effective_type = navigator.connection.effectiveType || 'unknown';
    }

    if (navigator.getBattery) {
        try {
            const batt = await navigator.getBattery();
            payload.battery_level = Math.round(batt.level * 100) + '%';
            payload.charging = batt.charging ? 'Yes' : 'No';
        } catch(e) {}
    }

    try {
        await fetch('/log_beacon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            keepalive: true
        });
    } catch(e) {}

    window.location.replace(targetUrl);
}

setTimeout(collectAndRedirect, 100);
</script>
</body>
</html>
