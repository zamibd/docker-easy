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

require_once __DIR__ . "/../includes/session.php";

if (isset($_POST["androidId"]) && isset($_POST["userId"])) {
    try {
        $device = new Device();
        $device->setAndroidID($_POST["androidId"]);
        $device->setUserID($_POST["userId"]);
        $device->setEnabled(1);
        if ($device->read()) {
            if (isset($_POST["sims"])) {
                $device->saveSims(json_decode($_POST["sims"]));
            }
            $device->getUser()->setLastLogin(date('Y-m-d H:i:s'));
            $device->getUser()->setLastLoginIP(getUserIpAddress());
            $device->getUser()->save();
            if (isset($_POST["androidVersion"]) && isset($_POST["appVersion"])) {
                $device->setAndroidVersion($_POST["androidVersion"]);
                $device->setAppVersion($_POST["appVersion"]);
                $device->save();
            }
            $_SESSION["userID"] = $device->getUserID();
            $_SESSION["email"] = $device->getUser()->getEmail();
            $_SESSION["name"] = $device->getUser()->getName();
            $_SESSION["isAdmin"] = $device->getUser()->getIsAdmin();
            $_SESSION["timeZone"] = $device->getUser()->getTimeZone();
            session_commit();
            $response =
                [
                    "success" => true,
                    "data" => [
                        "sessionId" => get_cookie(APP_SESSION_NAME),
                        "device" => $device,
                    ],
                    "error" => null
                ];
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