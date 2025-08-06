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

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../vendor/autoload.php";

if (isset($_POST["androidId"]) && isset($_POST["userId"])) {
    try {
        $device = new Device();
        $device->setAndroidID($_POST["androidId"]);
        $device->setUserID($_POST["userId"]);
        if ($device->read()) {
            $device->setToken($_POST["token"] ?? null);
            $device->setEnabled(1);
            $device->save(false);
            $response = ["success" => true, "data" => ["device" => $device], "error" => null];
            echo json_encode($response);
            die;
        } else {
            $errorCode = 401;
            $error = __("error_device_not_found");
        }
    } catch (Throwable $t) {
        $errorCode = 500;
        $error = $t->getMessage();
    }
    $response = ["success" => false, "data" => null, "error" => ["code" => $errorCode, "message" => $error]];
    echo json_encode($response);
}