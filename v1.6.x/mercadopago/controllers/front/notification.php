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
 *  @author    MERCADOPAGO.COM REPRESENTA&Ccedil;&Otilde;ES LTDA.
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

include_once dirname(__FILE__) . '/../../mercadopago.php';
include_once dirname(__FILE__) . '/../../includes/MPApi.php';

class MercadoPagoNotificationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart = new Cart(Tools::getValue('cart_id'));
        $total = (float) ($cart->getOrderTotal(true, Cart::BOTH));
        $checkout = Tools::getValue('checkout');
        $topic = null;
        $id = null;

        $mercadopago = $this->module;

        if (empty(Tools::getValue('topic'))) {
            $topic = Tools::getValue('type');
            $id = Tools::getValue('data_id');
        } else {
            $topic = Tools::getValue('topic');
            $id = Tools::getValue('id');
        }
        if ($checkout == 'custom') {
            $status = $this->getStatusCustom();
            if ($status == 'rejected') {
                UtilMercadoPago::log(
                    "Notification",
                    "The notification came, but the status is rejected " . Tools::getValue('data_id')
                );
                var_dump(http_response_code(500));
                die();
            }
        }
        if ($topic == 'merchant_order') {
            $api = $mercadopago->getAPI();
            $result = $api->getMerchantOrder($id);
            if ($result['response']['status'] == "opened") {
                var_dump(http_response_code(200));
                die();
            }
        }

        if ($checkout == 'standard' || $checkout == 'custom') {
            if (!$cart->orderExists()) {
                var_dump(http_response_code(500));
                $customer = new Customer((int) $cart->id_customer);
                $displayName = $mercadopago->l('Mercado Pago ' . $checkout);
                $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta['started']);
                try {
                    $mercadopago->validateOrder(
                        $cart->id,
                        $payment_status,
                        $total,
                        $displayName,
                        null,
                        array(),
                        (int) $cart->id_currency,
                        false,
                        $customer->secure_key
                    );
                    Order::getOrderByCartId(Tools::getValue('cart_id'));
                } catch (Exception $e) {
                    UtilMercadoPago::log(
                        "There is a problem with notification id " . $cart->id,
                        $e->getMessage()
                    );
                }
            } else {
                $mercadopago->listenIPN(
                    $checkout,
                    $topic,
                    $id
                );
                UtilMercadoPago::log(
                    "Notification",
                    "The notification return 201, the card is updated  " . Tools::getValue('cart_id')
                );
                var_dump(http_response_code(201));
            }
        } else {
            var_dump(http_response_code(500));
        }
        die();
    }

    public function getStatusCustom()
    {
        $api = $this->module->getAPI();
        $result = $api->getPayment(Tools::getValue('data_id'), "custom");
        $payment_info = $result['response'];
        return $payment_info['status'];
    }
}
