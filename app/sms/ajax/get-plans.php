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
 * @noinspection BadExpressionStatementJS
 * @noinspection CommaExpressionJS
 */

try {
    require_once __DIR__ . "/../includes/ajax_protect.php";
    require_once __DIR__ . "/../includes/login.php";

    if ($_SESSION["isAdmin"]) {
        $plans = Plan::read_all();
        $data = [];
        foreach ($plans as $plan) {
            $row = [];
            $name = htmlentities($plan->name, ENT_QUOTES);
            $contacts = is_null($plan->getContacts()) ? 'null' : $plan->getContacts();
            $devices = is_null($plan->getDevices()) ? 'null' : $plan->getDevices();
            $credits = is_null($plan->getCredits()) ? 'null' : $plan->getCredits();
            $row[] = "<label><input type='checkbox' name='plans[]' class='remove-plans' onchange='toggleRemove()' value='{$plan->getID()}'></label>";
            $row[] = "<a href=\"#plans\" onclick=\"editPlan('{$name}', {$credits}, {$devices}, {$contacts}, {$plan->getEnabled()}, {$plan->getID()})\">{$name}</a>";
            $row[] = is_null($plan->getDevices()) ? '&infin;' : $plan->getDevices();
            $row[] = is_null($plan->getContacts()) ? '&infin;' : $plan->getContacts();
            $row[] = is_null($plan->getCredits()) ? '&infin;' : $plan->getCredits();
            $row[] = "{$plan->getPrice()} {$plan->getCurrency()}";
            $frequencyUnit = ucfirst(__(strtolower($plan->getFrequencyUnit())));
            $row[] = "{$plan->getFrequency()} {$frequencyUnit}";
            $row[] = $plan->getTotalCycles() == 0 ? "&infin;" : $plan->getTotalCycles();
            $row[] = $plan->getEnabled() ? "<i class='fa fa-check'></i>" : "<i class='fa fa-close'></i>";
            $data[] = $row;
        }

        echo json_encode([
            "data" => $data
        ]);
    }
} catch (Throwable $t) {
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}

