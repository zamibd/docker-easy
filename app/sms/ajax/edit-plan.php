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

    if ($_SESSION["isAdmin"]) {
        if (!isset($_POST["enabled"]) || empty($_POST["planID"])) {
            throw new Exception(__("error_missing_fields"));
        } else {
            $plan = new Plan();
            $plan->setID($_POST["planID"]);
            if ($plan->read()) {
                //$plan->setName($_POST["name"]);
                if (isset($_POST["devicesLimit"])) {
                    if (ctype_digit($_POST["devicesLimit"])) {
                        $plan->setDevices($_POST["devicesLimit"]);
                    } else {
                        throw new Exception(__("error_max_devices_not_number"));
                    }
                } else {
                    $plan->setDevices(null);
                }
                if (isset($_POST["contactsLimit"])) {
                    if (ctype_digit($_POST["contactsLimit"])) {
                        $plan->setContacts($_POST["contactsLimit"]);
                    } else {
                        throw new Exception(__("error_max_contacts_not_number"));
                    }
                } else {
                    $plan->setContacts(null);
                }
                if (isset($_POST["credits"])) {
                    if (ctype_digit($_POST["credits"])) {
                        $plan->setCredits($_POST["credits"]);
                    } else {
                        throw new Exception(__("error_credits_not_number"));
                    }
                } else {
                    $plan->setCredits(null);
                }
                $oldEnabledValue = $plan->getEnabled();
                $plan->setEnabled($_POST["enabled"]);
                if (!empty($plan->getPaypalPlanID())) {
                    if ($oldEnabledValue != $plan->getEnabled()) {
                        if ($plan->getEnabled()) {
                            PayPal::activatePlan($plan->getPaypalPlanID());
                        } else {
                            PayPal::deactivatePlan($plan->getPaypalPlanID());
                        }
                    }
                }
                $plan->save(false);
                echo json_encode([
                    'result' => __("success_edit_plan")
                ]);
            }
        }
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
