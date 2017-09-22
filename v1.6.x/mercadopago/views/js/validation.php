<?php
/**
* Capturador de IPN
*
* @author    Kijam.com <info@kijam.com>
* @copyright 2014 Kijam.com
* @license   Comercial
*/

$mp = null;

include(dirname(__FILE__).'/files.php');

if (!$mp) {
    if (_PS_VERSION_ >= '1.5') {
        Tools::redirect('index.php?controller=cart');
    } else {
        Tools::redirect('cart.php');
    }
    exit;
}

header('Content-type: text/plain');

if ($mp->gateway) {
    $mp->gateway->ipn();
}
