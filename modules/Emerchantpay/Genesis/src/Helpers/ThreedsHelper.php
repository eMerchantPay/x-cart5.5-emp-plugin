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

namespace Emerchantpay\Genesis\Helpers;

use DateInterval;
use DateTime;
use XLite\Core\CommonCell;
use Emerchantpay\Genesis\Model\Payment\Processor\EmerchantpayCheckout;

class ThreedsHelper
{
    public const ACTIVITY_24_HOURS = 'PT24H';
    public const ACTIVITY_6_MONTHS = 'P6M';
    public const ACTIVITY_1_YEAR   = 'P1Y';

    /**
     * @var $transaction
     */
    private $transaction;

    /**
     * @var $profileOrders
     */
    private $profileOrders;

    /**
     * @var ThreedsIndicatorHelper
     */
    private $treedsIndicatorHelper;

    /**
     * ThreedsHelper constructor.
     *
     * @param $transaction
     */
    public function __construct($transaction)
    {
        $this->transaction           = $transaction;
        $this->treedsIndicatorHelper = new ThreedsIndicatorHelper(Helper::isGuestCustomer());
        $this->profileOrders         = $this->getProfileOrders();
    }

    /**
     * Check that billing and shipping addresses are the same from current order
     *
     * @return mixed
     */
    public function isSameAddress()
    {
        return $this->transaction->getOrder()->getProfile()->isSameAddress();
    }

    /**
     * Get billing address from order
     *
     * @return mixed
     */
    public function getOrderBillingAddress()
    {
        return $this->transaction->getOrder()->getProfile()->getBillingAddress();
    }

    /**
     * Get shipping address from order
     *
     * @return mixed
     */
    public function getOrderShippingAddress()
    {
        return $this->transaction->getOrder()->getProfile()->getShippingAddress();
    }

    /**
     * Fetch the Shipping Indicator from the Order Data
     *
     * @return string
     * @throws \Exception
     */
    public function fetchShippingIndicator()
    {
        return $this->treedsIndicatorHelper->fetchShippingIndicator(
            $this->areAllItemsDigital(),
            $this->isSameAddress(),
            $this->getOrderBillingAddress(),
            $this->getOrderShippingAddress()
        );
    }

