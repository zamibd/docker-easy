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

class ContactsList extends Entity
{
    public $name;

    public $userID;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getUserID(): int
    {
        return $this->userID;
    }

    /**
     * @param int $userID
     */
    public function setUserID(int $userID)
    {
        $this->userID = $userID;
    }

    /**
     * @param int $listID
     * @param int $userID
     * @return bool|ContactsList
     * @throws Exception
     */
    public static function getContactsList(int $listID, int $userID)
    {
        $contactsList = new ContactsList();
        $contactsList->setID($listID);
        $contactsList->setUserID($userID);
        return $contactsList->read();
    }

    /**
     * @param int $listID
     * @return array
     * @throws Exception
     */
    public static function getNumbers(int $listID): array
    {
        $result = MysqliDb::getInstance()
            ->where('contactsListID', $listID)
            ->orderBy('number')
            ->get('Contact', null, 'number');
        $contacts = [];
        foreach ($result as $row) {
            $contacts[$row["number"]] = 1;
        }
        return $contacts;
    }
}