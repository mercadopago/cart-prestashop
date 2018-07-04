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
        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));
        UtilMercadoPago::log("====id cart====",  $cart->id);  

        $checkout = Tools::getValue('checkout');
        $topic = Tools::getValue('topic');
        UtilMercadoPago::log("====checkout====", $checkout);
        UtilMercadoPago::log("====topic====", $topic);

        if ($checkout == 'standard' && $topic == 'merchant_order') {
            $id_order = Order::getOrderByCartId(Tools::getValue('cart_id'));    
            if (!$cart->orderExists()) {
                UtilMercadoPago::log("====orderExists====", "Necessário criar a order");    
                var_dump(http_response_code(500)); 
                $mercadopago = new MercadoPago();
                $customer = new Customer((int)$cart->id_customer);
                $displayName = $mercadopago->l('Mercado Pago Redirect');
                $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta['started']);               
                try {
                    $mercadopago->validateOrder(
                        $cart->id,
                        $payment_status,
                        $total,
                        $displayName,
                        null,
                        array(),
                        (int)$cart->id_currency,
                        false,
                        $customer->secure_key
                    );
                    $id_order = Order::getOrderByCartId(Tools::getValue('cart_id'));  
                    UtilMercadoPago::log("====id_order criado====".$id_order, $id_order);      
                } catch(Exception $e) {
                    UtilMercadoPago::log(
                        "There is a problem with notification id ". $cart->id,
                        $e->getMessage()
                    );     
                }    
            } else {
                $mercadopago->listenIPN(
                    $checkout,
                    $topic,
                    Tools::getValue('id')
                );
                var_dump(http_response_code(201));
            }
        } else {
            var_dump(http_response_code(500));
        }
        die();
    }
}
