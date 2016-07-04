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

include_once dirname(__FILE__) . '/../../mercadopago.php';

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
        // card_token_id
        $mercadopago = $this->module;

        $response = $mercadopago->execPayment($_POST);
        error_log("====response=====".Tools::jsonEncode($response));
        $order_status = null;
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
        
        if ($order_status != null) {
            $cart = Context::getContext()->cart;
            
            $total = (double) number_format($response['transaction_amount'], 2, '.', '');
            $extra_vars = array(
                '{bankwire_owner}' => $mercadopago->textshowemail,
                '{bankwire_details}' => '',
                '{bankwire_address}' => ''
            );

            $id_order = Order::getOrderByCartId($cart->id);

            $order = new Order($id_order);
            $existStates = $mercadopago->checkStateExist($id_order, Configuration::get($order_status));
            if ($existStates) {
                return;
            }
            
            $mercadopago->validateOrder(
                $cart->id,
                Configuration::get($order_status),
                $total,
                $mercadopago->displayName,
                null,
                $extra_vars,
                $cart->id_currency,
                false,
                $cart->secure_key
            );
            $order = new Order($mercadopago->currentOrder);
            $order_payments = $order->getOrderPayments();
            $order_payments[0]->transaction_id = $response['id'];

            $uri = __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $cart->id . '&id_module=' . $mercadopago->id .
                 '&id_order=' . $mercadopago->currentOrder . '&key=' . $order->secure_key . '&payment_id=' .
                 $response['id'] . '&payment_status=' . $response['status'];
            
            if (Tools::getIsset('card_token_id')) {
                // get credit card last 4 digits
                $four_digits = '**** **** **** ' . $response["card"]["last_four_digits"];
                
                $cardholderName = $response["card"]["cardholder"]["name"];
                
                $order_payments[0]->card_number = $four_digits;
                $order_payments[0]->card_brand = Tools::ucfirst($response['payment_method_id']);
                $order_payments[0]->card_holder = $cardholderName;
                
                $uri .= '&card_token=' . Tools::getValue('card_token_id') . '&card_holder_name=' . $cardholderName .
                     '&four_digits=' . $four_digits . '&payment_method_id=' . $response['payment_method_id'] .
                     '&payment_type=' . $response['payment_type_id'] . '&installments=' . $response['installments'] .
                     '&statement_descriptor=' . $response['statement_descriptor'] . '&status_detail=' .
                     $response['status_detail'] . '&amount=' . $response['transaction_details']['total_paid_amount'];
            } else {
                $uri .= '&payment_method_id=' . $response['payment_method_id'] . '&payment_type=' .
                     $response['payment_type_id'] . '&boleto_url=' .
                     urlencode($response['transaction_details']['external_resource_url']);
            }
            $order_payments[0]->save();
            Tools::redirectLink($uri);
        } else {
            $this->context->controller->addCss(
                (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
                htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ .
                'modules/mercadopago/views/css/mercadopago_core.css',
                'all'
            );
            
            $data = array(
                'version' => $mercadopago->getPrestashopVersion(),
                'one_step' => Configuration::get('PS_ORDER_PROCESS_TYPE')
            );
            
            if (array_key_exists('message', $response) && (strpos($response['message'], 'Invalid users involved') !==
                 false || (strpos($response['message'], 'users from different countries') !== false))) {
                $data['valid_user'] = false;
            } else {
                $data['version'] = $mercadopago->getPrestashopVersion();
                
                $data['status_detail'] = $response['status_detail'];
                $data['card_holder_name'] = Tools::getValue('cardholderName');
                $data['four_digits'] = Tools::getValue('lastFourDigits');
                $data['payment_method_id'] = Tools::getValue('payment_method_id');
                $data['installments'] = $response['installments'];
                $data['amount'] = Tools::displayPrice(
                    $response['transaction_details']['total_paid_amount'],
                    new Currency(Context::getContext()->cart->id_currency),
                    false
                );
                $data['payment_id'] = $response['payment_id'];
                $data['one_step'] = Configuration::get('PS_ORDER_PROCESS_TYPE');
                $data['valid_user'] = true;
                $data['message'] = $response['message'];
            }
            $this->context->smarty->assign($data);
            $this->setTemplate('error.tpl');
        }
    }
}
