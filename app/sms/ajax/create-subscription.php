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

    if ($_SESSION["isAdmin"]) {
        if (empty($_POST["userID"]) || empty($_POST["planID"])) {
            throw new Exception(__("error_missing_fields"));
        } else {
            if (ctype_digit($_POST["userID"]) && ctype_digit($_POST["planID"])) {
                if (Subscription::where("userID", $_POST["userID"])->where("status", "ACTIVE")->count() == 0) {
                    $plan = new Plan();
                    $plan->setID($_POST["planID"]);
                    if ($plan->read()) {
                        MysqliDb::getInstance()->startTransaction();
                        $subscription = new Subscription();
                        $subscription->setUserID($_POST["userID"]);
                        $subscription->setPlanID($_POST["planID"]);
                        $subscription->setPlan($plan);
                        $subscription->setSubscribedDate(date("Y-m-d H:i:s"));
                        $expiryDate = date("Y-m-d H:i:s", time() + $plan->getFrequencyInSeconds());
                        $subscription->setExpiryDate($expiryDate);
                        $subscription->setPaymentMethod("Manual");
                        $subscription->setCyclesCompleted(1);
                        $subscription->setStatus("ACTIVE");
                        $subscription->save();
                        DeviceUser::toggleDemoDevices($subscription->getUserID());
                        $subscription->renew($expiryDate);
                        MysqliDb::getInstance()->commit();
                        echo json_encode([
                            'result' => __("success_create_subscription")
                        ]);
                    }
                } else {
                    throw new Exception(__("error_user_already_subscribed"));
                }
            }
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
