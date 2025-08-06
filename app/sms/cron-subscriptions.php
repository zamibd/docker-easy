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
require_once __DIR__ . "/includes/set-language.php";

date_default_timezone_set(TIMEZONE);

try {
    MysqliDb::getInstance()->startTransaction();
    $subscriptions = Subscription::where("Subscription.status", "ACTIVE")->read_all();
//    $admin = User::getAdmin();
//    $from = array($admin->getEmail(), $admin->getName());
    foreach ($subscriptions as $subscription) {
        $renew = $subscription->getPlan()->getTotalCycles() == 0 || $subscription->getCyclesCompleted() < $subscription->getPlan()->getTotalCycles();
//        $secondsTillRenew = (new DateTime())->getTimestamp() - $subscription->getExpiryDate()->getTimestamp();
//        if ($renew && $subscription->getPaymentMethod() == "PayPal" && $secondsTillRenew >= 259200 && $secondsTillRenew <= 259259) {
//            $to = array($subscription->getUser()->getEmail(), $subscription->getUser()->getName());
//            sendEmail($from, $to, "Subscription Renewal", "Your subscription will be renewed in 3 days and you will be charged {$subscription->getPlan()->getPrice()} {$subscription->getPlan()->getCurrency()}}.");
//        }
        if ($subscription->getExpiryDate() < new DateTime()) {
            if ($renew) {
                $subscription->setCyclesCompleted($subscription->getCyclesCompleted() + 1);
                $expiryDate = date("Y-m-d H:i:s", $subscription->getExpiryDate()->getTimestamp() + $subscription->getPlan()->getFrequencyInSeconds());
                $subscription->setExpiryDate($expiryDate);
                $subscription->save();
                $subscription->renew($expiryDate);
            } else {
                $subscription->setStatus("EXPIRED");
                $subscription->save();
            }
        }
    }
    MysqliDb::getInstance()->commit();
} catch (Exception $e) {
    error_log($e->getMessage());
}
