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

class Sim extends Entity
{
    public $name;

    public $carrier;

    public $country;

    public $iccID;

    public $number;

    public $slot;

    public $deviceID;

    public $enabled;

    public $device;

    public static $relations = [
        "Device" => ["ID", "deviceID"]
    ];

    public function __construct()
    {
        $this->device = new Device();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    /**
     * @param string|null $carrier
     */
    public function setCarrier(?string $carrier)
    {
        $this->carrier = $carrier;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getIccID(): string
    {
        return $this->iccID;
    }

    /**
     * @param string $iccID
     */
    public function setIccID(string $iccID)
    {
        $this->iccID = $iccID;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string|null $number
     */
    public function setNumber(?string $number)
    {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getSlot(): int
    {
        return $this->slot;
    }

    /**
     * @param int $slot
     */
    public function setSlot(int $slot)
    {
        $this->slot = $slot;
    }

    /**
     * @return int
     */
    public function getDeviceID(): int
    {
        return $this->deviceID;
    }

    /**
     * @param int $deviceID
     */
    public function setDeviceID(int $deviceID)
    {
        $this->deviceID = $deviceID;
    }

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @param Device $device
     */
    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    public function __toString()
    {
        $simIndex = $this->getSlot() + 1;
        if (!empty($this->getNumber())) {
            $name = $this->getNumber();
        } else if (!empty($this->getName())) {
            $name = $this->getName();
        } else if (!empty($this->getCarrier())) {
            $name = $this->getCarrier();
        }
        if (isset($name)) {
            return "SIM #{$simIndex} [{$name}]";
        } else {
            return "SIM #{$simIndex}";
        }
    }

    /**
     * @param stdClass $object
     * @return Sim
     */
    public static function fromObject(stdClass $object): Sim
    {
        $sim = new Sim();
        foreach ($object as $property => $value) {
            $sim->$property = $value;
        }
        return $sim;
    }

    /**
     * @param int|null $userID
     * @return array
     * @throws Exception
     */
    public static function getSims(?int $userID = null): array
    {
        $deviceIds = [];
        if ($userID != null) {
            $deviceIds = User::getDeviceIds($userID);
        }
        $objects = Sim::where("Sim.enabled", true);
        if ($deviceIds) {
            $objects->where('Sim.DeviceID', $deviceIds, 'IN');
        }
        $objects = $objects->read_all(false);
        $sims = [];
        foreach ($objects as $sim) {
            $sims[$sim->getDeviceID()][$sim->getSlot()] = strval($sim);
        }
        return $sims;
    }
}