    /**
     * Fetch whether product/s have been previously re-ordered
     *
     * @return string
     * @throws \Exception
     */
    public function fetchReorderItemsIndicator()
    {
        return $this->treedsIndicatorHelper
                ->fetchReorderItemsIndicator(
                    $this->transaction->getOrder()->getItems(),
                    $this->getProfileBoughtItems()
                );
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function fetchUpdateIndicator()
    {
        return $this->treedsIndicatorHelper->fetchUpdateIndicator($this->getPasswordChangeDate());
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function fetchPasswordChangeIndicator()
    {
        return $this->treedsIndicatorHelper->fetchPasswordChangeIndicator($this->getPasswordChangeDate());
    }

    /**
     * Get Shipping address usage indicator by current profile first order date
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchShippingAddressUsageIndicator()
    {
        return $this->treedsIndicatorHelper->fetchShippingAddressUsageIndicator(
            $this->getShippingAddressDateFirstUsed()
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function fetchRegistrationIndicator()
    {
        return $this->treedsIndicatorHelper->fetchRegistrationIndicator($this->getProfileFirstOrderDate());
    }

    /**
     * @return bool
     */
    public function hasPhysicalProduct()
    {
        return ! $this->areAllItemsDigital() ? true : false;
    }

    /**
     * @return bool
     */
    public function areAllItemsDigital()
    {
        foreach ($this->transaction->getOrder()->getItems() as $item) {
            if ($item->isShippable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return false|string|null
     */
    public function getPasswordChangeDate()
    {
        return Helper::getProfilePasswordChangeDate() > 0
            ? date(Helper::THREEDS_DATE_FORMAT, Helper::getProfilePasswordChangeDate())
            : null;
    }

    /**
     * @param $period
     *
     * @return mixed
     * @throws \Exception
     */
    public function countOrdersPeriod($period)
    {
        $dateFrom             = (new DateTime())->sub(new DateInterval($period));
        $searchCondition      = new CommonCell();

        $searchCondition->{\XLite\Model\Repo\Order::P_DATE} = array(
            $dateFrom->getTimestamp(),
            \XLite\Core\Converter::time()
        );

        if ($period == self::ACTIVITY_1_YEAR) {
            $previousYear = $this->getPreviousYear();

            $searchCondition->{\XLite\Model\Repo\Order::P_DATE} = array(
                $previousYear['from']->getTimestamp(),
                $previousYear['to']->getTimestamp(),
            );
        }

        $searchCondition->{\XLite\Model\Repo\Order::P_PROFILE_ID}          = Helper::getCurrentUserId();
        // Plugin Generation style fix
        $searchCondition->{\XLite\Model\Repo\Order::P_PAYMENT_METHOD_NAME} =
            EmerchantpayCheckout::PAYMENT_METHOD_NAME;

        if ($period == self::ACTIVITY_6_MONTHS) {
            $paidStatus = \XLite\Model\Order\Status\Payment::getPaidStatuses();
            $searchCondition->{\XLite\Model\Repo\Order::P_PAYMENT_STATUS} = $paidStatus;
        }

        $result = \XLite\Core\Database::getRepo('XLite\Model\Order')->search($searchCondition, true);

        return $result > 0 ? $result : null;
    }

    /**
     * Get profile order history
     *
     * @return array
     */
    public function getProfileOrders()
    {
        $searchCondition                                                   = new CommonCell();
        $searchCondition->{\XLite\Model\Repo\Order::P_PROFILE_ID}          = Helper::getCurrentUserId();
        // Plugin Generation style fix
        $searchCondition->{\XLite\Model\Repo\Order::P_PAYMENT_METHOD_NAME} =
            EmerchantpayCheckout::PAYMENT_METHOD_NAME;
        $searchCondition->{\XLite\Model\Repo\AttributeOption::P_ORDER_BY}  = ['o.date', 'ASC'];

        return \XLite\Core\Database::getRepo('XLite\Model\Order')->search($searchCondition);
    }

    /**
     * Get profile items history
     *
     * @return array
     */
    public function getProfileBoughtItems()
    {
        $itemsList = [];

        foreach ($this->profileOrders as $order) {
            foreach ($order->getItems() as $item) {
                if (!in_array($item->getProductId(), $itemsList)) {
                    array_push($itemsList, $item->getProductId());
                }
            }
        }

        return $itemsList;
    }

    /**
     * @return int|null
     */
    public function getLastChangeDate()
    {
        return $this->getPasswordChangeDate() !== null ?
            $this->getPasswordChangeDate() :
            date(Helper::THREEDS_DATE_FORMAT, Helper::getProfile()->getAdded());
    }

    /**
     * Get the date when current shipping address is used for a first time
     *
     * @return string
     */
    public function getShippingAddressDateFirstUsed()
    {
        $firstUsageDate         = date(Helper::THREEDS_DATE_FORMAT, time());
        $orders                 = $this->profileOrders;
        $profileShippingAddress = $this->transaction->getOrder()->getProfile()->getShippingAddress();

        foreach ($orders as $order) {
            $orderShippingAddress = $order->getProfile()->getShippingAddress();
            if ($this->areAddressesSame($profileShippingAddress, $orderShippingAddress)) {
                $firstUsageDate = date(Helper::THREEDS_DATE_FORMAT, $order->getDate());
                break;
            }
        }

        return $firstUsageDate;
    }

    /**
     * Get Customer's first order date
     *
     * @return string|null
     */
    public function getProfileFirstOrderDate()
    {
        return (count($this->profileOrders) > 0)
            ? date(Helper::THREEDS_DATE_FORMAT, $this->profileOrders[0]->getDate())
            : null;
    }

    /**
     * @return array
     */
    private function getPreviousYear()
    {
        $previousYear = gmdate('Y', strtotime('-1 Year'));
        $dateFrom  = \DateTime::createFromFormat(Helper::THREEDS_DATE_FORMAT, "$previousYear-01-01 00:00:00");
        $dateTo    = \DateTime::createFromFormat(Helper::THREEDS_DATE_FORMAT, "$previousYear-12-31 23:59:59");

        return ['from' => $dateFrom, 'to' => $dateTo];
    }

    /**
     * Check if the given addresses are equal
     *
     * @param object $orderAddress
     * @param object $profileAddress
     *
     * @return bool
     */
    private function areAddressesSame($orderAddress, $profileAddress)
    {
        $result = false;

        if (null !== $orderAddress && null !== $profileAddress) {
            $result = true;

            if ($orderAddress->getAddressId() != $profileAddress->getAddressId()) {
                $addressFields = $orderAddress->getAvailableAddressFields();

                foreach ($addressFields as $name) {
                    $methodName = 'get' . \XLite\Core\Converter::getInstance()->convertToCamelCase($name);
                    // Compare field values of both addresses
                    if ($orderAddress->$methodName() != $profileAddress->$methodName()) {
                        $result = false;
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
