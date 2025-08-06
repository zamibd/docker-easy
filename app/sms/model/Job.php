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

class Job extends Entity
{
    public $functionName;

    public $arguments;

    public $lockName;

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @param string $functionName
     */
    public function setFunctionName(string $functionName): void
    {
        $this->functionName = $functionName;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return json_decode($this->arguments);
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = json_encode($arguments);
    }

    /**
     * @return string|null
     */
    public function getLockName(): ?string
    {
        return $this->lockName;
    }

    /**
     * @param string|null $lockName
     */
    public function setLockName(?string $lockName): void
    {
        $this->lockName = $lockName;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function execute() {
        if ($this->lockName) {
            return lock($this->lockName, function () {
                return call_user_func_array($this->getFunctionName(), $this->getArguments());
            });
        } else {
            return call_user_func_array($this->getFunctionName(), $this->getArguments());
        }
    }

    /**
     * @param string $functionName
     * @param array $arguments
     * @param string|null $lockName
     * @throws Exception
     */
    public static function queue(string $functionName, array $arguments, ?string $lockName = null) {
        $job = new Job();
        $job->setFunctionName($functionName);
        $job->setArguments($arguments);
        $job->setLockName($lockName);
        $job->save();
    }
}