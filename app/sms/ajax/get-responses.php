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

    $responses = Response::where("userID", $_SESSION["userID"])->read_all();

    $data = [];
    $matchTypes = [
        0 => __("exact_case_insensitive"),
        1 => __("exact_case_sensitive"),
        2 => __("contains"),
        3 => __("regular_expression"),
    ];
    foreach ($responses as $response) {
        $message = htmlentities($response->getMessage(), ENT_QUOTES);
        $responseText = htmlentities($response->getResponse(), ENT_QUOTES);
        $messageText = nl2br($message);
        $row = [];
        $row[] = "<label><input type='checkbox' name='responses[]' class='remove-responses' onchange='toggleRemove()' value='{$response->getID()}'></label>";
        $row[] = "<a href=\"#\" class=\"edit-response\" data-id=\"{$response->getID()}\" data-message=\"{$message}\" data-response=\"{$responseText}\" data-match-type=\"{$response->getMatchType()}\" data-enabled=\"{$response->getEnabled()}\">{$messageText}</a>";
        $row[] = nl2br($responseText);
        $row[] = $matchTypes[$response->getMatchType()];
        $row[] = $response->getEnabled() == 1 ? __("yes") : __("no");

        $data[] = $row;
    }

    echo json_encode([
        "data" => $data
    ]);
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}