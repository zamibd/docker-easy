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

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

try {
    if (empty($_REQUEST["number"]) || empty($_REQUEST["listID"])) {
        $message = __("error_missing_data");
    } else {
        $contact = new Contact();
        $contact->setNumber($_REQUEST["number"]);
        $contact->setContactsListID($_REQUEST["listID"]);
        if ($contact->read()) {
            if ($contact->getSubscribed()) {
                $contact->setSubscribed(false);
                $contact->save();
                $message = __("success_unsubscribed");
            } else {
                $message = __("error_already_unsubscribed");
            }
        } else {
            $message = __("error_invalid_number");
        }
    }
} catch (Throwable $t) {
    $message = $t->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe</title>
</head>

<body>
<h1><?= $message; ?></h1>
</body>

</html>
