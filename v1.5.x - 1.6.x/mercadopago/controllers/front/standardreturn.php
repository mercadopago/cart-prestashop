<?php
/**
 * 2007-2015 PrestaShop
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
 *  @author    ricardobrito
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */
include_once (dirname ( __FILE__ ) . '/../../mercadopago.php');
class MercadoPagoStandardReturnModuleFrontController extends ModuleFrontController {
    public function initContent() {
        parent::initContent ();
        if (Tools::getIsset ( 'collection_id' ) && Tools::getValue ( 'collection_id' ) != 'null') {
            // payment variables
            $payment_statuses = array ();
            $payment_ids = array ();
            $payment_types = array ();
            $payment_method_ids = array ();
            $card_holder_names = array ();
            $four_digits_arr = array ();
            $statement_descriptors = array ();
            $status_details = array ();
            $transaction_amounts = 0;
            $collection_ids = explode ( ',', Tools::getValue ( 'collection_id' ) );
            
            $mercadopago = $this->module;
            $mercadopago_sdk = $mercadopago->mercadopago;
            foreach ( $collection_ids as $collection_id ) {
                $result = $mercadopago_sdk->getPayment ( $collection_id );
                $payment_info = $result ['response'];
                $id_cart = $payment_info ['external_reference'];
                $cart = new Cart ( $id_cart );
                
                $payment_statuses [] = $payment_info ['status'];
                $payment_ids [] = $payment_info ['id'];
                $payment_types [] = $payment_info ['payment_type_id'];
                
                if (isset ( $payment_info ['payment_method_id'] )) {
                    $payment_method_ids [] = $payment_info ['payment_method_id'];
                }
                
                $transaction_amounts += $payment_info ['transaction_amount'];
                
                if (isset ( $payment_info ['payment_type_id'] ) && $payment_info ['payment_type_id'] == 'credit_card') {
                    $card_holder_names [] = $payment_info ['card']['cardholder'] ['name'];
                    if (isset ( $payment_info ['card']['last_four_digits'] )) {
                        $four_digits_arr [] = '**** **** **** ' . $payment_info ['card']['last_four_digits'];
                    }
                    
                    $statement_descriptors [] = $payment_info ['statement_descriptor'];
                    $status_details [] = $payment_info ['status_detail'];
                }
            }
            if (Validate::isLoadedObject ( $cart )) {
                
                $total = ( double ) number_format ( $transaction_amounts, 2, '.', '' );
                
                $extra_vars = array (
                        '{bankwire_owner}' => $mercadopago->textshowemail,
                        '{bankwire_details}' => '',
                        '{bankwire_address}' => '' 
                );
                $order_status = null;
                $payment_status = $payment_info ['status'];
                switch ($payment_status) {
                    case 'in_process' :
                        $order_status = 'MERCADOPAGO_STATUS_0';
                        break;
                    case 'approved' :
                        $order_status = 'MERCADOPAGO_STATUS_1';
                        break;
                    case 'pending' :
                        $order_status = 'MERCADOPAGO_STATUS_7';
                        break;
                }
                $order_id = Order::getOrderByCartId ( $cart->id );
                if ($order_status != null) {
                    
                    if (! $order_id) {
                        $mercadopago->validateOrder ( $cart->id, Configuration::get ( $order_status ), $total, $mercadopago->displayName, null, $extra_vars, $cart->id_currency, false, $cart->secure_key );
                    }
                    $order_id = ! $order_id ? Order::getOrderByCartId ( $cart->id ) : $order_id;
                    $order = new Order ( $order_id );
                    
                    $uri = __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $order->id_cart . '&id_module=' . $mercadopago->id . '&id_order=' . $order->id . '&key=' . $order->secure_key;
                    $order_payments = $order->getOrderPayments ();
                    $order_payments [0]->transaction_id = Tools::getValue ( 'collection_id' );
                    $uri .= '&payment_status=' . $payment_statuses [0];
                    $uri .= '&payment_id=' . join ( " / ", $payment_ids );
                    $uri .= '&payment_type=' . join ( " / ", $payment_types );
                    $uri .= '&payment_method_id=' . join ( " / ", $payment_method_ids );
                    $uri .= '&amount=' . $transaction_amounts;
                    if ($payment_info ['payment_type_id'] == 'credit_card') {
                        $uri .= '&card_holder_name=' . join ( " / ", $card_holder_names );
                        $uri .= '&four_digits=' . join ( " / ", $four_digits_arr );
                        $uri .= '&statement_descriptor=' . $statement_descriptors [0];
                        $uri .= '&status_detail=' . $status_details [0];
                        $order_payments [0]->card_number = empty ( $four_digits_arr ) ? "" : join ( " / ", $four_digits_arr );
                        $order_payments [0]->card_brand = empty ( $payment_method_ids ) ? "" : join ( " / ", $payment_method_ids );
                        $order_payments [0]->card_holder = join ( " / ", $card_holder_names );
                    }
                    $order_payments [0]->save ();
                    Tools::redirectLink ( $uri );
                }
            }
        } else {
            PrestaShopLogger::addLog ( "MercadoPagoStandardReturnModuleFrontController::initContent = " . 'External reference is not set. Order placement has failed.', MP_SDK::ERROR, 0 );
        }
    }
}
?>
