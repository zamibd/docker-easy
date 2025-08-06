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

    if (!empty($_POST["subscriptionID"])) {
        $details = PayPal::getSubscriptionDetails($_POST["subscriptionID"]);
        $plan = new Plan();
        $plan->setPaypalPlanID($details->plan_id);
        if ($plan->read()) {
            MysqliDb::getInstance()->startTransaction();
            $subscription = new Subscription();
            $subscription->setUserID($logged_in_user->getID());
            $subscription->setSubscriptionID($details->id);
            $subscription->setPlanID($plan->getID());
            $subscription->setPlan($plan);
            $subscription->setSubscribedDate(date("Y-m-d H:i:s", strtotime($details->create_time)));
            //$expiryDate = date("Y-m-d H:i:s", strtotime($details->billing_info->next_billing_time));
            $expiryDate = date("Y-m-d H:i:s", strtotime($details->create_time) + $plan->getFrequencyInSeconds());
            $subscription->setExpiryDate($expiryDate);
            $subscription->setPaymentMethod("PayPal");
            $subscription->setCyclesCompleted(1);
            $subscription->setStatus("ACTIVE");
            $subscription->save();
            DeviceUser::toggleDemoDevices($subscription->getUserID());
            $subscription->renew($expiryDate);
            MysqliDb::getInstance()->commit();
            echo json_encode([
                'result' => __("success_subscribed")
            ]);
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}