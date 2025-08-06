<?php
/*
 * Copyright © 2018-2025 RBSoft (Ravi Patel). All rights reserved.
 *
 * Author: Ravi Patel
 * Website: https://rbsoft.org/downloads/sms-gateway
 *
 * This software is licensed, not sold. Buyers are granted a limited, non-transferable license
 * to use this software exclusively on a single domain, subdomain, or computer. Usage on
 * multiple domains, subdomains, or computers requires the purchase of additional licenses.
 *
 * Redistribution, resale, sublicensing, or sharing of the source code, in whole or in part,
 * is strictly prohibited. Modification (except for personal use by the licensee), reverse engineering,
 * or creating derivative works based on this software is strictly prohibited.
 *
 * Unauthorized use, reproduction, or distribution of this software may result in severe civil
 * and criminal penalties and will be prosecuted to the fullest extent of the law.
 *
 * For licensing inquiries or support, please visit https://support.rbsoft.org.
 */

/**
 * @var string $start_date
 * @var string $end_date
 */

if (count(get_included_files()) == 1) {
    http_response_code(403);
    die("HTTP Error 403 - Forbidden");
}

$message = Message::orderBy("Message.sentDate", "DESC")
    ->orderBy("Message.userID", "ASC")
    ->orderBy("Message.deviceID", "ASC");

if (empty($_REQUEST["status"]) || $_REQUEST["status"] != "Scheduled") {
    if (isset($start_date)) {
        $message->where("Message.sentDate", getDatabaseTime($start_date . " 00:00:00")->format('Y-m-d H:i:s'), ">=");
    }
    if (isset($end_date)) {
        $message->where("Message.sentDate", getDatabaseTime($end_date . " 23:59:59")->format('Y-m-d H:i:s'), "<=");
    }
} else {
    if (isset($start_date)) {
        $message->where("Message.schedule", getDisplayTime($start_date . " 00:00:00")->getTimestamp(), ">=");
    }
    if ($end_date) {
        $message->where("Message.schedule", getDisplayTime($end_date . " 23:59:59")->getTimestamp(), "<=");
    }
}

if ($_SESSION["isAdmin"]) {
    if (isset($_REQUEST["user"]) && ctype_digit($_REQUEST["user"]))
        $message->where("Message.userID", $_REQUEST["user"]);
} else {
    $message->where("Message.userID", $_SESSION["userID"]);
}

if (isset($_REQUEST["device"])) {
    if (empty($_REQUEST["device"])) {
        $message->where("Message.deviceID", null, "IS");
    } else if (ctype_digit($_REQUEST["device"])) {
        $message->where("Message.deviceID", $_REQUEST["device"]);
    }
}
if (!empty($_REQUEST["status"]))
    $message->where("Message.status", $_REQUEST["status"]);
if (!empty($_REQUEST["type"]))
    $message->where("Message.type", $_REQUEST["type"]);
if (!empty($_REQUEST["mobileNumber"]))
    $message->where("Message.number", "%{$_REQUEST['mobileNumber']}%", "LIKE");
if (isset($_REQUEST["message"])) {
    $messageText = trim($_REQUEST["message"]);
    if ($messageText) {
        $message->where("Message.message", "%{$messageText}%", "LIKE");
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$pageNo = 1;
if (!empty($_REQUEST["pageLimit"]) && ctype_digit($_REQUEST["pageLimit"])) {
    Message::setPageLimit($_REQUEST["pageLimit"]);
}
if (!empty($_REQUEST["page"]) && ctype_digit($_REQUEST["page"])) {
    $pageNo = $_REQUEST["page"];
}
/** @var Message[] $messages */
$messages = Message::read_all(false, $pageNo);