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

    if (empty($_POST["contactsList"]) || empty($_POST["devices"]) || ($_POST["type"] === 'sms' && empty($_POST["message"]))) {
        throw new Exception(__("error_missing_fields"));
    } else {
        if (ContactsList::getContactsList($_POST["contactsList"], $_SESSION["userID"])) {
            $contacts = Contact::where("contactsListID", $_POST["contactsList"])
                ->where("subscribed", true)
                ->read_all(false);
            if (empty($contacts)) {
                throw new Exception(__("error_no_subscribers"));
            } else {
                $messages = [];
                $attachments = $logged_in_user->upload("attachments");
                if (count($attachments) > 0) {
                    $attachments = implode(',', $attachments);
                } else {
                    if ($_POST["type"] === 'mms' && empty($_POST["message"])) {
                        throw new Exception(__("error_missing_fields"));
                    }
                    $attachments = null;
                }
                foreach ($contacts as $contact) {
                    $number = $contact->getNumber();
                    $message = $contact->getMessage($_POST["message"]);
                    $messages[] = ["number" => $number, "message" => $message, "attachments" => $attachments, 'type' => $_POST["type"]];
                }
                $schedule = null;
                if (isset($_POST["schedule"])) {
                    $schedule = new DateTime($_POST["schedule"], new DateTimeZone($_SESSION["timeZone"]));
                    $schedule = $schedule->getTimestamp();
                }
                Message::sendMessages($messages, $logged_in_user, $_POST["devices"], $schedule, $_POST["prioritize"]);
                $success = is_null($schedule) ? __("success_sent") : __("success_scheduled");
                echo json_encode(array(
                    'result' => $success
                ));
            }
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => nl2br(htmlentities($t->getMessage(), ENT_QUOTES))
    ));
}