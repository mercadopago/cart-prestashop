<?php
/**
* Tratar as IPN
**/

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/mercadopago.php');

$mercadopago = new MercadoPago();

$mercadopago->listenIPN(
    Tools::getValue('checkout'),
    Tools::getValue('topic'),
    Tools::getValue('id')
);
