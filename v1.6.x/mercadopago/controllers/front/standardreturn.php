<?php

/**
 * 2007-2015 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    henriqueleite
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */
class MercadoPagoStandardReturnModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        error_log("ENTROU NO RETORNO da IPN");
        error_log("checkout=====".Tools::getValue('checkout'));
        error_log("topic=====".Tools::getValue('topic'));
		$checkout = Tools::getValue('checkout');
		$topic = Tools::getValue('topic');
		if ($checkout == 'standard' && $topic == 'merchant_order') {
            error_log("====notification id ==== ".Tools::getValue('id'));
	        $this->listenIPN(
	            $checkout,
	            $topic,
	            Tools::getValue('id')
	        );
   		}
    }

    public function listenIPN($checkout, $topic, $id)
    {
        $mercadopago_sdk = MPApi::getInstanceMP();
		$mercadopago = $this->module;
        $payment_method_ids = array();
        $payment_ids = array();
        $payment_statuses = array();
        $payment_types = array();
        $credit_cards = array();
        $transaction_amounts = 0;
        $cardholders = array();
        $external_reference = '';
        $cost_mercadoEnvios = 0;
        $isMercadoEnvios = 0;

        $result = $mercadopago_sdk->getMerchantOrder($id);
        $merchant_order_info = $result['response'];
        if (isset($merchant_order_info['status']) && $merchant_order_info['status'] == 404) {
        	error_log("==return==merchant_order_info===". Tools::jsonEncode($merchant_order_info ));
        	return;
        }
        error_log("====merchant_order_info===". Tools::jsonEncode($merchant_order_info ));
        // check value
        $cart = new Cart($merchant_order_info['external_reference']);
        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO') {
            $total = floor($cart->getOrderTotal(true, Cart::BOTH));

            error_log("vai formatar  total  ". floor($cart->getOrderTotal(true, Cart::BOTH)));
        } else {
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        }

        // check the module
        $id_order = $mercadopago->getOrderByCartId($merchant_order_info['external_reference']);
        $order = new Order($id_order);
        $total_amount = $merchant_order_info['total_amount'];

        error_log("entrou no retorno total=". $total);
        error_log("entrou no retorno total_amount=".$total_amount);

        if ($total_amount != $total) {
            PrestaShopLogger::addLog('MercadoPago :: listenIPN - Não atualizou o pedido, valores diferentes'.
            ' id = '.$id, MPApi::INFO, 0);

            error_log("entrou no retorno=");
            error_log("entrou no retorno=");
            return;
        }
        $status_shipment = null;
        if (isset($merchant_order_info['shipments'][0]) &&
            $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2') {
            $isMercadoEnvios = true;
            $cost_mercadoEnvios = $merchant_order_info['shipments'][0]['shipping_option']['cost'];

            $status_shipment = $merchant_order_info['shipments'][0]['status'];

            $id_order = $mercadopago->getOrderByCartId($merchant_order_info['external_reference']);
            $order = new Order($id_order);
            $order_status = null;
            switch ($status_shipment) {
                case 'ready_to_ship':
                    $order_status = 'MERCADOPAGO_STATUS_8';
                    break;
                case 'shipped':
                    $order_status = 'MERCADOPAGO_STATUS_9';
                    break;
                case 'delivered':
                    $order_status = 'MERCADOPAGO_STATUS_10';
                    break;
            }
            if ($order_status != null) {
                $existStates = $this->checkStateExist($id_order, Configuration::get($order_status));
                if ($existStates) {
                    return;
                }
                $this->updateOrderHistory($order->id, Configuration::get($order_status));
            }

            return;
        }
        $payments = $merchant_order_info['payments'];
        $external_reference = $merchant_order_info['external_reference'];
        foreach ($payments as $payment) {
            // get payment info
            $result = $mercadopago_sdk->getPaymentStandard($payment['id']);
            $payment_info = $result['response']['collection'];
            // colect payment details
            $payment_ids[] = $payment_info['id'];
            $payment_statuses[] = $payment_info['status'];
            $payment_types[] = $payment_info['payment_type'];
            $transaction_amounts += $payment_info['transaction_amount'];
            if ($payment_info['payment_type'] == 'credit_card') {
                $payment_method_ids[] = isset($payment_info['payment_method_id']) ?
                                        $payment_info['payment_method_id'] : '';
                $credit_cards[] = isset($payment_info['card']['last_four_digits']) ?
                                        '**** **** **** '.$payment_info['card']['last_four_digits'] : '';
                $cardholders[] = isset($payment_info['card']['cardholder']['name']) ?
                                $payment_info['card']['cardholder']['name'] : '';
            }
        }

        if ($merchant_order_info['total_amount'] == $transaction_amounts) {
            if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO') {
                $transaction_amounts = $cart->getOrderTotal(true, Cart::BOTH);
            }
            $this->updateOrder(
                $payment_ids,
                $payment_statuses,
                $payment_method_ids,
                $payment_types,
                $credit_cards,
                $cardholders,
                $transaction_amounts,
                $external_reference,
                $result
            );
        }
    }

    private function updateOrder(
        $payment_ids,
        $payment_statuses,
        $payment_method_ids,
        $payment_types,
        $credit_cards,
        $cardholders,
        $transaction_amounts,
        $external_reference,
        $result
    ) {
        $order = null;
		$mercadopago = $this->module;
        // if has two creditcard validate whether payment has same status in order to continue validating order
        if (count($payment_statuses) == 1 ||
             (count($payment_statuses) == 2 && $payment_statuses[0] == $payment_statuses[1])) {
            $order = null;
            $order_status = null;
            $payment_status = $payment_statuses[0];
            $payment_type = $payment_types[0];

            switch ($payment_status) {
                case 'in_process':
                    $order_status = 'MERCADOPAGO_STATUS_0';
                    break;
                case 'approved':
                    $order_status = 'MERCADOPAGO_STATUS_1';
                    break;
                case 'cancelled':
                    $order_status = 'MERCADOPAGO_STATUS_2';
                    break;
                case 'refunded':
                    $order_status = 'MERCADOPAGO_STATUS_4';
                    break;
                case 'charged_back':
                    $order_status = 'MERCADOPAGO_STATUS_5';
                    break;
                case 'in_mediation':
                    $order_status = 'MERCADOPAGO_STATUS_6';
                    break;
                case 'pending':
                    $order_status = 'MERCADOPAGO_STATUS_7';
                    break;
                case 'rejected':
                    $order_status = 'MERCADOPAGO_STATUS_3';
                    break;
                case 'ready_to_ship':
                    $order_status = 'MERCADOPAGO_STATUS_8';
                    break;
                case 'shipped':
                    $order_status = 'MERCADOPAGO_STATUS_9';
                    break;
                case 'delivered':
                    $order_status = 'MERCADOPAGO_STATUS_10';
                    break;
            }
            // just change if there is an order status
            if ($order_status) {
                $id_cart = $external_reference;
                $id_order = $mercadopago->getOrderByCartId($id_cart);
                if ($id_order) {
                    $order = new Order($id_order);

                    $existStates = $this->checkStateExist(
                        $id_order,
                        Configuration::get($order_status)
                    );
                    if ($existStates) {
                        return;
                    }
                }
                // If order wasn't created yet and payment is approved or pending or in_process, create it.
                // This can happen when user closes checkout standard
                if (empty($id_order) && ($payment_status == 'in_process' || $payment_status == 'approved' ||
                    $payment_status == 'pending')
                    ) {
                    $cart = new Cart($id_cart);
                    $total = (double) number_format($transaction_amounts, 2, '.', '');
                    $extra_vars = array(
                        '{bankwire_owner}' => $this->l('You must follow MercadoPago rules for purchase to be valid'),
                        '{bankwire_details}' => '',
                        '{bankwire_address}' => '',
                    );
                    $id_order = !$id_order ? $mercadopago->getOrderByCartId($id_cart) : $id_order;
                    $order = new Order($id_order);
                    $existStates = $this->checkStateExist($id_order, Configuration::get($order_status));
                    if ($existStates) {
                        return;
                    }

                    $displayName = UtilMercadoPago::setNamePaymentType($payment_type);

                    $this->module->validateOrder(
                        $id_cart,
                        Configuration::get($order_status),
                        $total,
                        $displayName,
                        null,
                        $extra_vars,
                        $cart->id_currency,
                        false,
                        $cart->secure_key
                    );
                } elseif (!empty($order) && $order->current_state != null &&
                     $order->current_state != Configuration::get($order_status)) {
                    $id_order = !$id_order ? $mercadopago->getOrderByCartId($id_cart) : $id_order;
                    $order = new Order($id_order);
                    /*
                     * this is necessary to ignore the transactions with the same
                     * external reference and states diferents
                     * the transaction approved cant to change the status, except refunded.
                     */
                    if ($payment_status == 'cancelled' || $payment_status == 'rejected') {
                        // check if is mercadopago
                        if ($order->module == "mercadopago") {
                            $retorno = $this->getOrderStateApproved($id_order);
                            if ($retorno) {
                                return;
                            }
                        } else {
                            return;
                        }
                    }
                    $this->updateOrderHistory($order->id, Configuration::get($order_status));

                    // Cancel the order to force products to go to stock.
                    switch ($payment_status) {
                        case 'cancelled':
                        case 'refunded':
                        case 'rejected':
                            $this->updateOrderHistory($id_order, Configuration::get('PS_OS_CANCELED'), false);
                            break;
                    }
                }
                if ($order) {
                    // update order payment information
                    $order_payments = $order->getOrderPayments();
                    foreach ($order_payments as $order_payment) {
                        $order_payment->transaction_id = implode(' / ', $payment_ids);
                        if ($payment_type == 'credit_card') {
                            $order_payment->card_number = implode(' / ', $credit_cards);
                            $order_payment->card_brand = implode(' / ', $payment_method_ids);
                            $order_payment->card_holder = implode(' / ', $cardholders);
                        }
                        $order_payment->save();
                    }
                }
            }
        }
    }

    public function updateOrderHistory($id_order, $status, $mail = true)
    {
        // Change order state and send email
        $history = new OrderHistory();
        $history->id_order = (integer) $id_order;
        $history->changeIdOrderState((integer) $status, (integer) $id_order, true);
        if ($mail) {
            $extra_vars = array();
            $history->addWithemail(true, $extra_vars);
        }
    }

    /**
     * Verify if there is state approved for order.
     */
    public static function checkStateExist($id_order, $id_order_state)
    {
        return (bool) Db::getInstance()->getValue(
            '
        SELECT `id_order_state`
        FROM '._DB_PREFIX_.'order_history
        WHERE `id_order` = '.(int) $id_order.'
        AND `id_order_state` = '.
            (int) $id_order_state
        );
    }
}
