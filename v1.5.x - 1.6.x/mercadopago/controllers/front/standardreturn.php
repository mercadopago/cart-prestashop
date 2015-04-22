<?php
/**
* 2007-2015 PrestaShop
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
*  @author    ricardobrito
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of MercadoPago
*/

include_once(dirname(__FILE__).'/../../mercadopago.php');

class MercadoPagoStandardReturnModuleFrontController extends ModuleFrontController {
	public function initContent()
	{
		parent::initContent();

		if (Tools::getIsset('collection_id') && Tools::getValue('collection_id') != 'null')
		{
			$mercadopago = $this->module;
			$mercadopago_sdk = $mercadopago->mercadopago;
			$result = $mercadopago_sdk->getPayment(Tools::getValue('collection_id'));
			$payment_info = $result['response']['collection'];
			$id_cart = $payment_info['external_reference'];
			$cart = new Cart($id_cart);
			
			if (Validate::isLoadedObject($cart))
			{
				$total = (Float)number_format($payment_info['transaction_amount'], 2, '.', '');
				

				$extra_vars = array (
							'{bankwire_owner}' => $mercadopago->textshowemail,
							'{bankwire_details}' => '',
							'{bankwire_address}' => ''
							);

				$order_status = null;
				$payment_status = $payment_info['status'];
				switch ($payment_status)
				{
					case 'in_process':
						$order_status = 'MERCADOPAGO_STATUS_0';
						break;
					case 'approved':
						$order_status = 'MERCADOPAGO_STATUS_1';
						break;
					case 'pending':
						$order_status = 'MERCADOPAGO_STATUS_7';
						break;
				}

				$order_id = Order::getOrderByCartId($cart->id);

				if ($order_status != null)
				{
					if (!$order_id)
					{
						$mercadopago->validateOrder($cart->id, Configuration::get($order_status),
											$total,
											$mercadopago->displayName,
											null,
											$extra_vars, $cart->id_currency);
					}

					$order_id  = !$order_id ? Order::getOrderByCartId($cart->id) : $order_id;
					$order = new Order($order_id);
					$uri = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.$mercadopago->id.
							'&id_order='.$order->id.'&key='.$order->secure_key;

					$order_payments = $order->getOrderPayments();
					$order_payments[0]->transaction_id = Tools::getValue('collection_id');

					$uri .= '&payment_status='.$payment_info['status'];
					$uri .= '&payment_id='.$payment_info['id'];
					$uri .= '&payment_type='.$payment_info['payment_type'];
					$uri .= '&payment_method_id='.$payment_info['payment_method_id'];

					if ($payment_info['payment_type'] == 'credit_card')
					{
						$uri .= '&card_holder_name='.$payment_info['cardholder']['name'];
						$uri .= '&four_digits='.$payment_info['last_four_digits'];
						$uri .= '&statement_descriptor='.$payment_info['statement_descriptor'];
						$uri .= '&status_detail='.$payment_info['status_detail'];

						$order_payments[0]->card_number = 'xxxx xxxx xxxx '.$payment_info['last_four_digits'];
						$order_payments[0]->card_brand = Tools::ucfirst($payment_info['payment_method_id']);
						$order_payments[0]->card_holder = $payment_info['cardholder']['name'];
					}
					$order_payments[0]->save();

					Tools::redirectLink($uri);
				}
			}
		}
		else
			error_log('External reference is not set. Order placement has failed.');
	}
}
?>