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
 *  @author    MercadoPago
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

include_once dirname(__FILE__).'/../../mercadopago.php';
class MercadoPagoCustomPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        $this->placeOrder();
    }

    private function placeOrder()
    {
        $mercadopago = $this->module;
        $cart = Context::getContext()->cart;
        $response = $mercadopago->execPayment($_POST);
        $displayName = 'Mercado Pago Custom';
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $order_status = null;
        UtilMercadoPago::log("custom payment", Tools::jsonEncode($response));
        if (array_key_exists('status', $response)) {
            switch ($response['status']) {
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
        }
        UtilMercadoPago::log("custom payment order_status", $order_status);
        if ($order_status != null) {
            $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');
            $id_cart_rule = null;
            if ($percent > 0) {
                $payment_mode = 'boleto';
                $installments = 1;
                if (Tools::getIsset('card_token_id')) {
                    $payment_mode = 'cartao';
                    $installments = (int)$response['installments'];
                }
                $id_cart_rule = $mercadopago->applyDiscount($cart, $payment_mode, $installments);
            }
            $customer = new Customer((int)$cart->id_customer);
            $payment_type_id = $response['payment_type_id'];
            $displayName = $mercadopago->setNamePaymentType($payment_type_id);

            $extra_vars = array('transaction_id' => $response['id']);
            $mercadopago->validateOrder(
                $cart->id,
                Configuration::get($order_status),
                $total,
                $displayName,
                null,
                $extra_vars,
                (int)$cart->id_currency,
                false,
                $customer->secure_key
            );
            if ($id_cart_rule != null) {
                $cartRule = new CartRule($id_cart_rule);
                $cartRule->active = false;
                $cartRule->save();
            }
         
            $uri = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$mercadopago->id.
                 '&id_order='.$mercadopago->currentOrder.'&key='.$customer->secure_key.'&payment_id='.
                 $response['id'].'&payment_status='.$response['status'];

            if (Tools::getIsset('card_token_id')) {
                // get credit card last 4 digits
                $four_digits = '**** **** **** '.$response['card']['last_four_digits'];
                $cardholderName = $response['card']['cardholder']['name'];
                $uri .= '&card_token='.Tools::getValue('card_token_id').'&card_holder_name='.$cardholderName.
                     '&four_digits='.$four_digits.'&payment_method_id='.$response['payment_method_id'].
                     '&payment_type='.$response['payment_type_id'].'&installments='.$response['installments'].
                     '&statement_descriptor='.$response['statement_descriptor'].'&status_detail='.
                     $response['status_detail'].'&amount='.$response['transaction_details']['total_paid_amount'];
            } else {
                $uri .= '&payment_method_id='.$response['payment_method_id'].'&payment_type='.
                     $response['payment_type_id'].'&boleto_url='.
                     urlencode($response['transaction_details']['external_resource_url']);
            }
          
            Tools::redirectLink($uri);
        }

        $data = $this->getError($mercadopago, $response, $cart->id);
        $this->context->smarty->assign($data);
        $this->setTemplate('error.tpl');
    }

    private function getError($mercadopago, $response, $cart_id)
    {
        $data = array();
        $status_detail = "";
        $messageAPI = "";
        $payment_method_id = "";
        if (isset($response['error'])) {
            $messageAPI = $response['message'];
        } else {
            $data['message'] = $mercadopago->l('Occurred an error in payment, please try again.');
            $status_detail = $response['status_detail'];
            $payment_method_id = $response['payment_method_id'];
        }
        $data['standard'] = "false";
        $data['payment_method_id'] = $payment_method_id;
        $data['status_detail'] = $status_detail;
        $data['one_step'] = Configuration::get('PS_ORDER_PROCESS_TYPE');
        $data['show_QRCode'] = "";

        UtilMercadoPago::logMensagem(
            'Occurred an error in payment, the id cart is ' .$cart_id,
            MPApi::ERROR,
            $messageAPI,
            true,
            null,
            "custompayment->placeOrder"
        );

        return $data;
    }
}
