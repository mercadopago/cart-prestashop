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

include_once dirname(__FILE__) . '/../../includes/MPApi.php';
class MercadoPagoCancelOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->cancelOrder();
    }

    public function cancelOrder()
    {
        // card_token_id
        $mercadopago = $this->module;
        $mercadopago_sdk = $mercadopago->mercadopago;
        $responseCancel = null;
        $token = Tools::getAdminToken('AdminOrder'.Tools::getValue('id_order'));
        $token_form = Tools::getValue('token_form');

        //check token
        if ($token == $token_form) {
            $order = new Order(Tools::getValue("id_order"));
            $order_payments =  $order->getOrderPayments();
            foreach ($order_payments as $order_payment) {
                if ($order_payment->transaction_id > 0) {
                    $result = $mercadopago_sdk->getPayment($order_payment->transaction_id, "custom");
                    if ($result['status'] == 404) {
                        $result = $mercadopago_sdk->getPayment($order_payment->transaction_id, "standard");
                    }
                    if ($result['status'] == 200) {
                        $responseCancel = $mercadopago_sdk->cancelPaymentsCustom(
                            $order_payment->transaction_id
                        );
                    }
                }
                break;
            }
            if ($responseCancel != null && $responseCancel['status'] == 200) {
                $mercadopago->updateOrderHistory($order->id, Configuration::get('PS_OS_CANCELED'));
                $response = array(
                    'status' => '200',
                    'message' => $this->module->l('The payment was cancelled.')
                );
            } else {
                $response = array(
                    'status' => '404',
                    'message' => $this->module->l('Cannnot cancel the payment, please see the PrestaShop Log.')
                );

                UtilMercadoPago::logMensagem(
                    'Cannnot cancel the payment, please see the PrestaShop Log.',
                    MPApi::WARNING,
                    'Cannnot cancel the payment, please see the PrestaShop Log.',
                    true,
                    null,
                    'CancelOrder->cancelOrder'
                );
            }
        }

        header('Content-Type: application/json');
        echo Tools::jsonEncode($response);
        exit;
    }
}
