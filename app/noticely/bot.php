<?php
// ——— CONFIGURATION ———
// Error reporting is turned off as requested.
error_reporting(0);
ini_set('display_errors', 0);

$bot_token       = "8014915239:AAEEFWsybKFjbbunlBMn7feIkXoD6IuoBvA"; // Replace with your bot token
$api_key         = "ac571399a5ace4a976e36506e5a0d77968514576b1e31"; // Replace with your API key
$api_url         = "https://502.eflexi.xyz/api/billing/recharge-request";
$api_username    = "Razonpay";
$api_domain      = "imzami.com";
$user_state_file = __DIR__ . '/user_states.json';

// --- User Authentication: Add allowed users here ---
// Format: 'phoneNumber' => 'PIN'
$allowed_users = [
    '01920280000' => '2255', // Example user 1
    '01707514800' => '2007', // Example user 2
    // Add more users here
];

$services = [
    'recharge'=>['id'=>64,'name'=>'Mobile Recharge'],
    'bkash'   =>['id'=>128,'name'=>'bKash'],
    'nagad'   =>['id'=>8192,'name'=>'Nagad'],
    'rocket'  =>['id'=>256,'name'=>'Rocket'],
    'upay'    =>['id'=>2048,'name'=>'Upay'],
];
$types_recharge  = ['1'=>'Prepaid','2'=>'Postpaid','3'=>'Skitto'];
$types_mbanking  = ['1'=>'Personal','2'=>'Agent','3'=>'Send Money'];

// ——— HELPERS ——
function escapeMd($text) {
    $escape = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($escape as $char) $text = str_replace($char, '\\' . $char, $text);
    return $text;
}

function debugLog($msg, $file = 'bot_debug.log') {
    $path = __DIR__ . "/$file";
    error_log("[".date('Y-m-d H:i:s')."] $msg\n", 3, $path);
}

function sendMessage($chatId, $text, $keyboard = null) {
    global $bot_token;
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $payload = [
        'chat_id'    => $chatId,
        'text'       => $text,
        'parse_mode' => 'MarkdownV2'
    ];
    if ($keyboard) $payload['reply_markup'] = json_encode($keyboard, JSON_UNESCAPED_UNICODE);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => $payload,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_SSL_VERIFYPEER  => false,
    ]);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) debugLog("sendMessage CURL error: ".curl_error($ch));
    curl_close($ch);
}

function callApi($url, $data) {
    global $api_key;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => $data,
        CURLOPT_HTTPHEADER      => ["X-API-KEY: {$api_key}"],
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_SSL_VERIFYPEER  => false,
    ]);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) debugLog("callApi CURL error: ".curl_error($ch));
    curl_close($ch);
    return $resp;
}

function getUserStates() {
    global $user_state_file;
    if (!file_exists($user_state_file)) return [];
    $json = file_get_contents($user_state_file);
    return json_decode($json, true) ?: [];
}

