<?php

// This will help you for the version 1.5.6

/* This will fix the next errors:
* Warning: Function displayHeader() is deprecated
* Warning: Function displayFooter() is deprecated
* Fatal error: Call to a member function isLogged()
*/

// Esto te será de ayuda para los errores del modulo para la version 1.5.6 de prestashop

/* Arreglará los siguientes errores:
* Warning: Function displayHeader() is deprecated
* Warning: Function displayFooter() is deprecated
* Fatal error: Call to a member function isLogged()
*/

$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');

$controller=new FrontController();
$controller->init();
$controller->initContent();
$controller->setMedia();
$controller->displayHeader();

include(dirname(__FILE__).'/mercadopago.php');

if (!Context::getContext()->customer->isLogged())
    Tools::redirect('authentication.php?back=order.php');
$mercadopago = new mercadopago();
echo $mercadopago->execPayment($cart);

$controller->displayFooter();

?>
