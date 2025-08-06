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

header('Cache-Control: no cache');
require_once __DIR__ . "/session.php";

/*
 * @link https://stackoverflow.com/a/1270960/1273550
 */
if (isset($_SESSION["userID"])) {
    $_SESSION['LAST_ACTIVITY'] = time();
    if (isset($_COOKIE["DEVICE_ID"])) {
        $currentDevice = Device::getById($_COOKIE["DEVICE_ID"], $_SESSION["userID"]);
        if ($currentDevice && $currentDevice->getEnabled()) {
            $logged_in_user = $currentDevice->getUser();
        }
    } else {
        $logged_in_user = User::getById($_SESSION["userID"]);
    }

    if (empty($logged_in_user)) {
        require_once __DIR__ . "/../logout.php";
        exit();
    } else {
        $_SESSION['timeZone'] = $logged_in_user->getTimeZone();
        $_SESSION['name'] = $logged_in_user->getName();
        require_once __DIR__ . "/set-language.php";
    }
} else {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(array(
            'redirect' => "index.php"
        ));
        exit();
    }
    header("location:index.php");
    exit();
}