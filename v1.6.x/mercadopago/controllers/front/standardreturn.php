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
include_once dirname(__FILE__).'/../../includes/MPApi.php';
class MercadoPagoStandardReturnModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        $this->placeOrder();
    }
    public function placeOrder()
    {
        $collection_status = Tools::getValue('collection_status');
        $typeReturn = Tools::getValue('typeReturn');
        $mercadopago = $this->module;
        $mercadopago_sdk = $mercadopago->mercadopago;

        $preference = $mercadopago_sdk->getPreference(Tools::getValue('preference_id'));
        if ($typeReturn == 'failure') {
            $data = array();

            $data['typeReturn'] = "failure";
            $data['standard'] = "true";
            $data['status_detail'] = '';
            $data['one_step'] = Configuration::get('PS_ORDER_PROCESS_TYPE');
            $data['show_QRCode'] = "false";
            $data['this_path_ssl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                                     htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;

            if (isset($preference['response']['init_point'])) {
                $data['show_QRCode'] = "true";
                $data['init_point'] = $preference['response']['init_point'];
            }
            $this->context->smarty->assign($data);
            $this->setTemplate('error.tpl');
            return;
        }

        $id_cart = (int)Tools::getValue('cart_id');
        $cart = new Cart($id_cart);
        if ($collection_status == 'null' ||
            is_null($collection_status) ||
            !Validate::isLoadedObject($cart)
        ) {
            if (_PS_VERSION_ >= '1.5') {
                Tools::redirect('index.php?controller=cart');
            }
            return;
        }
        if (!Validate::isLoadedObject($cart)) {
            Tools::redirect('index.php?controller=cart');
            return;
        }

        if (Tools::getIsset('collection_id') && Tools::getValue('collection_id') != 'null') {
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

            foreach ($collection_ids as $collection_id) {
                $result = $mercadopago_sdk->getPayment($collection_id, "standard");
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

                if (isset($payment_info['payment_type_id']) &&
                    $payment_info['payment_type_id'] == 'credit_card' ||
                    $payment_info['payment_type_id'] == 'account_money'
                    ) {
                    $card_holder_names[] = isset($payment_info['card']['cardholder']['name'])
                    ? $payment_info['card']['cardholder']['name'] : '';
                    if (isset($payment_info['card']['last_four_digits'])) {
                        $four_digits_arr[] = '**** **** **** '.$payment_info['card']['last_four_digits'];
                    }
                    $statement_descriptors[] = isset($payment_info['statement_descriptor']) ?
                    $payment_info['statement_descriptor'] : ''  ;
                    $status_details[] = $payment_info['status_detail'];
                }
            }

            $order_id = $mercadopago->getOrderByCartId($cart->id);
            $order = new Order($order_id);

            $statusPS = (int)$order->getCurrentState();
            $payment_status = $payment_info['status'];
            $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta[$payment_status]);
            if ($payment_status != $statusPS) {
                $order->setCurrentState($payment_status);
            }

            $uri = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.
                 $mercadopago->id.'&id_order='.$order->id.'&key='.$order->secure_key;

            $uri .= '&payment_status='.$payment_statuses[0];
            $uri .= '&payment_id='.implode(' / ', $payment_ids);
            $uri .= '&payment_type='.implode(' / ', $payment_types);
            $uri .= '&payment_method_id='.implode(' / ', $payment_method_ids);
            $uri .= '&amount='.$cart->getOrderTotal(true, Cart::BOTH);

            if ($payment_info['payment_type_id'] == 'credit_card' ||
                $payment_info['payment_type_id'] == 'account_money') {
                $uri .= '&card_holder_name='.implode(' / ', $card_holder_names);
                $uri .= '&four_digits='.implode(' / ', $four_digits_arr);
                $uri .= '&statement_descriptor='.$statement_descriptors[0];
                $uri .= '&status_detail='.$status_details[0];
            }

            Tools::redirectLink($uri);
        } else {
            UtilMercadoPago::logMensagem(
                'MercadoPagoStandardReturnModuleFrontController::initContent',
                'External reference is not set. Order placement has failed.'
            );
            $this->setTemplate('error.tpl');
        }
    }
}
