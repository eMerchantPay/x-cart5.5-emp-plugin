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

namespace EMerchantPay\Genesis\Controller\Customer;

use XCart\Extender\Mapping\Extender;

/**
 * @Extender\Mixin
 */
class PaymentReturn extends \XLite\Controller\Customer\PaymentReturn
{
    /**
     * Process return
     *
     * @return void
     */
    protected function doActionReturn()
    {
        $txn = $this->detectTransaction();

        if (
            $txn
            && $txn->getPaymentMethod()
            && $txn->getPaymentMethod()->getProcessor()
            && $txn->getPaymentMethod()->getProcessor() instanceof \XLite\Model\Payment\Base\Online
        ) {
            $urlParams = [];
            $urlParams['order_id'] = $txn->getOrder()->getOrderId();

            if ($txn->getNote()) {
                $urlParams['txnNote'] = base64_encode(static::t($txn->getNote()));
                $urlParams['txnNoteType'] = $txn->isFailed()
                    ? \XLite\Core\TopMessage::ERROR
                    : \XLite\Core\TopMessage::INFO;
            }

            \XLite\Core\Session::getInstance()->iframeReturnUrl = $this->getCheckoutReturnURL($txn, $urlParams);
        }

        parent::doActionReturn();
    }
}
