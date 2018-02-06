<?php
/**
* Tratar as IPN
**/

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/mercadopago.php');

$mercadopago = new MercadoPago();
error_log(Tools::getValue('checkout'));
error_log(Tools::getValue('topic'));
error_log(Tools::getValue('id'));

error_log("NOTIFICATION.PHP checkout = " . Tools::getValue('checkout'));
error_log("NOTIFICATION.PHP topic = " . Tools::getValue('topic'));
error_log("NOTIFICATION.PHP id = " . Tools::getValue('id'));

$mercadopago->listenIPN(
    Tools::getValue('checkout'),
    Tools::getValue('topic'),
    Tools::getValue('id')
);
