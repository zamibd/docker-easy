<?php
// === CONFIGURATION ===
define('LOG_FILE', 'webhook_log.txt');
define('MAX_LINES', 500); // Limit the number of lines shown
define('ACCESS_PASSWORD', 'zami123'); // Change this to your desired password

// === AUTHENTICATION CHECK ===
if (!isset($_GET['key']) || $_GET['key'] !== ACCESS_PASSWORD) {
    http_response_code(403);
    exit("❌ Access Denied.");
}

// === READ LOG FILE ===
$logContent = '';
if (file_exists(LOG_FILE)) {
    $lines = file(LOG_FILE);
    $totalLines = count($lines);
    $logContent = implode("", array_slice($lines, max(0, $totalLines - MAX_LINES)));
} else {
    $logContent = "⚠️ Log file not found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Webhook Log Viewer</title>
    <style>
        body {
            background: #121212;
            color: #f1f1f1;
            font-family: Consolas, monospace;
            padding: 20px;
        }
        h1 {
            color: #00ffcc;
        }
        pre {
            background: #1e1e1e;
            border: 1px solid #333;
            padding: 15px;
            overflow-x: auto;
            max-height: 80vh;
        }
        .actions {
            margin-bottom: 15px;
        }
        .button {
            background: #00b894;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            margin-right: 10px;
            border-radius: 4px;
        }
        .button:hover {
            background: #00cec9;
        }
    </style>
</head>
<body>

    <h1>📄 Webhook Log Viewer</h1>

    <div class="actions">
        <a href="view-log.php?key=<?php echo ACCESS_PASSWORD; ?>" class="button">🔄 Refresh</a>
        <a href="clear-log.php?key=<?php echo ACCESS_PASSWORD; ?>" class="button" onclick="return confirm('Are you sure to clear the log?')">🗑 Clear Log</a>
    </div>

    <pre><?php echo htmlspecialchars($logContent); ?></pre>

</body>
</html>
