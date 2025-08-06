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

/**
 * @param $user
 * @return Message[]
 * @throws Exception
 */
function getMessages($user): array
{
    if ($user->getIsAdmin()) {
        $messages = new Message();
    } else {
        $messages = Message::where("userID", $user->getID());
    }
    if (isset($_REQUEST["groupId"])) {
        $messages = $messages->where("groupId", $_REQUEST["groupId"]);
    }
    if (isset($_REQUEST["id"])) {
        $messages = $messages->where("ID", $_REQUEST["id"]);
    }
    if (isset($_REQUEST["status"])) {
        $messages = $messages->where("status", $_REQUEST["status"]);
    }
    if (isset($_REQUEST["deviceID"])) {
        $messages = $messages->where("deviceID", $_REQUEST["deviceID"]);
    }
    if (isset($_REQUEST["simSlot"])) {
        $messages = $messages->where("simSlot", $_REQUEST["simSlot"]);
    }
    if (isset($_REQUEST["startTimestamp"])) {
        $messages = $messages->where("sentDate", date("Y-m-d H:i:s", $_REQUEST["startTimestamp"]), ">=");
    }
    if (isset($_REQUEST["endTimestamp"])) {
        $messages = $messages->where("sentDate", date("Y-m-d H:i:s", $_REQUEST["endTimestamp"]), "<=");
    }
    return $messages->read_all();
}