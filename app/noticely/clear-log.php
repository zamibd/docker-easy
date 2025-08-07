<?php
// === CONFIGURATION ===
define('LOG_FILE', 'webhook_log.txt');
define('ACCESS_PASSWORD', 'zami123'); // Same password as view-log.php

// === AUTHENTICATION CHECK ===
if (!isset($_GET['key']) || $_GET['key'] !== ACCESS_PASSWORD) {
    http_response_code(403);
    exit("❌ Access Denied.");
}

// === CLEAR THE LOG FILE ===
if (file_exists(LOG_FILE)) {
    file_put_contents(LOG_FILE, '');
}

// === REDIRECT BACK ===
header("Location: view-log.php?key=" . ACCESS_PASSWORD);
exit;
