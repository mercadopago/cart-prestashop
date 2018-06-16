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

class MercadoPagoValidationStandardModuleFrontController extends ModuleFrontController
{
    protected $orderConfirmationUrl = 'index.php?controller=order-confirmation';

    public function initContent()
    {


        parent::initContent();
    

        if (Tools::getValue('typeReturn') == 'failure') {
            $this->redirectError();
        }
        if (Tools::getIsset('collection_id') && Tools::getValue('collection_id') != 'null') {
            // payment variables
            $payment_statuses = array();
            $payment_ids = array();
            $payment_types = array();
            $payment_method_ids = array();
            $card_holder_names = array();
            $four_digits_arr = array();
            $statement_descriptors = array();
            $status_details = array();
            $transaction_amounts = 0;
            $collection_ids = explode(',', Tools::getValue('collection_id'));

            $merchant_order_id = Tools::getValue('merchant_order_id');

            $mercadopago = $this->module;
            $mercadopago_sdk = MPApi::getInstanceMP();
            foreach ($collection_ids as $collection_id) {
                $result = $mercadopago_sdk->getPaymentStandard($collection_id);
                if ($result['status'] != 200) {
                    continue;
                }

                $payment_info = $result['response'];
                $id_cart = $payment_info['external_reference'];
                $cart = new Cart($id_cart);
                $payment_statuses[] = $payment_info['status'];
                $payment_ids[] = $payment_info['id'];
                $payment_types[] = $payment_info['payment_type_id'];

                if (isset($payment_info['payment_method_id'])) {
                    $payment_method_ids[] = $payment_info['payment_method_id'];
                }

                $transaction_amounts += $payment_info['transaction_amount'];

                if (isset($payment_info['payment_type_id']) && $payment_info['payment_type_id'] == 'credit_card') {
                    $card_holder_names[] = isset($payment_info['card']['cardholder']['name'])
                    ? $payment_info['card']['cardholder']['name'] : '';
                    if (isset($payment_info['card']['last_four_digits'])) {
                        $four_digits_arr[] = '**** **** **** '.$payment_info['card']['last_four_digits'];
                    }
                    $statement_descriptors[] = $payment_info['statement_descriptor'];
                    $status_details[] = $payment_info['status_detail'];
                }
            }

            if (Validate::isLoadedObject($cart)) {
                $total = $cart->getOrderTotal(true, Cart::BOTH);
                $order_status = null;
                $payment_status = $payment_info['status'];
                switch ($payment_status) {
                    case 'in_process':
                        $order_status = 'MERCADOPAGO_STATUS_0';
                        break;
                    case 'approved':
                        $order_status = 'MERCADOPAGO_STATUS_1';
                        break;
                    case 'pending':
                        $order_status = 'MERCADOPAGO_STATUS_7';
                        break;
                }

                $order_id = UtilMercadoPago::getOrderByCartId($cart->id);
                $order = new Order($order_id);
                if ($order_status != null) {
                    $statusPS = (int)$order->getCurrentState();
                    $payment_status = $payment_info['status'];
                    $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta[$payment_status]);
                    if ($payment_status != $statusPS) {
                        $order->setCurrentState($payment_status);
                    }

                    try {
                        $payments = $order->getOrderPaymentCollection();
                        $payments[0]->transaction_id = implode(' / ', $payment_ids);
                        $payments[0]->update();
                    } catch (Exception $e) {
                        UtilMercadoPago::logMensagem(
                            'Occured a error during the process the update order, payments is null = '.$id_cart,
                            MPApi::ERROR,
                            $e->getMessage(),
                            true,
                            null,
                            'MercadoPago->updateOrder'
                        );
                    }

                    $uri = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.
                         $mercadopago->id.'&id_order='.$order->id.'&key='.$order->secure_key;

                    $uri .= '&payment_status='.$payment_statuses[0];
                    $uri .= '&payment_id='.implode(' / ', $payment_ids);
                    $uri .= '&payment_type='.implode(' / ', $payment_types);
                    $uri .= '&payment_method_id='.implode(' / ', $payment_method_ids);
                    $uri .= '&amount='.$total;
                    if ($payment_info['payment_type_id'] == 'credit_card') {
                        $uri .= '&card_holder_name='.implode(' / ', $card_holder_names);
                        $uri .= '&four_digits='.implode(' / ', $four_digits_arr);
                        $uri .= '&statement_descriptor='.$statement_descriptors[0];
                        $uri .= '&status_detail='.$status_details[0];
                    }
                    Tools::redirectLink($uri);
                }
            }
        } else {
            UtilMercadoPago::logMensagem(
                'MercadoPagoStandardReturnModuleFrontController::initContent = '.
                'External reference is not set. Order placement has failed.',
                MPApi::ERROR
            );
        }
    }

    protected function redirectError()
    {
        $this->errors[] = $this->module->getMappingError("ERROR_PENDING");
        $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, array(
            'step' => '3')));
    }
}
