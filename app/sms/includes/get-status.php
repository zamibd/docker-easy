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

if (count(get_included_files()) == 1) {
    http_response_code(403);
    die("HTTP Error 403 - Forbidden");
}

function appendWhere(string $query ,array $where): string
{
    for ($i = 0; $i < count($where); $i++) {
        if ($i) {
            $query .= " AND ";
        } else {
            $query .= " WHERE ";
        }
        $query .= $where[$i];
    }
    return $query;
}

$query = "SELECT COUNT(IF(Message.status = 'Sent', 1, NULL)) as totalSent, COUNT(IF(Message.status = 'Scheduled', 1, NULL)) as totalScheduled, COUNT(IF(Message.status = 'Delivered', 1, NULL)) as totalDelivered, COUNT(IF(Message.status = 'Failed', 1, NULL)) as totalFailed, COUNT(IF(Message.status = 'Pending', 1, NULL)) as totalPending, COUNT(IF(Message.status = 'Queued', 1, NULL)) as totalQueued, COUNT(IF(Message.status = 'Canceled', 1, NULL)) as totalCanceled, COUNT(IF(Message.status = 'Received', 1, NULL)) as totalReceived FROM Message";

$where = [];

if (!empty($_GET["interval"]) && ctype_digit($_GET["interval"])) {
    $start_date = getDataBaseTime(date("Y-m-d", time() - 86400 * $_GET["interval"]) . "  00:00:00")->format("Y-m-d H:i:s");
    $end_date = getDataBaseTime(date("Y-m-d", time()) . "  23:59:59")->format("Y-m-d H:i:s");
    array_push($where, "Message.sentDate >= '{$start_date}' AND Message.sentDate <= '{$end_date}'");
}

if (!$_SESSION["isAdmin"]) {
    array_push($where, "Message.userID = {$_SESSION["userID"]}");
}
/*
if (isset($_COOKIE["DEVICE_ID"])) {
    array_push($where, "Message.deviceID = {$_COOKIE["DEVICE_ID"]}");
}
*/

$query = appendWhere($query, $where);

$counts = MysqliDb::getInstance()->rawQueryOne($query);
$pending = $counts["totalPending"];
$scheduled = $counts["totalScheduled"];
$queued = $counts["totalQueued"];
$sent = $counts["totalSent"];
$failed = $counts["totalFailed"];
$received = $counts["totalReceived"];
$delivered = $counts["totalDelivered"];
$canceled = $counts["totalCanceled"];

$ussdQuery = "SELECT COUNT(IF(Ussd.responseDate IS NULL, 1, NULL)) as totalPending, COUNT(IF(Ussd.responseDate IS NOT NULL, 1, NULL)) as totalSent FROM Ussd";
$where = [];
if (isset($start_date) && isset($end_date)) {
    $where[] = "Ussd.sentDate >= '{$start_date}' AND Ussd.sentDate <= '{$end_date}'";
}
if (!$_SESSION["isAdmin"]) {
    $where[] = "Ussd.userID = {$_SESSION["userID"]}";
}
/*
if (isset($_COOKIE["DEVICE_ID"])) {
    $where[] = "Ussd.deviceID = {$_COOKIE["DEVICE_ID"]}";
}
*/

$ussdQuery = appendWhere($ussdQuery, $where);

$ussdCounts = MysqliDb::getInstance()->rawQueryOne($ussdQuery);
$pendingUssd = $ussdCounts["totalPending"];
$sentUssd = $ussdCounts["totalSent"];