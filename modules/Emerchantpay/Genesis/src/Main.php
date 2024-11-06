<?php

/*
 * Copyright (C) 2018-2025 emerchantpay Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      emerchantpay
 * @copyright   2018-2025 emerchantpay Ltd.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

namespace Emerchantpay\Genesis;

/**
 * emerchantpay Module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Name of the emerchantpay Checkout method
     */
    private const EMP_CHECKOUT = 'EmerchantpayCheckout';

    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'emerchantpay Ltd.';
    }

    /**
     * Author Website
     *
     * @return string
     */
    public static function getAuthorWebsite()
    {
        return 'https://emerchantpay.com';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'emerchantpay';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.5';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '1';
    }

    /**
     * Module built version
     *
     * @return string
     */
    public static function getBuildVersion()
    {
        return '0';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Accept payments through emerchantpay\'s Payment Gateway - Genesis';
    }

    /**
     * The module is defined as the payment module
     *
     * @return int|null
     */
    public static function getModuleType()
    {
        return static::MODULE_TYPE_PAYMENT;
    }

    /**
     * Returns payment method
     *
     * @param string  $service_name Service name
     * @param boolean $enabled      Enabled status OPTIONAL
     *
     * @return \XLite\Model\Payment\Method
     */
    public static function getPaymentMethod($service_name, $enabled = null)
    {
        $condition = array(
            'service_name' => $service_name
        );

        if (null !== $enabled) {
            $condition['enabled'] = (bool) $enabled;
        }

        return \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                                                    ->findOneBy($condition);
    }

    /**
     * Returns true if emerchantpayCheckout payment is enabled
     *
     * @param \XLite\Model\Cart $order Cart object OPTIONAL
     *
     * @return boolean
     */
    public static function isEmerchantpayCheckoutEnabled($order = null)
    {
        static $result;

        $index = isset($order) ? 1 : 0;

        if (!isset($result[$index])) {
            $paymentMethod = self::getPaymentMethod(self::EMP_CHECKOUT, true);

            if ($order && $result[$index]) {
                $result[$index] = $paymentMethod->getProcessor()->isApplicable($order, $paymentMethod);
            }
        }

        return $result[$index];
    }

    /**
     * Check - SSL Enabled
     *
     * @return boolean
     */
    public static function isStoreOverSecuredConnection()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    /**
     * Retrieves the X-Cart Core Version
     *
     * @return string
     */
    public static function getCurrentCoreVersion()
    {
        return \XLite::getInstance()->getVersion();
    }

    /**
     * Detects if the X-Cart Core Version is 5.2
     *
     * @return bool
     */
    public static function isCoreVersion52()
    {
        return
            version_compare(self::getCurrentCoreVersion(), '5.2', '>=') &&
            version_compare(self::getCurrentCoreVersion(), '5.3', '<=');
    }

    /**
     * Detects if the X-Cart Core Version is 5.3
     *
     * @return bool
     */
    public static function isCoreAboveVersion53()
    {
        return version_compare(self::getCurrentCoreVersion(), '5.3', '>=');
    }
}
