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

try {
    require_once __DIR__ . "/../includes/ajax_protect.php";
    require_once __DIR__ . "/../includes/login.php";

    if (isset($_POST["numbers"])) {
        // https://stackoverflow.com/a/7058270/1273550
        $numbers = preg_split('/\r\n|[\r\n]/', $_POST['numbers']);
        $count = 0;
        MysqliDb::getInstance()->startTransaction();
        foreach ($numbers as $number) {
            if (isValidMobileNumber($number)) {
                $entry = new Blacklist();
                $entry->setNumber($number);
                if ($_SESSION["isAdmin"] && isset($_GET["user"])) {
                    $entry->setUserID($_GET["user"]);
                } else {
                    $entry->setUserID($_SESSION["userID"]);
                }
                if (!$entry->read()) {
                    $entry->save();
                    $count++;
                }
            }
        }
        MysqliDb::getInstance()->commit();
        if ($count > 0) {
            echo json_encode([
                "result" => __("success_add_to_blacklist")
            ]);
        } else {
            throw new Exception(__("error_no_valid_numbers_found"));
        }
    } else {
        throw new Exception(__("error_missing_fields"));
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
