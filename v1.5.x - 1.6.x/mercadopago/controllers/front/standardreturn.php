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
			// payment variables
			$payment_statuses = array();
			$payment_ids = array();
			$payment_types = array();
			$payment_method_ids = array();
			$card_holder_names = array();
			$four_digits_arr = array();
			$statement_descriptors = array();
			$status_details = array();
			$transaction_amounts = 0;

			$collection_ids = split(',', Tools::getValue('collection_id'));
			foreach ($collection_ids as $collection_id)
			{
				$mercadopago = $this->module;
				$mercadopago_sdk = $mercadopago->mercadopago;
				$result = $mercadopago_sdk->getPayment($collection_id);
				$payment_info = $result['response']['collection'];

				$id_cart = $payment_info['external_reference'];
				$cart = new Cart($id_cart);

				$payment_statuses[] = $payment_info['status'];
				$payment_ids[] = $payment_info['id'];
				$payment_types[] = $payment_info['payment_type'];
				$payment_method_ids[] = $payment_info['payment_method_id'];
				$transaction_amounts += $payment_info['transaction_amount'];
				if ($payment_info['payment_type'] == 'credit_card')
				{
					$card_holder_names[] = $payment_info['cardholder']['name'];
					$four_digits_arr[] = '**** **** **** '.$payment_info['last_four_digits'];
					$statement_descriptors[] = $payment_info['statement_descriptor'];
					$status_details[] = $payment_info['status_detail'];
				}

				if (isset($result['response']['collection'])) {
					$response = $result['response']['collection'];
					$order_status = null;
					if (array_key_exists('status', $response))
					{
						switch ($response['status'])
						{
							case 'in_process':
								$order_status = 'MERCADOPAGO_STATUS_0';
								break;
							case 'approved':
								$order_status = 'MERCADOPAGO_STATUS_1';
								break;
							case 'cancelled':
								$order_status = 'MERCADOPAGO_STATUS_2';
								break;
							case 'refunded':
								$order_status = 'MERCADOPAGO_STATUS_4';
								break;
							case 'charged_back':
								$order_status = 'MERCADOPAGO_STATUS_5';
								break;
							case 'in_mediation':
								$order_status = 'MERCADOPAGO_STATUS_6';
								break;
							case 'pending':
								$order_status = 'MERCADOPAGO_STATUS_7';
								break;
							case 'rejected':
								$order_status = 'MERCADOPAGO_STATUS_3';
								break;
						}
					}

					if ($order_status != null && array_key_exists('transaction_amount', $response))
					{
						$total = (Float)number_format($response['transaction_amount'], 2, '.', '');

						$extra_vars = array (
									'{bankwire_owner}' => $mercadopago->textshowemail,
									'{bankwire_details}' => '',
									'{bankwire_address}' => ''
									);

						$mercadopago->validateOrder($cart->id, Configuration::get($order_status),
													$total,
													$mercadopago->displayName,
													null,
													$extra_vars, $cart->id_currency);
					}
				}
			}

			if (Validate::isLoadedObject($cart))
			{
				$order_id = Order::getOrderByCartId($cart->id);
				$order = new Order($order_id);
				$uri = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.$mercadopago->id.
						'&id_order='.$order->id.'&key='.$order->secure_key;

				$uri .= '&payment_status='.$payment_statuses[0];
				$uri .= '&payment_id='.join(" / ", $payment_ids);
				$uri .= '&payment_type='.join(" / ", $payment_types);
				$uri .= '&payment_method_id='.join(" / ", $payment_method_ids);
				$uri .= '&amount='.$transaction_amounts;

				if ($payment_info['payment_type'] == 'credit_card')
				{
					$uri .= '&card_holder_name='.join(" / ", $card_holder_names);
					$uri .= '&four_digits='.join(" / ", $four_digits_arr);
					$uri .= '&statement_descriptor='.$statement_descriptors[0];
					$uri .= '&status_detail='.$status_details[0];
				}

				Tools::redirectLink($uri);
			}
		}
		else
			error_log('External reference is not set. Order placement has failed.');
	}
}
?>