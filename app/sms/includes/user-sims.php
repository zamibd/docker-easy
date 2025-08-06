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

if (count(get_included_files()) == 1) {
    http_response_code(403);
    die("HTTP Error 403 - Forbidden");
}

if (!isset($sims)) {
    $sims = $logged_in_user->getSims();
}

?>

<script type="text/javascript">

    $(function () {
        const deviceInput = $('#deviceInput');

        let sims = <?= json_encode($sims, JSON_FORCE_OBJECT) ?>;

        function getSims(deviceId) {
            let simsInput = $('#simInput');
            simsInput.html('');
            simsInput.append('<option value=""><?=__("default")?></option>');
            if (sims[deviceId]) {
                $.each(sims[deviceId], function (val, label) {
                    let selected = '';
                    <?php if(isset($_REQUEST["sim"])) { ?>
                    if (val === '<?=$_REQUEST["sim"]?>') {
                        selected = 'selected="selected"';
                    }
                    <?php } ?>
                    simsInput.append(`<option value="${val}" ${selected}>${label}</option>`);
                });
            }
        }

        deviceInput.change(function () {
            getSims(this.value);
        });

        getSims(deviceInput.val());
    });

</script>
