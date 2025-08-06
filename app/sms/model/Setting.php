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

class Setting extends Entity
{
    public $name;

    public $value;

    private static $all;

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
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function all(): array
    {
        if (!isset(self::$all)) {
            $settings = Setting::read_all();
            self::$all = [];
            foreach ($settings as $setting) {
                self::$all[$setting->getName()] = $setting->getValue();
            }
        }
        return self::$all;
    }

    /**
     * @param string $name
     * @return string|null
     * @throws Exception
     */
    public static function get(string $name): ?string
    {
        global $lang;
        $settings = self::all();
        if (array_key_exists($name, $settings)) {
            return $settings[$name];
        } else {
            if (isset($lang[$name])) {
                return $lang[$name];
            }
            return "";
        }
    }

    /**
     * @param array $data
     * @param bool $overwrite
     * @throws Exception
     */
    public static function apply(array $data, bool $overwrite = true)
    {
        $startedTransaction = MysqliDb::getInstance()->startTransaction();
        foreach ($data as $entryName => $entryValue) {
            $setting = new Setting();
            $setting->setName($entryName);
            if ($setting->read() && $overwrite === false) {
                continue;
            }
            $setting->setValue($entryValue);
            $setting->save(false);
        }
        if ($startedTransaction) {
            MysqliDb::getInstance()->commit();
        }
        if (isset(self::$all)) {
            foreach ($data as $name => $value) {
                self::$all[$name] = $value;
            }
        }
    }
}