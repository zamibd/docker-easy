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

    if (empty($_POST["name"])) {
        throw new Exception(__("error_name_empty"));
    } else {
        $logged_in_user->setName($_POST["name"]);
        $logged_in_user->setDelay($_POST["delay"]);
        $logged_in_user->setUssdDelay($_POST["ussdDelay"]);
        $logged_in_user->setReportDelivery(isset($_POST["reportDelivery"]) ? 1 :0);
        $logged_in_user->setUseProgressiveQueue(isset($_POST["useProgressiveQueue"]) ? 1 :0);
        $logged_in_user->setAutoRetry(isset($_POST["autoRetry"]) ? 1 : 0);
        if (Setting::get("sms_to_email_enabled")) {
            $logged_in_user->setSmsToEmail(isset($_POST["smsToEmail"]) ? 1 : 0);
            if (isset($_POST["receivedSmsEmail"])) {
                $logged_in_user->setReceivedSmsEmail($_POST["receivedSmsEmail"]);
            }
        } else {
            $logged_in_user->setSmsToEmail(0);
            $logged_in_user->setReceivedSmsEmail(null);
        }
        $logged_in_user->setTimeZone($_POST["timezone"]);
        if (isset($_POST["language"]) && file_exists(__DIR__ . "/../resources/lang/{$_POST["language"]}.php")) {
            $logged_in_user->setLanguage($_POST["language"]);
        }
        if (isset($_POST["sleepTimeFrom"]) && isset($_POST["sleepTimeTo"])) {
            if ($_POST["sleepTimeFrom"] !== $_POST["sleepTimeTo"]) {
                $logged_in_user->setSleepTime("{$_POST["sleepTimeFrom"]}-{$_POST["sleepTimeTo"]}");
            } else {
                throw new Exception(__("error_invalid_sleep_time"));
            }
        } else {
            $logged_in_user->setSleepTime(null);
        }
        $logged_in_user->save(false);
        $_SESSION["name"] = $_POST["name"];
        $_SESSION["timeZone"] = $logged_in_user->getTimeZone();
        echo json_encode([
            "result" => __("success_settings_changed")
        ]);
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}

