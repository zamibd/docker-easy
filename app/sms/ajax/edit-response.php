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

try {
    require_once __DIR__ . "/../includes/ajax_protect.php";
    require_once __DIR__ . "/../includes/login.php";

    if (!empty($_POST["responseID"]) && isset($_POST["message"]) && trim($_POST["message"]) !== "" && isset($_POST["response"]) && trim($_POST["response"]) !== "") {
        $response = new Response();
        $response->setID($_POST["responseID"]);
        $response->setUserID($_SESSION["userID"]);
        if ($response->read()) {
            $response->setMessage(trim($_POST["message"]));
            $response->setResponse($_POST["response"]);
            $response->setMatchType($_POST["matchType"]);
            $response->setEnabled($_POST["enabled"]);
            $response->save();
        }

        echo json_encode([
            "result" => __("success_update_response")
        ]);
    } else {
        throw new Exception(__("error_missing_fields"));
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
