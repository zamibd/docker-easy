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
        if (isset($_POST["plans"]) && is_array($_POST["plans"])) {
            $count = 0;
            MysqliDb::getInstance()->startTransaction();
            foreach ($_POST["plans"] as $planID) {
                $plan = new Plan();
                $plan->setID($planID);
                if ($plan->read()) {
                    if ($plan->getEnabled() && !empty($plan->getPaypalPlanID())) {
                        try {
                            PayPal::deactivatePlan($plan->getPaypalPlanID());
                        } catch (Exception $e) {
                            // ignored
                        }
                    }
                    $plan->delete();
                    $count++;
                }
            }
            MysqliDb::getInstance()->commit();
            $success = $count > 1 ? __("success_plans_removed", ["count" => $count]) : __("success_plan_removed", ["count" => $count]);
            echo json_encode(array(
                'result' => $success
            ));
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}