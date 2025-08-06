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

$currentLanguage = $defaultLanguage = PRIMARY_LANGUAGE;
if (isset($_GET["language"])) {
    $currentLanguage = $_GET["language"];
} else if (isset($logged_in_user)) {
    $currentLanguage = $logged_in_user->getLanguage();
} else if (isset($_COOKIE["language"])) {
    $currentLanguage = $_COOKIE["language"];
} else if (defined("DB_SERVER") && Setting::get("default_language")) {
    $currentLanguage = Setting::get("default_language");
}

$currentLanguage = setLanguage($currentLanguage);

if (!$currentLanguage) {
    exit("Unable to find any language file.");
}

if (isset($_SERVER["HTTP_HOST"])) {
    if (empty($_COOKIE["language"]) || $currentLanguage !== $_COOKIE["language"]) {
        if (PHP_VERSION_ID < 70300) {
            setcookie("language", $currentLanguage, strtotime("+1 year"), "/; SameSite=Lax", $_SERVER["HTTP_HOST"]);
        } else {
            setcookie("language", $currentLanguage, [
                'expires' => strtotime("+1 year"),
                'path' => '/',
                'domain' => $_SERVER["HTTP_HOST"],
                'samesite' => 'Lax'
            ]);
        }
    }
}

if (defined("DB_SERVER")) {
    if (Setting::get("default_language")) {
        $defaultLanguage = Setting::get("default_language");
    }

    $emails = ["register_email_subject", "reset_password_email_subject", "reset_password_link_email_subject", "edit_user_subject", "register_email_body", "reset_password_email_body", "reset_password_link_email_body", "edit_user_email_body"];
    foreach (Setting::all() as $name => $value) {
        if ($currentLanguage !== $defaultLanguage && in_array($name, $emails)) {
            continue;
        }
        if (isset($lang[$name])) {
            $lang[$name] = $value;
        }
    }
}