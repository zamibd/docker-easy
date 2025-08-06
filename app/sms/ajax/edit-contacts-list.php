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

    if (!empty($_POST["name"]) && !empty($_POST["contactsListID"])) {
        $count = ContactsList::where('ID', $_POST["contactsListID"], '!=')
            ->where('userID', $_SESSION["userID"])
            ->where('name', $_POST["name"])
            ->count();
        if ($count > 0) {
            throw new Exception(__("error_contacts_list_exist"));
        } else {
            $contactsList = new ContactsList();
            $contactsList->setID($_POST["contactsListID"]);
            $contactsList->setUserID($_SESSION["userID"]);
            if ($contactsList->read()) {
                $contactsList->setName($_POST["name"]);
                $contactsList->save();
                echo json_encode([
                    "result" => __("success_contacts_list_updated"),
                ]);
            }
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
