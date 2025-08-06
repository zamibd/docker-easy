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

try {
    require_once __DIR__ . "/includes/login.php";

    $start_date = empty($_REQUEST["startDate"]) ? null : $_REQUEST["startDate"];
    $end_date = empty($_REQUEST["endDate"]) ? null : $_REQUEST["endDate"];
    require_once __DIR__ . "/includes/search.php";

    /** @var array<int, Message> $messages */
    if (count($messages) > 0) {
        if (isset($start_date)) {
            if (isset($end_date)) {
                $name = "Messages_{$start_date}_{$end_date}.csv";
            } else {
                $now = (new DateTime())->format('Y-m-d');
                $name = "Messages_{$start_date}_{$now}.csv";
            }
        } else {
            $name = "Messages.csv";
        }
        objectsToExcel($messages, $name, ["number" => __("mobile_number"), "message" => __("message"), "status" => __("status"), "sentDate" => __("sent_date"), "deliveredDate" => __("delivered_date")], array("userID", "deviceID", "ID", "groupID", "resultCode", "errorCode", "retries", "expiryDate"));
    } else {
        header("location:messages.php?" . $_SERVER['QUERY_STRING']);
    }
} catch (Exception $e) {
    echo json_encode(array(
        "error" => $e->getMessage()
    ));
}

