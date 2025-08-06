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
    require_once __DIR__ . "/includes/login.php";

    if (isset($_REQUEST["listID"])) {
        $contactsList = ContactsList::getContactsList($_REQUEST["listID"], $_SESSION["userID"]);
        if ($contactsList) {
            $contacts = Contact::where("contactsListID", $contactsList->getID())->read_all(false);
            if (count($contacts) > 0) {
                objectsToExcel($contacts, "{$contactsList->getName()}.csv", ["name" => __("name"), "number" => __("mobile_number"), "subscribed" => __("subscribed")], array("ID", "contactsListID", "token", "contactsList"));
            } else {
                header("location:contacts.php");
            }
        }
    }
} catch (Exception $e) {
    echo json_encode(array(
        "error" => $e->getMessage()
    ));
}

