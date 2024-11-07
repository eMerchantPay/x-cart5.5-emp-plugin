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

namespace EMerchantPay\Genesis\Helpers;

use DateTime;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\CardHolderAccount\PasswordChangeIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\CardHolderAccount\RegistrationIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\CardHolderAccount\ShippingAddressUsageIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\CardHolderAccount\UpdateIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\MerchantRisk\ReorderItemIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\MerchantRisk\ShippingIndicators;

class ThreedsIndicatorHelper
{
    /**
     * Indicator value constants
     */
    public const CURRENT_TRANSACTION_INDICATOR       = 'current_transaction';
    public const LESS_THAN_30_DAYS_INDICATOR         = 'less_than_30_days';
    public const MORE_THAN_30_LESS_THAN_60_INDICATOR = 'more_30_less_60_days';
    public const MORE_THAN_60_DAYS_INDICATOR         = 'more_than_60_days';

    /**
     * @var bool
     */
    private $isGuestCustomer;

    public function __construct($isGuestCustomer)
    {
        $this->isGuestCustomer = $isGuestCustomer;
    }

    /**
     * @param $areAllItemsDigital
     * @param $isSameAddress
     * @param $orderBillingAddress
     * @param $orderShippingAddress
     *
     * @return string
     */
    public function fetchShippingIndicator(
        $areAllItemsDigital,
        $isSameAddress,
        $orderBillingAddress,
        $orderShippingAddress
    ) {

        if ($areAllItemsDigital) {
            return ShippingIndicators::DIGITAL_GOODS;
        }

        if ($isSameAddress) {
             return ShippingIndicators::SAME_AS_BILLING;
        }

        if (
            $orderBillingAddress &&
            $orderShippingAddress &&
            !$orderBillingAddress
        ) {
            return ShippingIndicators::STORED_ADDRESS;
        }

        return ShippingIndicators::OTHER;
    }

    /**
     * Fetch 3DSv2 Account Holder Password Change Indicator
     *
     * @param $passwordChangeDate
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function fetchPasswordChangeIndicator($passwordChangeDate)
    {
        if (empty($passwordChangeDate)) {
            return PasswordChangeIndicators::NO_CHANGE;
        }

        switch ($this->getDateIndicator($passwordChangeDate)) {
            case static::LESS_THAN_30_DAYS_INDICATOR:
                return PasswordChangeIndicators::LESS_THAN_30DAYS;
            case static::MORE_THAN_30_LESS_THAN_60_INDICATOR:
                return PasswordChangeIndicators::FROM_30_TO_60_DAYS;
            case static::MORE_THAN_60_DAYS_INDICATOR:
                return PasswordChangeIndicators::MORE_THAN_60DAYS;
            default:
                return PasswordChangeIndicators::DURING_TRANSACTION;
        }
    }

    /**
     * Fetch 3DSv2 Registration Indicator
     *
     * @param $firstOrderCreatedAt
     *
     * @return string
     * @throws \Exception
     */
    public function fetchRegistrationIndicator($firstOrderCreatedAt)
    {

        if ($firstOrderCreatedAt == null) {
            return null;
        }

        switch ($this->getDateIndicator($firstOrderCreatedAt)) {
            case static::LESS_THAN_30_DAYS_INDICATOR:
                return RegistrationIndicators::LESS_THAN_30DAYS;
            case static::MORE_THAN_30_LESS_THAN_60_INDICATOR:
                return RegistrationIndicators::FROM_30_TO_60_DAYS;
            case static::MORE_THAN_60_DAYS_INDICATOR:
                return RegistrationIndicators::MORE_THAN_60DAYS;
            default:
                return RegistrationIndicators::CURRENT_TRANSACTION;
        }
    }

    /**
     * Fetch whether product/s have been previously re-ordered
     *
     * @param $orderItems
     * @param $profileBoughtItems
     *
     * @return string
     */
    public function fetchReorderItemsIndicator($orderItems, $profileBoughtItems)
    {

        if ($this->isGuestCustomer) {
            return ReorderItemIndicators::FIRST_TIME;
        }

        $items          = $orderItems;
        $boughtProducts = $profileBoughtItems;

        foreach ($items as $product) {
            if (in_array($product->getProductId(), $boughtProducts)) {
                return ReorderItemIndicators::REORDERED;
            }
        }

        return ReorderItemIndicators::FIRST_TIME;
    }

    /**
     * Fetch 3DSv2 Account Holder Update Indicator
     *
     * @param $customerModifyDate
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function fetchUpdateIndicator($customerModifyDate)
    {
        $indicatorClass = UpdateIndicators::class;

        if (!$customerModifyDate || $this->isGuestCustomer) {
            return UpdateIndicators::CURRENT_TRANSACTION;
        }

        switch ($this->getDateIndicator($customerModifyDate)) {
            case static::LESS_THAN_30_DAYS_INDICATOR:
                return $indicatorClass::LESS_THAN_30DAYS;
            case static::MORE_THAN_30_LESS_THAN_60_INDICATOR:
                return $indicatorClass::FROM_30_TO_60_DAYS;
            case static::MORE_THAN_60_DAYS_INDICATOR:
                return $indicatorClass::MORE_THAN_60DAYS;
            default:
                return $indicatorClass::CURRENT_TRANSACTION;
        }
    }

    /**
     * Fetch 3DSv2 Shipping Usage Indicator
     *
     * @param $date
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchShippingAddressUsageIndicator($date)
    {
        if ($date == null) {
            return null;
        }

        switch ($this->getDateIndicator($date)) {
            case static::LESS_THAN_30_DAYS_INDICATOR:
                return ShippingAddressUsageIndicators::LESS_THAN_30DAYS;
            case static::MORE_THAN_30_LESS_THAN_60_INDICATOR:
                return ShippingAddressUsageIndicators::FROM_30_TO_60_DAYS;
            case static::MORE_THAN_60_DAYS_INDICATOR:
                return ShippingAddressUsageIndicators::MORE_THAN_60DAYS;
            default:
                return ShippingAddressUsageIndicators::CURRENT_TRANSACTION;
        }
    }

    /**
     * @param $date
     *
     * @return string
     * @throws \Exception
     */
    private function getDateIndicator($date)
    {
        $now       = new DateTime();
        $checkDate = \DateTime::createFromFormat(Helper::THREEDS_DATE_FORMAT, $date);
        $days      = $checkDate->diff($now)->days;

        if ($days < 1) {
            return self::CURRENT_TRANSACTION_INDICATOR;
        }
        if ($days <= 30) {
            return self::LESS_THAN_30_DAYS_INDICATOR;
        }
        if ($days < 60) {
            return self::MORE_THAN_30_LESS_THAN_60_INDICATOR;
        }

        return self::MORE_THAN_60_DAYS_INDICATOR;
    }
}
