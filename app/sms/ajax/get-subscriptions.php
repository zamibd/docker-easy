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

    $subscriptions = [];
    if ($_SESSION["isAdmin"]) {
        $subscriptions = Subscription::read_all();
    } else {
        $subscriptions = Subscription::where("Subscription.userID", $_SESSION["userID"])->read_all();
    }

    $data = [];
    foreach ($subscriptions as $subscription) {
        $row = [];
        if ($_SESSION["isAdmin"]) {
            $row[] = strval($subscription->getUser());
        }
        $row[] = $subscription->getPaymentMethod() == "Manual" ? $subscription->getID() : $subscription->getSubscriptionID();
        $row[] = htmlentities($subscription->getPlan()->getName(), ENT_QUOTES);
        $row[] = $subscription->getSubscribedDate()->format("Y-m-d H:i:s");
        $row[] = $subscription->getExpiryDate()->format("Y-m-d H:i:s");
        $row[] = $subscription->getCyclesCompleted();
        if ($subscription->getPlan()->getTotalCycles() > 0) {
            $timestamp = $subscription->getSubscribedDate()->getTimestamp() + $subscription->getPlan()->getTotalCycles() * $subscription->getPlan()->getFrequencyInSeconds();
            $renewsUntil = new DateTime('@' . $timestamp);
            $renewsUntil->setTimezone(new DateTimeZone($_SESSION["timeZone"]));
            $row[] = $renewsUntil->format("Y-m-d H:i:s");
        } else {
            $row[] = ucfirst(__("cancelled"));
        }
        $row[] = $subscription->getPaymentMethod();
        if ($subscription->getStatus() === "ACTIVE") {
            $row[] = sprintf("%s&nbsp;<a href=\"#\" class=\"cancel-subscription\" style=\"color: red\" data-id=\"{$subscription->getID()}\" title=\"%s\"><i class=\"fa fa-remove\"></i></a>", $subscription->getStatus(), __("cancel"));
        } else {
            $row[] = $subscription->getStatus();
        }

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