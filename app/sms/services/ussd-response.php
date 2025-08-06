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

date_default_timezone_set(TIMEZONE);
set_time_limit(20);

try {
    if (isset($_POST["androidId"]) && isset($_POST["userId"]) && isset($_POST["ussdId"]) && isset($_POST["response"])) {
        $device = new Device();
        $device->setAndroidID($_POST["androidId"]);
        $device->setUserID($_POST["userId"]);
        if ($device->read()) {
            $ussd = new Ussd();
            $ussd->setID($_POST["ussdId"]);
            $ussd->setUserID($_POST["userId"]);
            $ussd->setDeviceID($device->getID());
            if ($ussd->read(false)) {
                $ussd->setResponse($_POST["response"]);
                $ussd->setResponseDate(date("Y-m-d H:i:s"));
                $ussd->save();

                $device->getUser()->callWebhook('ussdRequest', $ussd);
                echo json_encode(["success" => true, "data" => null, "error" => null]);
            }
        } else {
            echo json_encode(["success" => false, "data" => null, "error" => ["code" => 401, "message" => __("error_device_not_found")]]);
        }
    } else {
        throw new Exception(__("error_invalid_request_format"));
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "data" => null, "error" => ["code" => 500, "message" => $e->getMessage()]]);
}
