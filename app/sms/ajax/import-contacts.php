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

use OpenSpout\Reader\Common\Creator\ReaderFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    require_once __DIR__ . "/../includes/ajax_protect.php";
    require_once __DIR__ . "/../includes/login.php";

    $db = MysqliDb::getInstance();

    if (isset($_FILES['file']['tmp_name']) && isset($_POST["listID"]) && ctype_digit($_POST["listID"])) {
        if (ContactsList::getContactsList($_POST["listID"], $_SESSION["userID"])) {
            require_once __DIR__ . "/../includes/read-excel.php";
            $uploadDirectory = __DIR__ . "/../uploads/tmp";
            $filename = basename($_FILES['file']['name']);
            if (is_dir($uploadDirectory) || mkdir($uploadDirectory, 0755, true)) {
                $filePath = "{$uploadDirectory}/{$filename}";
                if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                    $reader = IOFactory::createReaderForFile($filePath);
                    if ($reader instanceof PhpOffice\PhpSpreadsheet\Reader\Csv) {
                        $reader->setDelimiter(",");
                    }
                    $worksheetInfo = $reader->listWorksheetInfo($filePath);
                    unset($reader);
                    $rowsCount = $worksheetInfo[0]['totalRows'] - 1;
                    $totalContacts = Contact::where('ContactsList.userID', $logged_in_user->getID())->count();
                    $totalContacts += $rowsCount;
                    if ($logged_in_user->getContactsLimit() !== null && $logged_in_user->getContactsLimit() <= $totalContacts) {
                        throw new Exception(__("error_contacts_limit_reached"));
                    }
                    if ($rowsCount > 25000) {
                        $reader = ReaderFactory::createFromFile($filePath);
                        $reader->open($filePath);
                        $foundNew = false;
                        $contacts = ContactsList::getNumbers($_POST["listID"]);
                        foreach ($reader->getSheetIterator() as $sheet) {
                            foreach ($sheet->getRowIterator() as $row) {
                                $cells = $row->getCells();
                                if (count($cells) >= 2) {
                                    $number = sanitize($cells[1]->getValue());
                                    if (isValidMobileNumber($number) && !isset($contacts[$number])) {
                                        $foundNew = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                        $reader->close();
                        if (!$foundNew) {
                            throw new Exception(__("error_no_contacts_found"));
                        }
                        Job::queue("Contact::import", [$filePath, $_POST["listID"]], "list-{$_POST["listID"]}");
                        unset($filePath);
                        echo json_encode(array(
                            'result' => __("success_queued_contacts_import")
                        ));
                    } else {
                        $count = Contact::import($filePath, $_POST["listID"]);
                        if ($count > 0) {
                            $success = $count > 1 ? __("success_contacts_saved", ["count" => $count]) : __("success_contact_saved", ["count" => $count]);
                            echo json_encode(array(
                                'result' => $success
                            ));
                        } else {
                            throw new Exception(__("error_no_contacts_found"));
                        }
                    }
                } else {
                    throw new Exception(__("error_uploading_excel_file"));
                }
            } else {
                throw new Exception(__("error_creating_directory", ["name" => "upload"]));
            }
        }
    }
} catch (Throwable $t) {
    if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }
    echo json_encode(array(
        'error' => $t->getMessage()
    ));
}