function saveUserStates($all) {
    global $user_state_file;
    file_put_contents($user_state_file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getUserState($chatId) {
    $all = getUserStates();
    return $all[$chatId] ?? ['state'=>null, 'data'=>[], 'authenticated'=>false, 'last_activity'=>0];
}

// Updated to always set last_activity timestamp on any state change
function setUserState($chatId, $state, $data = []) {
    $all = getUserStates();
    $currentState = $all[$chatId] ?? [];
    $all[$chatId] = [
        'state' => $state,
        'data'  => $data,
        'authenticated' => $currentState['authenticated'] ?? false,
        'last_activity' => time() // Update timestamp on every interaction
    ];
    saveUserStates($all);
}

// Updated to set last_activity timestamp on login
function setAuthState($chatId, bool $isAuthed) {
    $all = getUserStates();
    $currentState = $all[$chatId] ?? ['state' => null, 'data' => []];
    $currentState['authenticated'] = $isAuthed;
    $currentState['last_activity'] = time(); // Set timestamp on login/auth change
    if ($isAuthed) {
        $currentState['state'] = 'auth_success';
    }
    $all[$chatId] = $currentState;
    saveUserStates($all);
}

function clearUserState($chatId) {
    $all = getUserStates();
    unset($all[$chatId]);
    saveUserStates($all);
}

// —— BOT ENTRY ———
$input = file_get_contents('php://input');
if (!$input) exit;

debugLog("Incoming update: ".substr($input,0,200));
$update = json_decode($input, true);

if (isset($update['message'])) {
    $chatId     = $update['message']['chat']['id'];
    $text       = trim($update['message']['text'] ?? '');
    $isCallback = false;
} elseif (isset($update['callback_query'])) {
    $chatId     = $update['callback_query']['message']['chat']['id'];
    $text       = $update['callback_query']['data'];
    $isCallback = true;
    file_get_contents("https://api.telegram.org/bot{$bot_token}/answerCallbackQuery?callback_query_id={$update['callback_query']['id']}");
} else {
    exit;
}

$stateInfo       = getUserState($chatId);
$isAuthenticated = $stateInfo['authenticated'] ?? false;
$lastActivity    = $stateInfo['last_activity'] ?? 0;

// --- AUTO-LOGOUT LOGIC ---
if ($isAuthenticated && (time() - $lastActivity > 600)) { // 600 seconds = 10 minutes
    clearUserState($chatId);
    sendMessage($chatId, "⏰ _১০ মিনিট নিষ্ক্রিয় থাকার কারণে আপনাকে স্বয়ংক্রিয়ভাবে লগ\\-আউট করা হয়েছে।_\nআবার লগইন করতে /start টাইপ করুন।");
    exit; // Stop further execution
}

$currentState    = $stateInfo['state'];
$userData        = $stateInfo['data'];

// --- AUTHENTICATION GATE ---
if (!$isAuthenticated) {
    // Unauthenticated users are handled here
    if (!$isCallback && $text === '/start') {
        $currentState = 'awaiting_number'; // Force state to start login
    }

    switch ($currentState) {
        case 'awaiting_number':
            sendMessage($chatId, "🔒 *Authentication Required*\n──────────────\n_Please enter your registered phone number:_");
            setUserState($chatId, 'awaiting_pin_entry', []);
            break;

        case 'awaiting_pin_entry':
            if (preg_match('/^01[3-9]\d{8}$/', $text)) {
                $userData['number'] = $text;
                sendMessage($chatId, "🔑 _Number received\\._\n_Please enter your PIN:_");
                setUserState($chatId, 'check_credentials', $userData);
            } else {
                sendMessage($chatId, "⚠️ _Invalid number format\\!_\nPlease provide a valid 11\\-digit Bangladeshi mobile number \\(e\\.g\\. 01xxxxxxxxx\\)\\.");
                setUserState($chatId, 'awaiting_pin_entry', []);
            }
            break;

        case 'check_credentials':
            $number = $userData['number'];
            $pin = $text;
            if (isset($allowed_users[$number]) && $allowed_users[$number] === $pin) {
                setAuthState($chatId, true);
                $msg = "✅ *Login Successful\\!*\n\n✨ *Welcome to Razonpay*\n"
                    ."──────────────\n"
                    ."Your all\\-in\\-one solution for *Mobile Recharge* and *Mobile  Banking*\\!\n\n"
                    ."🟢 _Fast, secure, 24/7 service\\._\n\n"
                    ."Please choose a service to continue:";
                sendMessage($chatId, $msg, [
                    'inline_keyboard'=>[
                        [['text'=>'📲 Mobile Recharge','callback_data'=>'svc_rech']],
                        [['text'=>'🏦 Mobile Banking','callback_data'=>'svc_bank']],
                    ]
                ]);
            } else {
                sendMessage($chatId, "❌ *Authentication Failed*\\!\n_Invalid number or PIN\\._\n\nPlease type /start to try again\\.");
                clearUserState($chatId);
            }
            break;

        default:
            sendMessage($chatId, "Welcome\\! Please type /start to log in\\.");
            clearUserState($chatId);
            break;
    }
    exit; // Stop processing for unauthenticated users
}

// --- LOGGED-IN USER LOGIC ---

// ——— COMMANDS ———
if (!$isCallback && in_array($text, ['/start','/menu'])) {
    $msg = "✨ *Welcome back to Razonpay*\n"
        ."──────────────\n"
        ."Please choose a service to continue:";
    sendMessage($chatId, $msg, [
        'inline_keyboard'=>[
            [['text'=>'📲 Mobile Recharge','callback_data'=>'svc_rech']],
            [['text'=>'🏦 Mobile Banking','callback_data'=>'svc_bank']],
        ]
    ]);
    setUserState($chatId, 'main_menu', []);
    exit;
}

if (!$isCallback && $text === '/cancel') {
    sendMessage($chatId, "🚫 _Your operation was cancelled\\._\nType /start to return to the main menu\\.");
    setUserState($chatId, 'main_menu', []);
    exit;
}

if (!$isCallback && $text === '/logout') {
    sendMessage($chatId, "🔒 _You have been logged out\\._\nType /start to log in again\\.");
    clearUserState($chatId);
    exit;
}

if ($isCallback && preg_match('/^svc_(\w+)/', $text, $m)) {
    switch ($m[1]) {
        case 'rech':
            setUserState($chatId,'rech_num',['svc'=>'recharge']);
            sendMessage($chatId,"*Mobile Recharge*\n──────────────\n _Enter your  mobile number:_");
            break;
        case 'bank':
            setUserState($chatId,'bank_svc',['svc'=>'mbanking']);
            sendMessage($chatId,"*Mobile Banking*\n─────────────\n🏦 _Select your preferred banking service:_",[
                'inline_keyboard'=>[
                    [['text'=>'বিকাশ','callback_data'=>'bank_bkash'],['text'=>'নগদ','callback_data'=>'bank_nagad']],
                    [['text'=>'রকেট','callback_data'=>'bank_rocket'],['text'=>'উপায়','callback_data'=>'bank_upay']],
                ]
            ]);
            break;
    }
    exit;
}

// —— MAIN STATE MACHINE ——
switch ($currentState) {
    case 'rech_num':
        if (preg_match('/^01[3-9]\d{8}$/',$text)) {
            $userData['number']=$text;
            sendMessage($chatId,"*Mobile Recharge*\n──────────────\n✅ _Number accepted\\!_\n\nSelect your operator:",[
                'inline_keyboard'=>[
                    [['text'=>'GP','callback_data'=>'rech_op_1'],['text'=>'Robi','callback_data'=>'rech_op_3']],
                    [['text'=>'BL','callback_data'=>'rech_op_2'],['text'=>'Airtel','callback_data'=>'rech_op_4']],
                    [['text'=>'Teletalk','callback_data'=>'rech_op_5']],
                ]
            ]);
            setUserState($chatId,'rech_op',$userData);
        } else {
            sendMessage($chatId,"️ _Invalid number!_\nPlease provide a valid 11\\-digit mobile number \\(e\\.g\\. 017xxxxxxxx\\)\\.");
        }
        break;

    case 'rech_op':
        if ($isCallback && preg_match('/^rech_op_(\d+)$/',$text,$m)) {
            $userData['operator']=$m[1];
            sendMessage($chatId,"*Mobile Recharge*\n────────────\n💸 _Please enter an amount \\(e\\.g\\., 50\\):_");
            setUserState($chatId,'rech_amt',$userData);
        }
        break;

    case 'rech_amt':
        if (is_numeric($text) && $text>0) {
            $userData['amount']=$text;
            sendMessage($chatId,"*Mobile Recharge*\n─────────────\n💡 _Connection type?_",[
                'inline_keyboard'=>[[
                    ['text'=>'Prepaid','callback_data'=>'rech_typ_1'],
                    ['text'=>'Postpaid','callback_data'=>'rech_typ_2'],
                    ['text'=>'Skitto','callback_data'=>'rech_typ_3'],
                ]]
            ]);
            setUserState($chatId,'rech_typ',$userData);
        } else {
            sendMessage($chatId,"⚠️ _Please enter a valid numeric amount \\(e\\.g\\., 50\\)_\\.");
        }
        break;

    case 'rech_typ':
        if ($isCallback && preg_match('/^rech_typ_(\d+)$/',$text,$m)) {
            $userData['type']=$m[1];
            global $types_recharge;
            $confirm = "✅ *Mobile Recharge Confirmation*\n─────────────\n"
                ." *Number:* `".escapeMd($userData['number'])."`\n"
                ." *Amount:* `".escapeMd($userData['amount'])."` BDT\n"
                ." *Type:* `".escapeMd($types_recharge[$userData['type']])."`\n\n"
                ."Are you sure you want to proceed?";
            sendMessage($chatId, $confirm, [
                'inline_keyboard'=>[
                    [['text'=>'✅ Confirm','callback_data'=>'final_ok'],['text'=>'❌ Cancel','callback_data'=>'final_no']]
                ]
            ]);
            setUserState($chatId,'rech_confirm',$userData);
        }
        break;

    case 'bank_svc':
        if ($isCallback && preg_match('/^bank_(\w+)$/',$text,$m)) {
            $key = $m[1];
            $userData['svc_key']=$key;
            global $services;
            sendMessage($chatId,"*Mobile Banking*\n──────────────\n _Enter your account or wallet number:_");
            setUserState($chatId,'bank_num',$userData);
        }
        break;

    case 'bank_num':
        if (preg_match('/^01\d{9,10}$/',$text)) {
            $userData['number']=$text;
            sendMessage($chatId,"*Mobile Banking*\n─────────────\n💸 _Enter the amount to send:_");
            setUserState($chatId,'bank_amt',$userData);
        } else {
            sendMessage($chatId,"⚠️ _Invalid account or wallet number!_\nPlease try again\\.");
        }
        break;

    case 'bank_amt':
        if (is_numeric($text) && $text>0) {
            $userData['amount']=$text;
            sendMessage($chatId,"*Mobile Banking*\n──────────────\n💡 _Account type:_",[
                'inline_keyboard'=>[[
                    ['text'=>'Personal','callback_data'=>'bank_typ_1'],
                    ['text'=>'Agent','callback_data'=>'bank_typ_2'],
                    ['text'=>'Send Money','callback_data'=>'bank_typ_3'],
                ]]
            ]);
            setUserState($chatId,'bank_typ',$userData);
        } else {
            sendMessage($chatId,"️ _Please enter a valid amount \\(e\\.g\\., 1000\\)_\\.");
        }
        break;

    case 'bank_typ':
        if ($isCallback && preg_match('/^bank_typ_(\d+)$/',$text,$m)) {
            $userData['type']=$m[1];
            global $types_mbanking, $services;
            $confirm  = "✅ *Mobile Banking Confirmation*\n─────────────\n"
                ." *Service:* `".escapeMd($services[$userData['svc_key']]['name'])."`\n"
                ." *Number:* `".escapeMd($userData['number'])."`\n"
                ." *Amount:* `".escapeMd($userData['amount'])."` BDT\n"
                ." *Type:* `".escapeMd($types_mbanking[$userData['type']])."`\n\n"
                ."Are you sure you want to proceed?";
            sendMessage($chatId, $confirm, [
                'inline_keyboard'=>[
                    [['text'=>'✅ Confirm','callback_data'=>'final_ok'],['text'=>'❌ Cancel','callback_data'=>'final_no']]
                ]
            ]);
            setUserState($chatId,'bank_confirm',$userData);
        }
        break;

    case 'rech_confirm':
    case 'bank_confirm':
        if ($isCallback) {
            if ($text === 'final_ok') {
                sendMessage($chatId,"⏳ _Please wait while we complete your request\\.\\.\\._");
                global $api_username, $api_domain, $services, $api_url;
                $post = [
                    'username'=>$api_username,
                    'domain'  =>$api_domain,
                    'your_url'=>(isset($_SERVER['SCRIPT_URI']) ? $_SERVER['SCRIPT_URI'] : ''),
                    'number'  =>$userData['number'],
                    'amount'  =>$userData['amount'],
                    'type'    =>$userData['type'],
                ];
                if ($currentState==='rech_confirm') {
                    $post['service']  = $services['recharge']['id'];
                    $post['operator'] = $userData['operator'];
                } else {
                    $post['service']  = $services[$userData['svc_key']]['id'];
                    $post['operator'] = 1;
                }
                $rsp = callApi($api_url, $post);
                $j   = json_decode($rsp, true) ?: [];
                if (!empty($j['data']['guid'])) {
                    sendMessage($chatId,
                        "✅_Your request has been completed successfully\\._\n─────────────\n"
                        ."*Transaction ID:* `{$j['data']['guid']}`\n\n"
                        ."For new transactions, type /start at any time\\."
                        ."Thank you for using our services\\."
                    );
                } else {
                    $msg = $j['error'] ?? $j['message'] ?? 'Unknown error';
                    sendMessage($chatId,"❌ *Transaction Failed!*\n────────────\n_{$msg}_\n\nType /start to try again or contact support\\.");
                }
                setUserState($chatId, 'main_menu', []);
            } else {
                sendMessage($chatId," _Operation cancelled\\._\nType /start to return to the main menu\\.");
                setUserState($chatId, 'main_menu', []);
            }
        }
        break;

    default:
        if (!$isCallback && $currentState !== 'auth_success') {
            sendMessage($chatId,"🤔 _Sorry, I didn't understand that command\\._\nType /start for the main menu\\.");
            setUserState($chatId, 'main_menu', []);
        }
        break;
}
