<?php
// Telegram Bot credentials
$botToken = "8414150895:AAHWGNtMZWU6ywuwrUtirbWg2HA3PvQX370";
$chatId   = "1016866360";

// Read raw POST input (JSON body)
$rawData = file_get_contents("php://input");

// Decode the JSON data to PHP array
$decodedData = json_decode($rawData, true);

// Format the message for Telegram
$message = "🔔 *Webhook Triggered!*

";

if ($decodedData && is_array($decodedData)) {
    foreach ($decodedData as $key => $value) {
        // Convert nested arrays to string
        if (is_array($value)) {
            $value = print_r($value, true);
        }
        $message .= "*$key*: `$value`
";
    }
} else {
    $message .= "_No valid JSON data received._";
}

// Prepare Telegram API request
$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
$payload = [
    'chat_id'    => $chatId,
    'text'       => $message,
    'parse_mode' => 'Markdown'
];

// Send the message using CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegramApiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$telegramResponse = curl_exec($ch);
curl_close($ch);

// Log webhook data locally (for debug/audit)
$logEntry = "[" . date("Y-m-d H:i:s") . "]\n" . $message . "\n\n";
file_put_contents("webhook_log.txt", $logEntry, FILE_APPEND);

// Optional: respond to the webhook sender
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'telegram' => json_decode($telegramResponse, true)]);
