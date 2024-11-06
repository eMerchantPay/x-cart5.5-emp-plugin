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

namespace Emerchantpay\Genesis\Model\Payment\Processor;

use Genesis\Api\Constants\Payment\Methods;
use Genesis\Api\Constants\Transaction\Types;
use Genesis\Utils\Common as CommonUtils;
use XLite\Core\Session;
use Emerchantpay\Genesis\Helpers\Helper;
use Emerchantpay\Genesis\Helpers\ThreedsHelper;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\CardHolderAccount\RegistrationIndicators;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\MerchantRisk\DeliveryTimeframes;
use Genesis\Api\Constants\Transaction\Parameters\Threeds\V2\Purchase\Categories;
use Genesis\Genesis;
use Genesis\Api\Constants\Transaction\States;
use Exception;

/**
 * emerchantpay Checkout Payment Method
 *
 * @package Emerchantpay\Genesis\Model\Payment\Processor
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmerchantpayCheckout extends \Emerchantpay\Genesis\Model\Payment\Processor\AEmerchantpay
{
    /**
     * Payment method name
     */
    public const PAYMENT_METHOD_NAME = 'emerchantpay_checkout';

    /**
     * Create payment method transaction
     *
     * @return string $status Transaction Status
     */
    protected function doInitialPayment()
    {
        $status = static::FAILED;

        $this->initLibrary();

        try {
            $genesis = new Genesis('Wpf\Create');

            $data = $this->collectInitialPaymentData();

            $genesis
                ->request()
                    ->setTransactionId($data['transaction_id'])
                    ->setAmount($data['amount'])
                    ->setCurrency($data['currency'])
                    ->setUsage($data['usage'])
                    ->setDescription($data['description'])
                    ->setCustomerEmail($data['customer_email'])
                    ->setCustomerPhone($data['customer_phone'])
                    ->setNotificationUrl($data['notification_url'])
                    ->setReturnSuccessUrl($data['return_success_url'])
                    ->setReturnPendingUrl($data['return_success_url'])
                    ->setReturnFailureUrl($data['return_failure_url'])
                    ->setReturnCancelUrl($data['return_cancel_url'])
                    ->setBillingFirstName($data['billing']['first_name'])
                    ->setBillingLastName($data['billing']['last_name'])
                    ->setBillingAddress1($data['billing']['address1'])
                    ->setBillingAddress2($data['billing']['address2'])
                    ->setBillingZipCode($data['billing']['zip_code'])
                    ->setBillingCity($data['billing']['city'])
                    ->setBillingState($data['billing']['state'])
                    ->setBillingCountry($data['billing']['country'])
                    ->setShippingFirstName($data['shipping']['first_name'])
                    ->setShippingLastName($data['shipping']['last_name'])
                    ->setShippingAddress1($data['shipping']['address1'])
                    ->setShippingAddress2($data['shipping']['address2'])
                    ->setShippingZipCode($data['shipping']['zip_code'])
                    ->setShippingCity($data['shipping']['city'])
                    ->setShippingState($data['shipping']['state'])
                    ->setShippingCountry($data['shipping']['country']);

            $this->addTransactionTypesToGatewayRequest($genesis, $data['transaction_types']);

            if (in_array(Session::getInstance()->getLanguage()->getCode(), Helper::getSupportedWpfLanguages())) {
                $genesis->request()->setLanguage(
                    Session::getInstance()->getLanguage()->getCode()
                );
            }

            if ($this->getSetting('wpf_tokenization')) {
                $genesis->request()->setRememberCard(true);

                $consumerId = $this->getConsumerIdFromGenesisGateway($data['customer_email']);
                if ($consumerId !== 0) {
                    $genesis->request()->setConsumerId($consumerId);
                }
            }

            if ($this->getSetting('wpf_3dsv2_options')) {
                $this->addThreedsOptionalParameters($genesis);
            }

            $this->addScaExemptionParameters($genesis);

            $genesis->execute();

            $gatewayResponseObject = $genesis->response()->getResponseObject();

            if (!$genesis->response()->isSuccessful() || empty($gatewayResponseObject->redirect_url)) {
                $errorMessage =
                    isset($gatewayResponseObject->message)
                        ? $gatewayResponseObject->message
                        : '';

                throw new Exception($errorMessage);
            }

            $status = self::PROLONGATION;

            $this->redirectToURL($genesis->response()->getResponseObject()->redirect_url);
        } catch (\Exception $e) {
            $errorMessage = static::t(
                'Failed to initialize payment session, please contact support. '
                . $e->getMessage()
            );
            $this->transaction->setDataCell(
                'status',
                $errorMessage,
                null,
                static::FAILED
            );
            $this->transaction->setNote($errorMessage);
        }

        $this->setPaymentMethodName();

        return $status;
    }

    /**
     * @param \Genesis\Genesis $genesis
     * @param $transactionTypes
     * @throws \Exception
     */
    protected function addTransactionTypesToGatewayRequest(\Genesis\Genesis $genesis, $transactionTypes)
    {
        foreach ($transactionTypes as $transactionType) {
            if (is_array($transactionType)) {
                $genesis->request()->addTransactionType(
                    $transactionType['name'],
                    $transactionType['parameters']
                );

                continue;
            }

            $parameters = $this->getCustomRequiredParameters($transactionType);

            $genesis
                ->request()
                ->addTransactionType(
                    $transactionType,
                    $parameters
                );

            unset($parameters);
        }
    }

    /**
     * @param $transactionType
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getCustomRequiredParameters($transactionType)
    {
        $parameters = array();

        switch ($transactionType) {
            case \Genesis\Api\Constants\Transaction\Types::IDEBIT_PAYIN:
            case \Genesis\Api\Constants\Transaction\Types::INSTA_DEBIT_PAYIN:
                $parameters = array(
                    'customer_account_id' => Helper::getCurrentUserIdHash(
                        $this->transaction->getOrder()->getPaymentTransactionId()
                    )
                );
                break;
            case \Genesis\Api\Constants\Transaction\Types::INVOICE:
                $parameters = Helper::getInvoiceCustomParamItems(
                    $this->transaction->getOrder()
                )->toArray();
                break;
            case \Genesis\Api\Constants\Transaction\Types::TRUSTLY_SALE:
                $userId        = Helper::getCurrentUserId();
                $trustlyUserId = empty($userId) ?
                    Helper::getCurrentUserIdHash($this->transaction->getOrder()->getPaymentTransactionId()) : $userId;

                $parameters = array(
                    'user_id' => $trustlyUserId
                );
                break;
            case Types::ONLINE_BANKING_PAYIN:
                $selectedBankCodes = array_filter(
                    json_decode($this->getSetting('bank_codes')),
                    function ($value) {
                        return $value != 'none';
                    }
                );
                if (CommonUtils::isValidArray($selectedBankCodes)) {
                    $parameters['bank_codes'] = array_map(
                        function ($value) {
                            return ['bank_code' => $value];
                        },
                        $selectedBankCodes
                    );
                }
                break;
            case \Genesis\Api\Constants\Transaction\Types::PAYSAFECARD:
                $userId     = Helper::getCurrentUserId();
                $customerId = empty($userId) ?
                     Helper::getCurrentUserIdHash($this->transaction->getOrder()->getPaymentTransactionId()) : $userId;

                $parameters = array(
                    'customer_id' => $customerId
                );
                break;
        }

        return $parameters;
    }

    /**
     * Use Genesis API to get consumer ID
     *
     * @param string $email Consumer Email
     *
     * @return int
     */
    protected static function getConsumerIdFromGenesisGateway($email)
    {
        try {
            $genesis = new Genesis('NonFinancial\Consumers\Retrieve');
            $genesis->request()->setEmail($email);

            $genesis->execute();

            $response = $genesis->response()->getResponseObject();

            if (static::isErrorResponse($response)) {
                return 0;
            }

            return intval($response->consumer_id);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    /**
     * Checks if Genesis response is an error
     *
     * @param \stdClass $response Genesis response
     *
     * @return bool
     */
    protected static function isErrorResponse($response)
    {
        $state = new States($response->status);

        return $state->isError();
    }

    /**
     * Before Capture transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return void
     */
    public function doBeforeCapture(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        // Set the token
        $this->setTerminalToken($transaction);
    }

    /**
     * Before Refund transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return void
     */
    public function doBeforeRefund(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        // Set the token
        $this->setTerminalToken($transaction);
    }

    /**
     * Before Void transaction
     *
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @return void
     */
    public function doBeforeVoid(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        // Set the token
        $this->setTerminalToken($transaction);
    }

    /**
     * Process Genesis Reconciliation Object
     *
     * @param \XLite\Model\Payment\Transaction $transaction
     * @param \stdClass $reconcile
     *
     * @return void
     */
    protected function doProcessCallbackReconciliationObject(
        \XLite\Model\Payment\Transaction $transaction,
        \stdClass $reconcile
    ) {
        if (isset($reconcile->payment_transaction)) {
            $payment = $reconcile->payment_transaction;

            $payment_status = $this->getTransactionStatus($payment);

            // Backend transaction
            if (!$transaction->getInitialBackendTransaction()) {
                $transaction->createBackendTransaction(
                    $this->getTransactionType($payment)
                );
            }

            $backendTransaction = $transaction->getInitialBackendTransaction();

            /// Set BackendTransaction Status
            $backendTransaction->setStatus($payment_status);

            /// Set BackendTransaction Data
            $this->updateTransactionData($backendTransaction, $payment);

            $backendTransaction->update();

            // Payment transaction (Customer)
            $order_status = $this->getPaymentStatus($payment);

            /// Set Order Status (authorize/capture/sale)
            if ($transaction->getOrder()->getStatus() != $order_status) {
                $transaction->getOrder()->setPaymentStatus($order_status);
            }

            /// Set PaymentTransaction Data
            $this->updateInitialPaymentTransaction($transaction, $reconcile);

            /// Set PaymentTransaction Type (auth/capture)
            $transaction->setType(
                $this->getTransactionType($payment)
            );
        }

        $transaction_status = $this->getTransactionStatus($reconcile);

        /// Set PaymentTransaction Status (S/W)
        if ($transaction->getStatus() != $transaction_status) {
            $transaction->setStatus($transaction_status);
            // Workaround for Checkout status 'PENDING'
            /*
            if ($transaction_status != \XLite\Model\Payment\Transaction::STATUS_PENDING) {
                $transaction->setStatus($transaction_status);
            } else {
                if (isset($payment_status)) {
                    $transaction->setStatus($payment_status);
                }
            }
            */
        }

        $transaction->registerTransactionInOrderHistory('Notification, Genesis');

        $transaction->update();
    }

    /**
     * @param \XLite\Model\Payment\BackendTransaction $transaction
     *
     * @throws \Genesis\Exceptions\DeprecatedMethod
     * @throws \Genesis\Exceptions\ErrorParameter
     * @throws \Genesis\Exceptions\InvalidArgument
     * @throws \Genesis\Exceptions\InvalidClassMethod
     * @throws \Genesis\Exceptions\InvalidMethod
     * @throws \Genesis\Exceptions\InvalidResponse
     */
    protected function setTerminalToken(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        $token = $transaction->getPaymentTransaction()->getDataCell(self::REF_TKN);

        if (!isset($token)) {
            $unique_id = $transaction->getPaymentTransaction()->getDataCell(self::REF_UID);

            if (isset($unique_id)) {
                $reconcile = new Genesis('Wpf\Reconcile');

                $reconcile->request()->setUniqueId($unique_id->getValue());

                $reconcile->execute();

                if ($reconcile->response()->isSuccessful()) {
                    $token = $reconcile->response()->getResponseObject()->payment_transaction->terminal_token;
                }
            }
        } else {
            $token = $token->getValue();
        }

        \Genesis\Config::setToken(trim($token));
    }

    /**
     * Get all the data required by the Gateway
     *
     * @return array
     */
    protected function collectInitialPaymentData()
    {
        $data = parent::collectInitialPaymentData();

        if ($this->getSetting('transaction_types')) {
            $types = array(
                'transaction_types' => $this->getCheckoutTransactionTypes()
            );
        } else {
            // Fallback to authorize
            $types = array(
                'transaction_types' => array(
                    Types::AUTHORIZE,
                    Types::AUTHORIZE_3D,
                    Types::SALE,
                    Types::SALE_3D
                )
            );
        }

        $data = array_merge($data, $types);

        return $data;
    }

    /**
     * Get the selected Checkout transaction types
     *
     * @return array
     */
    protected function getCheckoutTransactionTypes()
    {
        $processedList = array();
        $aliasMap      = array();

        $selectedTypes = $this->orderCardTransactionTypes(
            json_decode(
                $this->getSetting('transaction_types')
            )
        );

        $aliasMap = [
            Helper::GOOGLE_PAY_TRANSACTION_PREFIX . Helper::GOOGLE_PAY_PAYMENT_TYPE_AUTHORIZE => Types::GOOGLE_PAY,
            Helper::GOOGLE_PAY_TRANSACTION_PREFIX . Helper::GOOGLE_PAY_PAYMENT_TYPE_SALE      => Types::GOOGLE_PAY,
            Helper::PAYPAL_TRANSACTION_PREFIX     . Helper::PAYPAL_PAYMENT_TYPE_AUTHORIZE     => Types::PAY_PAL,
            Helper::PAYPAL_TRANSACTION_PREFIX     . Helper::PAYPAL_PAYMENT_TYPE_SALE          => Types::PAY_PAL,
            Helper::PAYPAL_TRANSACTION_PREFIX     . Helper::PAYPAL_PAYMENT_TYPE_EXPRESS       => Types::PAY_PAL,
            Helper::APPLE_PAY_TRANSACTION_PREFIX  . Helper::APPLE_PAY_PAYMENT_TYPE_AUTHORIZE  => Types::APPLE_PAY,
            Helper::APPLE_PAY_TRANSACTION_PREFIX  . Helper::APPLE_PAY_PAYMENT_TYPE_SALE       => Types::APPLE_PAY,
        ];

        foreach ($selectedTypes as $selectedType) {
            if (array_key_exists($selectedType, $aliasMap)) {
                $transactionType = $aliasMap[$selectedType];

                $processedList[$transactionType]['name'] = $transactionType;

                $key = $this->getCustomParameterKey($transactionType);

                $processedList[$transactionType]['parameters'][] = array(
                    $key => str_replace(
                        [
                            Helper::GOOGLE_PAY_TRANSACTION_PREFIX,
                            Helper::PAYPAL_TRANSACTION_PREFIX,
                            Helper::APPLE_PAY_TRANSACTION_PREFIX,
                        ],
                        '',
                        $selectedType
                    )
                );
            } else {
                $processedList[] = $selectedType;
            }
        }

        return $processedList;
    }

    /**
     * Get the Checkout Template Path
     *
     * $param \XLite\Model\Payment\Method $method
     *
     * @return string|null
     */
    public function getCheckoutTemplate(\XLite\Model\Payment\Method $method)
    {
        if ($this->isCoreVersion52()) {
            return parent::getCheckoutTemplate($method) . 'emerchantpayCheckout.tpl';
        } elseif ($this->isCoreAboveVersion53()) {
            return parent::getCheckoutTemplate($method) . 'emerchantpayCheckout.twig';
        }

        return null;
    }

    /**
     * @param $transactionType
     * @return string
     */
    private function getCustomParameterKey($transactionType)
    {
        switch ($transactionType) {
            case Types::PAY_PAL:
                $result = 'payment_type';
                break;
            case Types::GOOGLE_PAY:
            case Types::APPLE_PAY:
                $result = 'payment_subtype';
                break;
            default:
                $result = 'unknown';
        }

        return $result;
    }

    /**
     * @param $genesis
     *
     * @throws \Exception
     */
    protected function addThreedsOptionalParameters($genesis)
    {
        $threeds = new ThreedsHelper($this->transaction);

        $genesis
            ->request()
            // Challenge Indicator
            ->setThreedsV2ControlChallengeIndicator(
                $this->getSetting('challenge_indicator')
            )
            // Purchase
            ->setThreedsV2PurchaseCategory(
                $threeds->hasPhysicalProduct() ?
                    Categories::GOODS :
                    Categories::SERVICE
            )
            // Merchant_risk
            ->setThreedsV2MerchantRiskShippingIndicator($threeds->fetchShippingIndicator())
            ->setThreedsV2MerchantRiskDeliveryTimeframe(
                $threeds->hasPhysicalProduct() ?
                    DeliveryTimeframes::ANOTHER_DAY :
                    DeliveryTimeframes::ELECTRONICS
            )
            ->setThreedsV2MerchantRiskReorderItemsIndicator($threeds->fetchReorderItemsIndicator());

        if (!Helper::isGuestCustomer()) {
            // CardHolder Account
            $genesis->request()
                    ->setThreedsV2CardHolderAccountCreationDate(Helper::getCustomerCreatedAt())
                    ->setThreedsV2CardHolderAccountUpdateIndicator($threeds->fetchUpdateIndicator())
                    ->setThreedsV2CardHolderAccountLastChangeDate($threeds->getLastChangeDate())
                    ->setThreedsV2CardHolderAccountPasswordChangeIndicator($threeds->fetchPasswordChangeIndicator())
                    ->setThreedsV2CardHolderAccountPasswordChangeDate($threeds->getPasswordChangeDate())
                    ->setThreedsV2CardHolderAccountShippingAddressUsageIndicator(
                        $threeds->fetchShippingAddressUsageIndicator()
                    )
                    ->setThreedsV2CardHolderAccountShippingAddressDateFirstUsed(
                        $threeds->getShippingAddressDateFirstUsed()
                    )
                    ->setThreedsV2CardHolderAccountTransactionsActivityLast24Hours(
                        $threeds->countOrdersPeriod(ThreedsHelper::ACTIVITY_24_HOURS)
                    )
                    ->setThreedsV2CardHolderAccountTransactionsActivityPreviousYear(
                        $threeds->countOrdersPeriod(ThreedsHelper::ACTIVITY_1_YEAR)
                    )
                    ->setThreedsV2CardHolderAccountPurchasesCountLast6Months(
                        $threeds->countOrdersPeriod(ThreedsHelper::ACTIVITY_6_MONTHS)
                    )
                    ->setThreedsV2CardHolderAccountRegistrationDate(
                        $threeds->getProfileFirstOrderDate()
                    );
        }

        $genesis
            ->request()
            ->setThreedsV2CardHolderAccountRegistrationIndicator(
                Helper::isGuestCustomer() ? RegistrationIndicators::GUEST_CHECKOUT :
                    $threeds->fetchRegistrationIndicator()
            );
    }

    /*
     * Add SCA Exemption parameter to Genesis Request
     *
     * @param $genesis
     */
    private function addScaExemptionParameters($genesis)
    {
        $wpfAmount         = (float) $genesis->request()->getAmount();
        $scaExemption      = $this->getSetting(self::SETTING_KEY_SCA_EXEMPTION);
        $scaExemptionValue = (float) $this->getSetting(self::SETTING_KEY_SCA_EXEMPTION_AMOUNT);

        if ($wpfAmount <= $scaExemptionValue) {
            $genesis->request()->setScaExemption($scaExemption);
        }
    }

    /**
     * Set payment method name during transaction
     */
    private function setPaymentMethodName()
    {
        $this->transaction->getOrder()->setPaymentMethodName(self::PAYMENT_METHOD_NAME);
    }

    /**
     * Order transaction types with Card Transaction types in front
     *
     * @param array $selected_types Selected transaction types
     * @return array
     */
    private function orderCardTransactionTypes($selected_types)
    {
        $custom_order = \Genesis\Api\Constants\Transaction\Types::getCardTransactionTypes();

        asort($selected_types);

        $sorted_array = array_intersect($custom_order, $selected_types);

        return array_merge($sorted_array, array_diff($selected_types, $sorted_array));
    }
}
