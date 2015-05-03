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

if (!defined('_PS_VERSION_'))
	exit;

function_exists('curl_init');
include(dirname(__FILE__).'/includes/mercadopago.php');

class MercadoPago extends PaymentModule {

	public function __construct()
	{
		$this->name = 'mercadopago';
		$this->tab = 'payments_gateways';
		$this->version = '3.0.1';
		$this->currencies = true;
		$this->currencies_mode = 'radio';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

		parent::__construct();

		$this->page = basename(__file__, '.php');
		$this->displayName = $this->l('MercadoPago');
		$this->description = $this->l('Receive payments via MercadoPago of credit cards and tickets using our custom checkout or standard checkout');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall MercadoPago?');
		$this->textshowemail = $this->l('You must follow MercadoPago rules for purchase to be valid');
		$this->author = $this->l('MERCADOPAGO.COM REPRESENTAÇÕES LTDA.');
		$this->link = new Link();
		$this->mercadopago = new MP(Configuration::get('MERCADOPAGO_CLIENT_ID'), Configuration::get('MERCADOPAGO_CLIENT_SECRET'));
	}

	public function createStates()
	{
		$order_states = array(
			array('#ccfbff', $this->l('Transaction in Process'), 'in_process', '010010000'),
			array('#c9fecd', $this->l('Transaction Finished'), 'payment', '110010010'),
			array('#fec9c9', $this->l('Transaction Cancelled'), 'order_canceled', '010010000'),
			array('#fec9c9', $this->l('Transaction Rejected'), 'payment_error', '010010000'),
			array('#ffeddb', $this->l('Transaction Refunded'), 'refund', '110010000'),
			array('#c28566', $this->l('Transaction Chargedback'), 'charged_back', '010010000'),
			array('#b280b2', $this->l('Transaction in Mediation'), 'in_mediation', '010010000'),
			array('#fffb96', $this->l('Transaction Pending'), 'pending', '010010000')
		);

		$languages = Language::getLanguages();

		foreach ($order_states as $key => $value)
		{
			$order_state = new OrderState();
			$order_state->invoice = $value[3][0];
			$order_state->send_email = $value[3][1];
			$order_state->module_name = 'mercadopago';
			$order_state->color = $value[0];
			$order_state->unremovable = $value[3][2];
			$order_state->hidden = $value[3][3];
			$order_state->logable = $value[3][4];
			$order_state->delivery = $value[3][5];
			$order_state->shipped = $value[3][6];
			$order_state->paid = $value[3][7];
			$order_state->deleted = $value[3][8];
			$order_state->name = array();
			$order_state->template = array();

			foreach (Language::getLanguages(false) as $language)
			{
				$order_state->name[(int)$language['id_lang']] = $value[1];
				$order_state->template[$language['id_lang']] = $value[2];
				
				if ($value[2] == 'in_process' || $value[2] == 'pending' ||
					$value[2] == 'charged_back' || $value[2] == 'in_mediation')
				{
					$this->populateEmail($language['iso_code'], $value[2], 'html');
					$this->populateEmail($language['iso_code'], $value[2], 'txt');
				}
			}

			if (!$order_state->add())
				return false;

			$file = _PS_ROOT_DIR_.'/img/os/'.(int)$order_state->id.'.gif';
			copy((dirname(__file__).'/views/img/mp_icon.gif'), $file);

			Configuration::updateValue('MERCADOPAGO_STATUS_'.$key, $order_state->id);
		}

		return true;
	}

	private function populateEmail($lang, $name, $extension)
	{
		if (!file_exists(_PS_MAIL_DIR_.$lang))
			mkdir(_PS_MAIL_DIR_.$lang, 0777, true);

		$new_template = _PS_MAIL_DIR_.$lang.'/'.$name.'.'.$extension;

		if (!file_exists($new_template))
		{
			$template = dirname(__file__).'/mails/'.$name.'.'.$extension;
			copy($template, $new_template);
		}
	}

	private function deleteStates()
	{
		for ($index = 0; $index <= 7; $index++)
		{
			$order_state = new OrderState(Configuration::get('MERCADOPAGO_STATUS_'.$index));
			if (!$order_state->delete())
				return false;
		}
		return true;
	}

