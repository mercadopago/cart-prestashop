<?php
/**
* Tratar as IPN
**/

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/mercadopago.php');


error_log("entrou aqui na notificação");
error_log("===external_reference=====".Tools::getValue('external_reference'));

// check value
$external_reference = Tools::getValue('external_reference');
$cart = new Cart(Tools::getValue('external_reference'));
$mercadopago = new MercadoPago();

if (!$cart->orderExists()) {
    var_dump(http_response_code(500)); 

    $customer = new Customer((int)$cart->id_customer);
    $displayName = $mercadopago->l('Mercado Pago Redirect');
    $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta['started']);
    try {
        $mercadopago->validateOrder(
            $cart->id,
            $payment_status,
            $cart->getOrderTotal(true, Cart::BOTH),
            $displayName,
            null,
            array(),
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );

        $id_order = Order::getOrderByCartId($external_reference);
        error_log("==id_order==".$id_order); 
    
    } catch(Exception $e) {
        error_log($e->getMessage());
    }    
} else {
    error_log("entrou em listenIPN");
    $mercadopago->listenIPN(
        Tools::getValue('checkout'),
        Tools::getValue('topic'),
        Tools::getValue('id')
    );  
    var_dump(http_response_code(201));       
}