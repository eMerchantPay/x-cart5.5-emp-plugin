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

namespace Emerchantpay\Genesis\View\Model;

/**
 * Settings Page Definition
 */
class EmerchantpayCheckout extends \Emerchantpay\Genesis\View\Model\AEmerchantpay
{
    /**
     * Save current form reference and initialize the cache
     *
     * @param array $params   Widget params OPTIONAL
     * @param array $sections Sections list OPTIONAL
     */
    public function __construct(array $params = array(), array $sections = array())
    {
        parent::__construct($params, $sections);

        $this->schemaAdditional['transaction_types'] = array(
            self::SCHEMA_CLASS    =>
                '\Emerchantpay\Genesis\View\FormField\Checkout\Select\TransactionTypes',
            self::SCHEMA_LABEL    => 'Transaction types',
            self::SCHEMA_HELP     =>
                'You can select which transaction types can be attempted (from the Gateway) upon customer processing',
            self::SCHEMA_REQUIRED => true,
        );
        $this->schemaAdditional['bank_codes'] = array(
            self::SCHEMA_CLASS    =>
                '\Emerchantpay\Genesis\View\FormField\Checkout\Select\BankCodes',
            self::SCHEMA_LABEL    => 'Bank codes for Online banking',
            self::SCHEMA_HELP     =>
                'You can select one or more Bank codes for Online Banking transaction type.',
            self::SCHEMA_REQUIRED => true,
        );
        $this->schemaAdditional['wpf_tokenization'] = array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Select\EnabledDisabled',
            self::SCHEMA_LABEL    => 'Tokenization Enabled',
            self::SCHEMA_HELP     =>
                'Is Tokenization going to be used for the Web Payment Form?',
            self::SCHEMA_REQUIRED => false,
        );
        $this->schemaAdditional['wpf_3dsv2_options'] = array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Select\EnabledDisabled',
            self::SCHEMA_LABEL    => '3DSv2 parameters handling',
            self::SCHEMA_HELP     =>
                'Enable/Disable handling of 3DSv2 optional parameters',
            self::SCHEMA_REQUIRED => false,
        );
        $this->schemaAdditional['challenge_indicator'] = array(
            self::SCHEMA_CLASS    =>
                '\Emerchantpay\Genesis\View\FormField\Checkout\Select\ChallengeIndicator',
            self::SCHEMA_LABEL    => '3DSv2 Challenge option',
            self::SCHEMA_HELP     =>
                'The value has weight and might impact the decision whether' .
                'a challenge will be required for the transaction or not.' .
                ' If not provided, it will be interpreted as no_preference.',
            self::SCHEMA_REQUIRED => false,
        );
        $this->schemaAdditional['sca_exemption'] = array(
            self::SCHEMA_CLASS    =>
                '\Emerchantpay\Genesis\View\FormField\Checkout\Select\ScaExemption',
            self::SCHEMA_LABEL    => 'SCA Exemption option',
            self::SCHEMA_HELP     =>
                'SCA Exemption for Strong Customer Authentication',
            self::SCHEMA_REQUIRED => false,
        );
        $this->schemaAdditional['sca_exemption_amount'] = array(
            self::SCHEMA_CLASS    => '\XLite\View\FormField\Input\Text',
            self::SCHEMA_LABEL    => 'SCA Exemption amount option',
            self::SCHEMA_HELP     =>
                'The exemption amounts to determine' .
                ' if the SCA Exemption should be included in the request to the Gateway.',
            self::SCHEMA_REQUIRED => false,
        );
    }
}