	public function install()
	{
		if (!parent::install()
			|| !$this->createStates()
			|| !Configuration::updateValue('MERCADOPAGO_PUBLIC_KEY', '')
			|| !Configuration::updateValue('MERCADOPAGO_CLIENT_ID', '')
			|| !Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', '')
			|| !Configuration::updateValue('MERCADOPAGO_CATEGORY', 'others')
			|| !Configuration::updateValue('MERCADOPAGO_CREDITCARD_BANNER', '')
			|| !Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', '')
			|| !Configuration::updateValue('MERCADOPAGO_BOLETO_ACTIVE', '')
			|| !Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', '')
			|| !Configuration::updateValue('MERCADOPAGO_STANDARD_BANNER', '')
			|| !Configuration::updateValue('MERCADOPAGO_WINDOW_TYPE', '')
			|| !Configuration::updateValue('MERCADOPAGO_IFRAME_WIDTH', '')
			|| !Configuration::updateValue('MERCADOPAGO_IFRAME_HEIGHT', '')
			|| !Configuration::updateValue('MERCADOPAGO_INSTALLMENTS', '')
			|| !Configuration::updateValue('MERCADOPAGO_AUTO_RETURN', '')
			|| !Configuration::updateValue('MERCADOPAGO_VISA', '')
			|| !Configuration::updateValue('MERCADOPAGO_MASTERCARD', '')
			|| !Configuration::updateValue('MERCADOPAGO_HIPERCARD', '')
			|| !Configuration::updateValue('MERCADOPAGO_AMEX', '')
			|| !Configuration::updateValue('MERCADOPAGO_DINERS', '')
			|| !Configuration::updateValue('MERCADOPAGO_ELO', '')
			|| !Configuration::updateValue('MERCADOPAGO_MELI', '')
			|| !Configuration::updateValue('MERCADOPAGO_BOLBRADESCO', '')
			|| !Configuration::updateValue('MERCADOPAGO_COUNTRY', '')
			|| !$this->registerHook('payment')
			|| !$this->registerHook('paymentReturn')
			|| !$this->registerHook('displayHeader'))

			return false;

		return true;
	}

