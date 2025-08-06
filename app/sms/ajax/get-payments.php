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

    $payments = [];
    if ($_SESSION["isAdmin"]) {
        $payments = Payment::read_all();
    } else {
        $payments = Payment::where("Payment.userID", $_SESSION["userID"])->read_all();
    }

    $data = [];
    foreach ($payments as $payment) {
        $row = [];
        $row[] = $payment->getTransactionID();
        if ($_SESSION["isAdmin"] && $payment->getStatus() === "COMPLETED") {
            $row[] = sprintf("%s&nbsp;<a href=\"#\" class=\"refund-payment\" style=\"color: red\" data-id=\"{$payment->getID()}\" title=\"%s\"><i class=\"fa fa-remove\"></i></a>", $payment->getStatus(), __("refund"));
        } else {
            $row[] = $payment->getStatus();
        }
        $row[] = "{$payment->getAmount()} {$payment->getCurrency()}";
        $row[] = "{$payment->getTransactionFee()} {$payment->getCurrency()}";
        $row[] = $payment->getSubscription()->getSubscriptionID();
        $row[] = $payment->getSubscription()->getPaymentMethod();
        $row[] = $payment->getDateAdded()->format("Y-m-d H:i:s");

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