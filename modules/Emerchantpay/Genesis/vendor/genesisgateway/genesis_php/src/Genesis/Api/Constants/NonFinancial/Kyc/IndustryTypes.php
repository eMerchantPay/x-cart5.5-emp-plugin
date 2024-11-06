<?php

/**
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author      emerchantpay
 * @copyright   Copyright (C) 2015-2024 emerchantpay Ltd.
 * @license     http://opensource.org/licenses/MIT The MIT License
 */

namespace Genesis\Api\Constants\NonFinancial\Kyc;

use Genesis\Utils\Common;

/**
 * Class IndustryTypes
 * @package Genesis\Api\Constants\NonFinancial\Kyc
 */
class IndustryTypes
{
    /**
     * Financial services
     *
     * @var int
     */
    const FINANCE = 1;

    /**
     * Gambling industry
     *
     * @var int
     */
    const GAMBLING = 2;

    /**
     * Crypto trading
     *
     * @var int
     */
    const CRYPTO = 3;

    /**
     * Travel
     *
     * @var int
     */
    const TRAVEL = 4;

    /**
     * Retail industry
     *
     * @var int
     */
    const RETAIL = 5;

    /**
     * Risk Vendor
     *
     * @var int
     */
    const RISK_VENDOR = 6;

    /**
     * Adult
     *
     * @var int
     */
    const ADULT = 7;

    /**
     * Remittance/Transfer
     *
     * @var int
     */
    const REMITTANCE_TRANSFER = 8;

    /**
     * Other
     *
     * @var int
     */
    const OTHER = 9;

    /**
     * @return array
     */
    public static function getAll()
    {
        return Common::getClassConstants(self::class);
    }
}
