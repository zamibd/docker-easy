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
    require_once __DIR__ . "/../includes/session.php";

    if (!empty($_POST["email"])) {
        $users = User::where("email", $_POST["email"])->read_all();
        if (count($users) <= 0) {
            throw new Exception(__("error_email_not_exist"));
        } else {
            $user = $users[0];
            $admin = User::getAdmin();
            $from = array($admin->getEmail(), $admin->getName());
            $to = array($user->getEmail(), $user->getName());
            $subject = __("reset_password_link_email_subject", ["app" => __("application_title")]);
            $serverURL = getServerURL();
            $code = urlencode(encrypt(time(), $user->getPassword(), $user->getDateAdded()->format(DATE_RFC850)));
            $email = urlencode($user->getEmail());
            $body = __("reset_password_link_email_body", ["app" => __("application_title"), "user" => htmlentities($user->getName(), ENT_QUOTES), "userEmail" => $email, "admin" => htmlentities($admin->getName(), ENT_QUOTES), "adminEmail" => $admin->getEmail(), "code" => $code, "server" => $serverURL]);
            try {
                sendEmail($from, $to, $subject, $body);
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                throw new Exception(__("error_send_email_reset_password") . " {$e->errorMessage()}");
            }
            echo json_encode([
                'result' => __("success_password_reset_link")
            ]);
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
