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

namespace EMerchantPay\Genesis\View\FormField\Checkout\Select;

use Genesis\Api\Constants\Transaction\Parameters\ScaExemptions;

/**
 * Class ScaExemption
 * @package EMerchantPay\Genesis\View\FormField\Checkout\Select
 */
class ScaExemption extends \XLite\View\FormField\Select\Regular
{
    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            ScaExemptions::EXEMPTION_LOW_VALUE => static::t('Low value'),
            ScaExemptions::EXEMPTION_LOW_RISK  => static::t('Low risk'),
        ];
    }
}
