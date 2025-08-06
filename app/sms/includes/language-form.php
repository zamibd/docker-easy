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
 * @var string $currentLanguage
 */

if (count(get_included_files()) == 1) {
    http_response_code(403);
    die("HTTP Error 403 - Forbidden");
}

$languageFiles = getLanguageFiles();
if (count($languageFiles) > 1) {
    ?>
    <div style="position: absolute; right: 28px; top: 28px">
        <form method="get" id="languageForm">
            <select title="Language" name="language" id="languageInput"
                    class="form-control select2" onchange="$('#languageForm').submit()" style="width: 125px">
                <?php
                foreach ($languageFiles as $languageFile) {
                    createOption(ucfirst($languageFile), $languageFile, $languageFile === $currentLanguage);
                }
                ?>
            </select>
        </form>
    </div>
<?php } ?>
