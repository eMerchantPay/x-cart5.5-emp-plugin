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
namespace Emerchantpay\Genesis\Controller\Admin;

/**
 * @package Emerchantpay\Genesis\Controller\Admin
 */
class EmerchantpaySettings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Module Path
     */
    public const MODULE_NAME = 'Emerchantpay_Genesis';

    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('method_id');

    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t($this->getPaymentMethod()->getTitle() . ' Settings');
    }

    /**
     * Returns Author Website
     *
     * @return string
     */
    public function getAuthorWebSite()
    {
        return \Emerchantpay\Genesis\Main::getAuthorWebsite();
    }

    /**
     * Return class name for the controller main form
     *
     * @return string
     */
    public function getModelFormClass()
    {
        return sprintf(
            '\Emerchantpay\Genesis\View\Model\%s',
            $this->getPaymentMethod()->getServiceName()
        );
    }

    /**
     * Get method id from request
     *
     * @return int
     */
    public function getMethodId()
    {
        return \XLite\Core\Request::getInstance()->method_id;
    }

    /**
     * Get configuration setting
     *
     * @param $key
     *
     * @return string|void
     */
    public function getSetting($key)
    {
        $paymentMethod = $this->getPaymentMethod();

        return $paymentMethod
            ? $paymentMethod->getSetting($key)
            : '';
    }

    /**
     * Get payment method
     *
     * @return \XLite\Model\Payment\Method
     */
    public function getPaymentMethod()
    {
        $paymentMethod = $this->getMethodId()
            ? \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')->find($this->getMethodId())
            : null;

        return $paymentMethod && static::MODULE_NAME === $paymentMethod->getModuleName()
            ? $paymentMethod
            : null;
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $this->getModelForm()->performAction('modify');
    }
}
