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
 * @var User $logged_in_user
 */

if (count(get_included_files()) == 1) {
    http_response_code(403);
    die("HTTP Error 403 - Forbidden");
}

$data = [];
if ($_SESSION["isAdmin"]) {
    $users = array();
    foreach (User::read_all() as $user) {
        $data[$user->getID()][null] = __("unknown_device");
        $users[$user->getID()] = $user;
    }
    $deviceUsers = DeviceUser::read_all();
} else {
    $data[$_SESSION["userID"]][null] = __("unknown_device");
    $deviceUsers = $logged_in_user->getDevices(false);
}

foreach ($deviceUsers as $deviceUser) {
    $data[$deviceUser->getUserID()][$deviceUser->getDeviceID()] = htmlentities(strval($deviceUser), ENT_QUOTES);
}