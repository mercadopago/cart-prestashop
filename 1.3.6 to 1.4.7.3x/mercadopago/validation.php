<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/mercadopago.php');

	$currency = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
		
	$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
		
	$mercadopago = new mercadopago();

	$mailVars = array
	(
		'{bankwire_owner}' 		=> $mercadopago->textshowemail, 
		'{bankwire_details}' 	=> '', 
		'{bankwire_address}' 	=> ''
	);
			
	$mercadopago->validateOrder
	(
		$cart->id, 
		Configuration::get('mercadopago_STATUS_0'), 
		$total, 
		$mercadopago->displayName, 
		NULL, 
		$mailVars, 
		$currency->id
	);
					
	$order 		= new Order($mercadopago->currentOrder);
	$idCustomer = $order->id_customer;
	$idLang		= $order->id_lang;
	$customer 	= new Customer(intval($idCustomer));
	$CusMail	= $customer->email;


	Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$mercadopago->id.'&id_order='.$mercadopago->currentOrder.'&key='.$order->secure_key);

?>