	public function uninstall()
	{
		if (!$this->deleteStates()
			|| !Configuration::deleteByName('MERCADOPAGO_PUBLIC_KEY')
			|| !Configuration::deleteByName('MERCADOPAGO_CLIENT_ID')
			|| !Configuration::deleteByName('MERCADOPAGO_CLIENT_SECRET')
			|| !Configuration::deleteByName('MERCADOPAGO_CATEGORY')
			|| !Configuration::deleteByName('MERCADOPAGO_CREDITCARD_BANNER')
			|| !Configuration::deleteByName('MERCADOPAGO_CREDITCARD_ACTIVE')
			|| !Configuration::deleteByName('MERCADOPAGO_BOLETO_ACTIVE')
			|| !Configuration::deleteByName('MERCADOPAGO_STANDARD_ACTIVE')
			|| !Configuration::deleteByName('MERCADOPAGO_STANDARD_BANNER')
			|| !Configuration::deleteByName('MERCADOPAGO_WINDOW_TYPE')
			|| !Configuration::deleteByName('MERCADOPAGO_IFRAME_WIDTH')
			|| !Configuration::deleteByName('MERCADOPAGO_IFRAME_HEIGHT')
			|| !Configuration::deleteByName('MERCADOPAGO_INSTALLMENTS')
			|| !Configuration::deleteByName('MERCADOPAGO_AUTO_RETURN')
			|| !Configuration::deleteByName('MERCADOPAGO_VISA')
			|| !Configuration::deleteByName('MERCADOPAGO_MASTERCARD')
			|| !Configuration::deleteByName('MERCADOPAGO_HIPERCARD')
			|| !Configuration::deleteByName('MERCADOPAGO_AMEX')
			|| !Configuration::deleteByName('MERCADOPAGO_DINERS')
			|| !Configuration::deleteByName('MERCADOPAGO_ELO')
			|| !Configuration::deleteByName('MERCADOPAGO_MELI')
			|| !Configuration::deleteByName('MERCADOPAGO_BOLBRADESCO')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_0')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_1')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_2')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_3')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_4')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_5')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_6')
			|| !Configuration::deleteByName('MERCADOPAGO_STATUS_7')
			|| !Configuration::deleteByName('MERCADOPAGO_COUNTRY')
			|| !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$errors = array();
		$success = false;

		if (Tools::getValue('login'))
		{
			$client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
			$client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');

			if (!$this->validateCredential($client_id, $client_secret))
			{
				$errors[] = $this->l('Client Id or Client Secret invalid.');
				$success = false;
			}
			else
			{
				$this->setDefaultValues($client_id, $client_secret);	
			}
		}
		else if (Tools::getValue('submitmercadopago'))
		{
			$client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
			$client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');
			$public_key = Tools::getValue('MERCADOPAGO_PUBLIC_KEY');

			$creditcard_active = Tools::getValue('MERCADOPAGO_CREDITCARD_ACTIVE');
			$boleto_active = Tools::getValue('MERCADOPAGO_BOLETO_ACTIVE');
			$standard_active = Tools::getValue('MERCADOPAGO_STANDARD_ACTIVE');

			if (!$this->validateCredential($client_id, $client_secret))
			{
				$errors[] = $this->l('Client Id or Client Secret invalid.');
				$success = false;
			}
			else
			{
				Configuration::updateValue('MERCADOPAGO_CLIENT_ID', $client_id);
				Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', $client_secret);
				Configuration::updateValue('MERCADOPAGO_COUNTRY', $this->getCountry($client_id, $client_secret));

				$success = true;

				if ($creditcard_active == 'true' && !empty($public_key))
					if (!$this->validatePublicKey($client_id, $client_secret, $public_key))
					{
						$errors[] = $this->l('Public Key invalid.');
						$success = false;
					}
					else
						Configuration::updateValue('MERCADOPAGO_PUBLIC_KEY', $public_key);
			}

			$category = Tools::getValue('MERCADOPAGO_CATEGORY');
			Configuration::updateValue('MERCADOPAGO_CATEGORY', $category);

			$creditcard_banner = Tools::getValue('MERCADOPAGO_CREDITCARD_BANNER');
			Configuration::updateValue('MERCADOPAGO_CREDITCARD_BANNER', $creditcard_banner);

			Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', $standard_active);
			Configuration::updateValue('MERCADOPAGO_BOLETO_ACTIVE', $boleto_active);
			Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', $creditcard_active);

			$standard_banner = Tools::getValue('MERCADOPAGO_STANDARD_BANNER');
			Configuration::updateValue('MERCADOPAGO_STANDARD_BANNER', $standard_banner);

			$window_type = Tools::getValue('MERCADOPAGO_WINDOW_TYPE');
			Configuration::updateValue('MERCADOPAGO_WINDOW_TYPE', $window_type);

			$iframe_width = Tools::getValue('MERCADOPAGO_IFRAME_WIDTH');
			Configuration::updateValue('MERCADOPAGO_IFRAME_WIDTH', $iframe_width);

			$iframe_height = Tools::getValue('MERCADOPAGO_IFRAME_HEIGHT');
			Configuration::updateValue('MERCADOPAGO_IFRAME_HEIGHT', $iframe_height);

			$installments = Tools::getValue('MERCADOPAGO_INSTALLMENTS');
			Configuration::updateValue('MERCADOPAGO_INSTALLMENTS', $installments);

			$auto_return = Tools::getValue('MERCADOPAGO_AUTO_RETURN');
			Configuration::updateValue('MERCADOPAGO_AUTO_RETURN', $auto_return);

			$visa = Tools::getValue('MERCADOPAGO_VISA');
			$mastercard = Tools::getValue('MERCADOPAGO_MASTERCARD');
			$hipercard = Tools::getValue('MERCADOPAGO_HIPERCARD');
			$amex = Tools::getValue('MERCADOPAGO_AMEX');
			$diners = Tools::getValue('MERCADOPAGO_DINERS');
			$elo = Tools::getValue('MERCADOPAGO_ELO');
			$meli = Tools::getValue('MERCADOPAGO_MELI');
			$bolbradesco = Tools::getValue('MERCADOPAGO_BOLBRADESCO');

			if (!($visa == 'checked' && $mastercard == 'checked' && $hipercard == 'checked' && $amex == 'checked'
				&& $diners == 'checked' && $elo == 'checked' && $meli == 'checked' && $bolbradesco == 'checked'))
			{
				Configuration::updateValue('MERCADOPAGO_VISA', $visa);
				Configuration::updateValue('MERCADOPAGO_MASTERCARD', $mastercard);
				Configuration::updateValue('MERCADOPAGO_HIPERCARD', $hipercard);
				Configuration::updateValue('MERCADOPAGO_AMEX', $amex);
				Configuration::updateValue('MERCADOPAGO_DINERS', $diners);
				Configuration::updateValue('MERCADOPAGO_ELO', $elo);
				Configuration::updateValue('MERCADOPAGO_MELI', $meli);
				Configuration::updateValue('MERCADOPAGO_BOLBRADESCO', $bolbradesco);
			}
			else
			{
				$errors[] = $this->l('Enable at least one payment method.');
				$success = false;
			}
		}

		$this->context->controller->addCss($this->_path.'views/css/settings.css', 'all');
		$this->context->controller->addCss($this->_path.'views/css/bootstrap.css', 'all');
		$this->context->controller->addCss($this->_path.'views/css/style.css', 'all');

		$this->context->smarty->assign(
			array(
				'public_key' => htmlentities(Configuration::get('MERCADOPAGO_PUBLIC_KEY'), ENT_COMPAT, 'UTF-8'),
				'client_id' => htmlentities(Configuration::get('MERCADOPAGO_CLIENT_ID'), ENT_COMPAT, 'UTF-8'),
				'client_secret' => htmlentities(Configuration::get('MERCADOPAGO_CLIENT_SECRET'), ENT_COMPAT, 'UTF-8'),
				'country' => htmlentities(Configuration::get('MERCADOPAGO_COUNTRY'), ENT_COMPAT, 'UTF-8'),
				'category' => htmlentities(Configuration::get('MERCADOPAGO_CATEGORY'), ENT_COMPAT, 'UTF-8'),
				'creditcard_banner' => htmlentities(Configuration::get('MERCADOPAGO_CREDITCARD_BANNER'), ENT_COMPAT, 'UTF-8'),
				'creditcard_active' => htmlentities(Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE'), ENT_COMPAT, 'UTF-8'),
				'boleto_active' => htmlentities(Configuration::get('MERCADOPAGO_BOLETO_ACTIVE'), ENT_COMPAT, 'UTF-8'),
				'standard_active' => htmlentities(Configuration::get('MERCADOPAGO_STANDARD_ACTIVE'), ENT_COMPAT, 'UTF-8'),
				'standard_banner' => htmlentities(Configuration::get('MERCADOPAGO_STANDARD_BANNER'), ENT_COMPAT, 'UTF-8'),
				'window_type' => htmlentities(Configuration::get('MERCADOPAGO_WINDOW_TYPE'), ENT_COMPAT, 'UTF-8'),
				'iframe_width' => htmlentities(Configuration::get('MERCADOPAGO_IFRAME_WIDTH'), ENT_COMPAT, 'UTF-8'),
				'iframe_height' => htmlentities(Configuration::get('MERCADOPAGO_IFRAME_HEIGHT'), ENT_COMPAT, 'UTF-8'),
				'installments' => htmlentities(Configuration::get('MERCADOPAGO_INSTALLMENTS'), ENT_COMPAT, 'UTF-8'),
				'auto_return' => htmlentities(Configuration::get('MERCADOPAGO_AUTO_RETURN'), ENT_COMPAT, 'UTF-8'),
				'visa' => htmlentities(Configuration::get('MERCADOPAGO_VISA'), ENT_COMPAT, 'UTF-8'),
				'mastercard' => htmlentities(Configuration::get('MERCADOPAGO_MASTERCARD'), ENT_COMPAT, 'UTF-8'),
				'hipercard' => htmlentities(Configuration::get('MERCADOPAGO_HIPERCARD'), ENT_COMPAT, 'UTF-8'),
				'amex' => htmlentities(Configuration::get('MERCADOPAGO_AMEX'), ENT_COMPAT, 'UTF-8'),
				'diners' => htmlentities(Configuration::get('MERCADOPAGO_DINERS'), ENT_COMPAT, 'UTF-8'),
				'elo' => htmlentities(Configuration::get('MERCADOPAGO_ELO'), ENT_COMPAT, 'UTF-8'),
				'meli' => htmlentities(Configuration::get('MERCADOPAGO_MELI'), ENT_COMPAT, 'UTF-8'),
				'bolbradesco' => htmlentities(Configuration::get('MERCADOPAGO_BOLBRADESCO'), ENT_COMPAT, 'UTF-8'),
				'uri' => $_SERVER['REQUEST_URI'],
				'errors' => $errors,
				'success' => $success,
				'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
									.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
				'version' => $this->getPrestashopVersion()

			)
		);

		return $this->display(__file__, '/views/templates/admin/settings.tpl');
	}

	private function setDefaultValues($client_id, $client_secret) {
		$country = $this->getCountry($client_id, $client_secret);

		Configuration::updateValue('MERCADOPAGO_CLIENT_ID', $client_id);
		Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', $client_secret);
		Configuration::updateValue('MERCADOPAGO_COUNTRY', $country);
		Configuration::updateValue('MERCADOPAGO_STANDARD_BANNER', (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
																	__PS_BASE_URI__.'modules/mercadopago/views/img/'.$country.'/banner_all_methods.png');
		Configuration::updateValue('MERCADOPAGO_WINDOW_TYPE', 'redirect');
		Configuration::updateValue('MERCADOPAGO_IFRAME_WIDTH', '725');
		Configuration::updateValue('MERCADOPAGO_IFRAME_HEIGHT', '570');
		Configuration::updateValue('MERCADOPAGO_INSTALLMENTS', '12');
		Configuration::updateValue('MERCADOPAGO_AUTO_RETURN', 'approved');

		if ($country == "MLB")
		{
			Configuration::updateValue('MERCADOPAGO_CREDITCARD_BANNER', (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
																			__PS_BASE_URI__.'modules/mercadopago/views/img/credit_card.png');
			Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', 'true');
			Configuration::updateValue('MERCADOPAGO_BOLETO_ACTIVE', 'true');
			Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', 'false');
		}
		else
		{
			Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', 'true');
		}
	}

	private function getCountry($client_id, $client_secret)
	{
		$mp = new MP($client_id, $client_secret);
		return $mp->getCountry();
	}

	private function validateCredential($client_id, $client_secret)
	{
		$mp = new MP($client_id, $client_secret);
		return $mp->getAccessToken() ? true : false;
	}

	private function validatePublicKey($client_id, $client_secret, $public_key)
	{
		$mp = new MP($client_id, $client_secret);
		return $mp->validatePublicKey($public_key);
	}

	public function hookDisplayHeader()
	{
		if (!$this->active)
			return;

		$data = array(
				'creditcard_active' => Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE'),
				'public_key' => Configuration::get('MERCADOPAGO_PUBLIC_KEY')
		);

		$this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');
		$this->context->controller->addCss($this->_path.'views/css/mercadopago_v'.$this->getPrestashopVersion().'.css', 'all');
		$this->context->smarty->assign($data);

		return $this->display(__file__, '/views/templates/hook/header.tpl');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;

		if ($this->hasCredential())
		{   
			$this_path_ssl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
									.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;
			$data = array(
				'this_path_ssl' => $this_path_ssl,
				'boleto_active' => Configuration::get('MERCADOPAGO_BOLETO_ACTIVE'),
				'creditcard_active' => Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE'),
				'standard_active' => Configuration::get('MERCADOPAGO_STANDARD_ACTIVE'),
				'version' => $this->getPrestashopVersion(),
				'custom_action_url' => $this->link->getModuleLink('mercadopago', 'custompayment', array(), Configuration::get('PS_SSL_ENABLED'), null, null, false),
				'payment_status' => Tools::getValue('payment_status'),
				'status_detail' => Tools::getValue('status_detail'),
				'payment_method_id' => Tools::getValue('payment_method_id'),
				'installments' => Tools::getValue('installments'),
				'statement_descriptor' => Tools::getValue('statement_descriptor'),
				'window_type' => Configuration::get('MERCADOPAGO_WINDOW_TYPE'),
				'iframe_width' => Configuration::get('MERCADOPAGO_IFRAME_WIDTH'),
				'iframe_height' => Configuration::get('MERCADOPAGO_IFRAME_HEIGHT')
			);

			// send credit card configurations only activated
			if (Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE') == 'true')
			{
				$data['public_key'] = Configuration::get('MERCADOPAGO_PUBLIC_KEY');
				$data['creditcard_banner'] = Configuration::get('MERCADOPAGO_CREDITCARD_BANNER');
				$data['amount'] = (Float)number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, '.', '');
			}

			// send standard configurations only activated
			if (Configuration::get('MERCADOPAGO_STANDARD_ACTIVE') == 'true')
			{
				$result = $this->createStandardCheckoutPreference();
				if (array_key_exists('init_point', $result['response']))
				{
					$data['standard_banner'] = Configuration::get('MERCADOPAGO_STANDARD_BANNER');
					$data['preferences_url'] = $result['response']['init_point'];
				}
				else
				{
					$data['preferences_url'] = null;
					error_log('An error occurred during preferences creation. Please check your credentials and try again.');
				}
			}

			$this->context->smarty->assign($data);

			return $this->display(__file__, '/views/templates/hook/checkout.tpl');
		}
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		if (Tools::getValue('payment_type') == 'ticket' || Tools::getValue('payment_method_id') == 'bolbradesco')
		{
			$this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');

			$this->context->smarty->assign(
				array(
					'payment_id' => Tools::getValue('payment_id'),
					'boleto_url' => Tools::getValue('boleto_url'),
					'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
									.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__
				)
			);
			return $this->display(__file__, '/views/templates/hook/boleto_payment_return.tpl');
		}
		else if (Tools::getValue('checkout') == 'standard')
		{
			$data = array();
			$data['amount'] = Tools::displayPrice(Tools::getValue('amount'), $params['currencyObj'], false);
			$data['preferences_url'] = Tools::getValue('preferences_url');
			$data['window_type'] = Tools::getValue('window_type');
			$data['standard_banner'] = Tools::getValue('standard_banner');
			$data['this_path_ssl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
									.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;

			if ($data['window_type'] == 'iframe')
			{
				$data['iframe_width'] = Tools::getValue('iframe_width');
				$data['iframe_height'] = Tools::getValue('iframe_height');
			}

			$this->context->smarty->assign($data);

			$this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');

			return $this->display(__file__, '/views/templates/hook/standard_checkout.tpl');
		}
		else
		{
			$this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');

			$this->context->smarty->assign(
				array(
					'payment_status' => Tools::getValue('payment_status'),
					'status_detail' => Tools::getValue('status_detail'),
					'card_holder_name' => Tools::getValue('card_holder_name'),
					'four_digits' => Tools::getValue('four_digits'),
					'payment_method_id' => Tools::getValue('payment_method_id'),
					'expiration_date' => Tools::getValue('expiration_date'),
					'installments' => Tools::getValue('installments'),
					'transaction_amount' => Tools::displayPrice(Tools::getValue('transaction_amount'), $params['currencyObj'], false),
					'statement_descriptor' => Tools::getValue('statement_descriptor'),
					'payment_id' => Tools::getValue('payment_id'),
					'amount' => Tools::displayPrice(Tools::getValue('amount'), $params['currencyObj'], false),
					'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
									.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__
				)
			);

			return $this->display(__file__, '/views/templates/hook/creditcard_payment_return.tpl');
		}
	}

	private function hasCredential()
	{
		return Configuration::get('MERCADOPAGO_CLIENT_ID') != '' && Configuration::get('MERCADOPAGO_CLIENT_SECRET') != '';
	}

	public function execPayment($post)
	{
		$result = $this->mercadopago->createCustomPayment($this->getPrestashopPreferences($post));
		return $result['response'];
	}

	private function getPrestashopPreferences($post)
	{
		$customer_fields = Context::getContext()->customer->getFields();
		$cart = Context::getContext()->cart;

		//Get shipment data
		$address_delivery = new Address((Integer)$cart->id_address_delivery);
		$shipments = array(
			'receiver_address' => array(
				'floor' => '-',
				'zip_code' => $address_delivery->postcode,
				'street_name' => $address_delivery->address1.' - '.$address_delivery->address2.' - '.$address_delivery->city.'/'.$address_delivery->country,
				'apartment' => '-',
				'street_number' => '-'
				)
		);

		// Get costumer data
		$address_invoice = new Address((Integer)$cart->id_address_invoice);
		$phone = $address_invoice->phone;
		$phone .= $phone == '' ? '' : '|';
		$phone .= $address_invoice->phone_mobile;
		$customer_data = array(
			'first_name' => $customer_fields['firstname'],
			'last_name' => $customer_fields['lastname'],
			'email' => $customer_fields['email'],
			'phone' => array(
				'area_code' => '-',
				'number' => $phone
			),
			'address' => array(
				'zip_code' => $address_invoice->postcode,
				'street_name' => $address_invoice->address1.' - '.$address_invoice->address2.' - '.
									$address_invoice->city.'/'.$address_invoice->country,
				'street_number' => '-'
			),
			// just have this data when using credit card
			'identification' => array(
				'number' => $post != null && array_key_exists('docNumber', $post) ? $post['docNumber'] : '',
				'type' => $post != null && array_key_exists('docType', $post) ? $post['docType'] : ''
			)
		);
		//items
		$image_url = '';
		$products = $cart->getProducts();
		$items = array();

		foreach ($products as $product)
		{
			$image_url = '';
			// get image URL
			if (!empty($product['id_image']))
			{
				$image = new Image($product['id_image']);
				$image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().'.'.$image->image_format;
			}

			$item = array (
				'id' => $product['id_product'],
				'title' => utf8_encode($product['description_short']),
				'description' => utf8_encode($product['description_short']),
				'quantity' => $product['quantity'],
				'unit_price' => $product['price_wt'],
				'picture_url'=> $image_url,
				'category_id'=> Configuration::get('MERCADOPAGO_CATEGORY')
			);

			$items[] = $item;
		}

		// include shipping cost
		$shipping_cost = (Float)$cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		if ($shipping_cost > 0)
		{
			$item = array (
				'title' => 'Shipping',
				'description' => 'Shipping service used by store',
				'quantity' => 1,
				'unit_price' => $shipping_cost,
				'category_id'=> Configuration::get('MERCADOPAGO_CATEGORY')
			);

			$items[] = $item;
		}

		// include wrapping cost
		$wrapping_cost = (Float)$cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
		if ($wrapping_cost > 0)
		{
			$item = array (
				'title' => 'Wrapping',
				'description' => 'Wrapping service used by store',
				'quantity' => 1,
				'unit_price' => $wrapping_cost,
				'category_id'=> Configuration::get('MERCADOPAGO_CATEGORY')
			);

			$items[] = $item;
		}

		// include discounts
		$discounts = (Float)$cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		if ($discounts > 0)
		{
			$item = array (
				'title' => 'Discount',
				'description' => 'Discount provided by store',
				'quantity' => 1,
				'unit_price' => -$discounts,
				'category_id'=> Configuration::get('MERCADOPAGO_CATEGORY')
			);

			$items[] = $item;
		}

		$data = array(
			'external_reference' => $cart->id,
			'customer' => $customer_data,
			'items' => $items,
			'shipments' => $shipments,
		);


		if ($post != null && (array_key_exists('card_token_id', $post) ||
			(array_key_exists('payment_method_id', $post) && $post['payment_method_id'] == 'bolbradesco')))
		{
			$cart = Context::getContext()->cart;

			$data['reason'] = 'Prestashop via MercadoPago';
			$data['amount'] = (Float)number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
			$data['payer_email'] = $customer_fields['email'];
			$data['notification_url'] = $this->link->getModuleLink('mercadopago', 'notification', array(), Configuration::get('PS_SSL_ENABLED'), null, null, false).'?checkout=custom&';

			// add only for creditcard
			if (array_key_exists('card_token_id', $post))
			{
				$data['card_token_id'] = $post['card_token_id'];
				$data['installments'] = (Integer)$post['installments'];
			}
			// add only for boleto
			else
				$data['payment_method_id'] = $post['payment_method_id'];
		}
		else
		{
			$data['auto_return'] = Configuration::get('MERCADOPAGO_AUTO_RETURN') == 'approved' ? 'approved' : '';
			$data['back_urls']['success'] = $this->link->getModuleLink('mercadopago', 'standardreturn', array(), Configuration::get('PS_SSL_ENABLED'), null, null, false);
			$data['back_urls']['failure'] = $this->link->getPageLink('order-opc', Configuration::get('PS_SSL_ENABLED'), null, null, false, null);
			$data['back_urls']['pending'] = $this->link->getModuleLink('mercadopago', 'standardreturn', array(), Configuration::get('PS_SSL_ENABLED'), null, null, false);
			$data['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentMethods();
			$data['payment_methods']['excluded_payment_types'] = array();
			$data['payment_methods']['installments'] = (Integer)Configuration::get('MERCADOPAGO_INSTALLMENTS');
			$data['notification_url'] = $this->link->getModuleLink('mercadopago', 'notification', array(), Configuration::get('PS_SSL_ENABLED'), null, null, false).'?checkout=standard&';

			// swap to payer index since customer is only for transparent
			$data['customer']['name'] = $data['customer']['first_name'];
			$data['customer']['surname'] = $data['customer']['last_name'];
			$data['payer'] = $data['customer'];
			unset($data['customer']);
		}
		return $data;
	}

	public function createStandardCheckoutPreference()
	{
		return $this->mercadopago->createPreference($this->getPrestashopPreferences(null));
	}

	private function getExcludedPaymentMethods()
	{
		$excluded_payment_methods = array();

		if (Configuration::get('MERCADOPAGO_VISA') == 'checked')
			$excluded_payment_methods[] = array('id' => 'visa');
		if (Configuration::get('MERCADOPAGO_MASTERCARD') == 'checked')
			$excluded_payment_methods[] = array('id' => 'master');
		if (Configuration::get('MERCADOPAGO_HIPERCARD') == 'checked')
			$excluded_payment_methods[] = array('id' => 'hipercard');
		if (Configuration::get('MERCADOPAGO_AMEX') == 'checked')
			$excluded_payment_methods[] = array('id' => 'amex');
		if (Configuration::get('MERCADOPAGO_DINERS') == 'checked')
			$excluded_payment_methods[] = array('id' => 'diners');
		if (Configuration::get('MERCADOPAGO_ELO') == 'checked')
			$excluded_payment_methods[] = array('id' => 'elo');
		if (Configuration::get('MERCADOPAGO_MELI') == 'checked')
			$excluded_payment_methods[] = array('id' => 'melicard');
		if (Configuration::get('MERCADOPAGO_BOLBRADESCO') == 'checked')
			$excluded_payment_methods[] = array('id' => 'bolbradesco');

		return $excluded_payment_methods;
	}

	public function listenIPN($checkout, $topic, $id)
	{
		$payment_method_ids = array();
		$payment_ids = array();
		$payment_statuses = array();
		$payment_types = array();
		$credit_cards = array();
		$transaction_amounts = 0;
		$cardholders = array();
		$external_reference = '';

		if ($checkout == "standard" && $topic == 'merchant_order' && $id > 0)
		{
			// get merchant order info
			$result = $this->mercadopago->getMerchantOrder($id);
			$merchant_order_info = $result['response'];
			$payments = $merchant_order_info['payments'];
			$external_reference = $merchant_order_info['external_reference'];

			foreach($payments as $payment)
			{
				// get payment info
				$result = $this->mercadopago->getPayment($payment['id']);
				$payment_info = $result['response']['collection'];

				// colect payment details
				$payment_ids[] = $payment_info['id'];
				$payment_statuses[] = $payment_info['status'];
				$payment_types[] = $payment_info['payment_type'];
				$transaction_amounts += $payment_info['transaction_amount'];
				if ($payment_info['payment_type'] == 'credit_card')
				{
					$payment_method_ids[] = $payment_info['payment_method_id'];
					$credit_cards[] = 'xxxx xxxx xxxx '.$payment_info['last_four_digits'];
					$cardholders[] = $payment_info['cardholder']['name'];
				}	
			}
			$this->updateOrder($payment_ids, $payment_statuses, $payment_method_ids, $payment_types, $credit_cards, $cardholders, $transaction_amounts, $external_reference);
		} 
		else if ($checkout == "custom" && $topic == 'payment' && $id > 0)
		{
			$result = $this->mercadopago->getPayment($id);
			$payment_info = $result['response']['collection'];
			$external_reference = $payment_info['external_reference'];
			// colect payment details
			$payment_ids[] = $payment_info['id'];
			$payment_statuses[] = $payment_info['status'];
			$payment_types[] = $payment_info['payment_type'];
			$transaction_amounts += $payment_info['transaction_amount'];
			if ($payment_info['payment_type'] == 'credit_card')
			{
				$payment_method_ids[] = $payment_info['payment_method_id'];
				$credit_cards[] = 'xxxx xxxx xxxx '.$payment_info['last_four_digits'];
				$cardholders[] = $payment_info['cardholder']['name'];
			}
			$this->updateOrder($payment_ids, $payment_statuses, $payment_method_ids, $payment_types, $credit_cards, $cardholders, $transaction_amounts, $external_reference);
		}

	}

	private function updateOrder($payment_ids, $payment_statuses, $payment_method_ids, $payment_types, $credit_cards, $cardholders, $transaction_amounts, $external_reference)
	{
		// if has two creditcard validate whether payment has same status in order to continue validating order
		if (count($payment_statuses) == 1 || (count($payment_statuses) == 2 && $payment_statuses[0] == $payment_statuses[1])) {
			$order;
			$order_status = null;
			$payment_status = $payment_statuses[0];
			$payment_type = $payment_types[0];
			switch ($payment_status)
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
			// just change if there is an order status
			if($order_status)
			{
				$id_cart = $external_reference;
				$id_order = Order::getOrderByCartId($id_cart);
				if ($id_order)
				{
					$order = new Order($id_order);
				}

				// If order wasn't created yet and payment is approved or pending or in_process, create it. 
				// This can happen when user closes checkout standard
				if (empty($id_order) && ($payment_status == 'in_process' || $payment_status == 'approved' || $payment_status == 'pending'))
				{
					$cart = new Cart($id_cart);
					$total = (Float)number_format($transaction_amounts, 2, '.', '');
					$extra_vars = array (
							'{bankwire_owner}' => $this->textshowemail,
							'{bankwire_details}' => '',
							'{bankwire_address}' => ''
							);
					$this->validateOrder($id_cart, Configuration::get($order_status),
												$total,
												$this->displayName,
												null,
												$extra_vars, $cart->id_currency);
					$id_order = !$id_order ? Order::getOrderByCartId($id_cart) : $id_order;
					$order = new Order($id_order);
				}
				else if ($order->current_state != null && $order->current_state != Configuration::get($order_status))
				{
					$id_order = !$id_order ? Order::getOrderByCartId($id_cart) : $id_order;
					$order = new Order($id_order);
					$this->updateOrderHistory($order->id, Configuration::get($order_status));

					// Cancel the order to force products to go to stock.
					switch ($payment_status)
					{
						case 'cancelled':
						case 'refunded':
						case 'rejected':
							$this->updateOrderHistory($id_order, Configuration::get('PS_OS_CANCELED'), false);
						break;
					}
				}

				// update order payment information
				$order_payments = $order->getOrderPayments();
				foreach($order_payments as $order_payment)
				{
					$order_payment->transaction_id = join(" / ", $payment_ids);
				
					if ($payment_type == "credit_card")
					{
						$order_payment->card_number = join(" / ", $credit_cards);
						$order_payment->card_brand = join(" / ", $payment_method_ids);
						$order_payment->card_holder = join(" / ", $cardholders);
						//card_expiration just custom checkout has it. Can't fecht it thru collections
					}
					$order_payment->save();
				}
			}	
		}
	}


	private function updateOrderHistory($id_order, $status, $mail = true)
	{
		// Change order state and send email
		$history = new OrderHistory();
		$history->id_order = (Integer)$id_order;
		$history->changeIdOrderState((Integer)$status, (Integer)$id_order, true);
		if ($mail)
		{
			$extra_vars = array();
			$history->addWithemail(true, $extra_vars);
		}
	}

	public function getPrestashopVersion()
	{
		if (version_compare(_PS_VERSION_, '1.6.0.1', '>='))
			$version = 6;
		else if (version_compare(_PS_VERSION_, '1.5.0.1', '>='))
			$version = 5;
		else
			$version = 4;

		return $version;
	}
}
?>