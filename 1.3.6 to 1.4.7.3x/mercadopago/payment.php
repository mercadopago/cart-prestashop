<?php

$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/mercadopago.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
$mercadopago = new mercadopago();
echo $mercadopago->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>


