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

// $_SERVER['PHP_SELF'] is dangerous if misused. If login.php/nearly_arbitrary_string is requested, $_SERVER['PHP_SELF'] will contain not just login.php, but the entire login.php/nearly_arbitrary_string.
// Use $_SERVER['SCRIPT_NAME'] instead of $_SERVER['PHP_SELF'].
$currentPage = basename($_SERVER['SCRIPT_NAME']);

array_walk_recursive($_REQUEST, 'trimByReference');
array_walk_recursive($_GET, 'trimByReference');
array_walk_recursive($_POST, 'trimByReference');

$accessibleScripts = [
    "index.php",
    "login-form.php",
    "reset-password.php",
    "reset-password-link.php",
    "register.php",
    "register-user.php"
];

if (in_array($currentPage, $accessibleScripts)) {
    require_once __DIR__ . "/set-language.php";
}
