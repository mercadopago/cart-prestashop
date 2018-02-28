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

include dirname(__FILE__)."/includes/MPApi.php";

class MercadoPagoStandardReturnModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        parent::initContent();
		$checkout = Tools::getValue('checkout');
		$topic = Tools::getValue('topic');

        UtilMercadoPago::log("LOG", "===listenIPN postProcess checkout====".$checkout);
        UtilMercadoPago::log("LOG", "===listenIPN postProcess id====".Tools::getValue('id'));
        UtilMercadoPago::log("LOG", "===listenIPN postProcess topic====".$topic) ;
        
		if ($checkout == 'standard' && $topic == 'merchant_order') {
	        $this->listenIPN(
	            $checkout,
	            $topic,
	            Tools::getValue('id')
	        );
   		}
    }

    public function listenIPN($checkout, $topic, $id)
    {
        $payment_method_ids = array();
        $payment_ids = array();
        $payment_statuses = array();
        $payment_types = array();
        $credit_cards = array();
        $transaction_amounts = 0;
        $cardholders = array();
        $external_reference = '';
        $isMercadoEnvios = 0;

        try {
            if ($checkout == 'standard' && $topic == 'merchant_order' && $id > 0) {
                $mercadopago_sdk = MPApi::getInstanceMP();
                $mercadopago = $this->module;

                $result = $mercadopago_sdk->getMerchantOrder($id);
                UtilMercadoPago::log("LOG", Tools::jsonEncode($result));
                $merchant_order_info = $result['response'];
                // check value
                $cart = new Cart($merchant_order_info['external_reference']);
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
                    if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO' ||
                        Configuration::get('MERCADOPAGO_COUNTRY') == 'MLC') {
                        $transaction_amounts = $cart->getOrderTotal(true, Cart::BOTH);
                    }
                    if ($isMercadoEnvios ||
                        (isset($merchant_order_info['shipments']) &&
                        isset($merchant_order_info['shipments'][0]) &&
                        $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2')
                        ) {
                        $transaction_amounts += $merchant_order_info['shipments'][0]['shipping_option']['cost'];
                    }
                    $this->updateOrder(
                        $payment_ids,
                        $payment_statuses,
                        $payment_types,
                        $external_reference,
                        $result,
                        $checkout
                    );
                }
                // check the module
                $id_order = $mercadopago->getOrderByCartId($merchant_order_info['external_reference']);
                $order = new Order($id_order);
                $status_shipment = null;
                if (isset($merchant_order_info['shipments'][0]) &&
                    $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2' &&
                    ($merchant_order_info['shipments'][0]['status'] == "ready_to_ship" ||
                    $merchant_order_info['shipments'][0]['status'] == "shipped" ||
                    $merchant_order_info['shipments'][0]['status'] == "delivered")
                    ) {
                    $isMercadoEnvios = true;
                    $status_shipment = $merchant_order_info['shipments'][0]['status'];
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
                }
            }
        }catch(Exception $e){
            UtilMercadoPago::log("LOG exception", "===listenIPN postProcess checkout====". $e->getMessage());
        }        
    }

    private function updateOrder(
        $payment_ids,
        $payment_statuses,
        $payment_types,
        $external_reference,
        $result,
        $checkout
    ) {
        $order = null;
        // if has two creditcard validate whether payment has same status in order to continue validating order
        if (count($payment_statuses) == 1 ||
            (count($payment_statuses) == 2 &&
            $payment_statuses[0] == $payment_statuses[1])
        ) {
            $order = null;
            $payment_status = $payment_statuses[0];
            $payment_type = $payment_types[0];
            $mercadopago = $this->module;
            // just change if there is an order status
            $id_cart = $external_reference;
            $id_order = $mercadopago->getOrderByCartId($id_cart);
            $order = new Order($id_order);
            $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta[$payment_status]);
            if ($id_order) {
                if ($this->checkStateExist($id_order, $payment_status)) {
                    return;
                }
            }
            if ($payment_status == 'cancelled' || $payment_status == 'rejected') {
                if ($order->module == "mercadopago" || $checkout == 'pos') {
                    $retorno = $this->getOrderStateApproved($id_order);
                    if ($retorno) {
                        return;
                    }
                } else {
                    return;
                }
            }
            $statusPS = (int)$order->getCurrentState();
            if ($payment_status != $statusPS) {
                $order->setCurrentState($payment_status);
            }
            
            try {
                $payments = $order->getOrderPaymentCollection();
                $payments[0]->transaction_id = implode(' / ', $payment_ids);
                $payments[0]->update();
            } catch (Exception $e) {
                UtilMercadoPago::log("Exception", "vai atualizar " . $e->getMessage());
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
    public static function getOrderStateApproved($id_order)
    {
        return (bool) Db::getInstance()->getValue(
            '
        SELECT `id_order_state`
        FROM '._DB_PREFIX_.'order_history
        WHERE `id_order` = '.(int) $id_order.'
        AND `id_order_state` = '.
            (int) Configuration::get('MERCADOPAGO_STATUS_1')
        );
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
