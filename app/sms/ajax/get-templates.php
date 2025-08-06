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

    $templates = Template::where('userID', $_SESSION["userID"])->read_all();
    $data = [];
    foreach ($templates as $template) {
        $row = [];
        $row[] = "<label><input type='checkbox' name='templates[]' class='remove-templates' onchange='toggleRemove()' value='{$template->getID()}'></label>";
        $row[] = "<a href=\"#\" class=\"edit-template\" data-id=\"{$template->getID()}\" data-name=\"{$template->getName()}\" data-message=\"{$template->getMessage()}\">{$template->getName()}</a>";
        $row[] = nl2br($template->getMessage());
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
