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
 * @var string $currentLanguage
 */

try {
    require_once __DIR__ . "/../includes/ajax_protect.php";
    require_once __DIR__ . "/../includes/session.php";

    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $user = User::login($_POST["email"], $_POST["password"]);
        if ($user) {
            $user->setLastLogin(date('Y-m-d H:i:s'));
            $user->setLastLoginIP(getUserIpAddress());
            $user->setLanguage($currentLanguage);
            $user->save();
            $_SESSION["userID"] = $user->getID();
            $_SESSION["email"] = $user->getEmail();
            $_SESSION["name"] = $user->getName();
            $_SESSION["isAdmin"] = $user->getisAdmin();
            $_SESSION["timeZone"] = $user->getTimeZone();
            if ($user->devicesLimit > 0) {
                $totalDevices = Device::where('userID', $user->getID())->count();
                if ($totalDevices <= 0) {
                    $_SESSION["showTutorial"] = true;
                }
            }

            echo json_encode([
                "result" => true
            ]);
        } else {
            throw new Exception(__("error_incorrect_credentials"));
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}