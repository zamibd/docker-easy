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

try {
    require_once __DIR__ . "/../includes/get-user.php";

    if (!empty($_REQUEST["request"]) && !empty($_REQUEST["device"]) && isset($user)) {
        if ($user) {
            try {
                $simSlot = null;
                if (isset($_REQUEST["sim"]) && ctype_digit($_REQUEST["sim"])) {
                    $simSlot = $_REQUEST["sim"];
                }

                $ussdRequest = DeviceUser::initiateUssdRequest($_REQUEST["request"], $user->getID(), $_REQUEST["device"], $simSlot);
                echo json_encode(["success" => true, "data" => ["request" => $ussdRequest], "error" => null]);
            } catch (InvalidArgumentException $e) {
                echo json_encode(["success" => false, "data" => null, "error" => ["code" => 404, "message" => $e->getMessage()]]);
            }
        } else {
            echo json_encode(["success" => false, "data" => null, "error" => ["code" => 401, "message" => isset($_REQUEST["key"]) ? __("error_incorrect_api_key") : __("error_incorrect_credentials")]]);
        }
    } else {
        echo json_encode(["success" => false, "data" => null, "error" => ["code" => 400, "message" => __("error_invalid_request_format")]]);
    }
} catch (Throwable $t) {
    echo json_encode(["success" => false, "data" => null, "error" => ["code" => 500, "message" => $t->getMessage()]]);
}