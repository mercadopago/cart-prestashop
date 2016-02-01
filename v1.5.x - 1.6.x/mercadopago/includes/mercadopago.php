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
*  @author    hcasatti
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of MercadoPago
*/

$GLOBALS['LIB_LOCATION'] = dirname(__FILE__);

class MP {

	const VERSION = '3.1.0';

	/*Info*/
	const INFO = 1;
	/*Warning*/
	const WARNING = 2;
	/*Error*/
	const ERROR = 3;
	/*Fatal Error*/
	const FATAL_ERROR = 4;

	private $client_id;
	private $client_secret;
	private $access_data;
	private $sandbox = false;

	public function __construct($client_id, $client_secret)
	{
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
	}

	/**
	 * Get Access Token for API use
	 */
	public function getAccessToken()
	{
		$app_client_values = $this->buildQuery(array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'client_credentials'
				));

		$access_data = MPRestClient::post('/oauth/token', $app_client_values, 'application/x-www-form-urlencoded');

		$this->access_data = $access_data['response'];

		return $this->access_data['access_token'];
	}

	/**
	 * Get Access Token for API use
	 */
	public function getAccessToken_v1()
	{
		$app_client_values = $this->buildQuery(array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'client_credentials'
				));

		$access_data = MPRestClient::post('/oauth/token', $app_client_values, 'application/x-www-form-urlencoded');

		$this->access_data = $access_data['response'];

		return $this->access_data['access_token'];
	}

	/* 
	 v0
	*/
	public function isTestUser()
	{
		$access_token = $this->getAccessToken();
		$result = MPRestClient::get('/users/me?access_token='.$access_token);

		return in_array('test_user', $result['response']['tags']);
	}

	public function getCountry()
	{
		$access_token = $this->getAccessToken();
		$result = MPRestClient::get('/users/me?access_token='.$access_token);

		return $result['response']['site_id'];
	}

	/**
	 * Get information for specific payment
	 * @param int $id
	 * @return array(json)
	 */
	public function getPayment($id)
	{
		$access_token = $this->getAccessToken();

		$uri_prefix = $this->sandbox ? '/sandbox' : '';
		$payment_info = MPRestClient::get($uri_prefix.'/collections/notifications/'.$id.'?access_token='.$access_token);
		return $payment_info;
	}

	/**
	 * Get information for specific payment
	 * @param int $id
	 * @return array(json)
	 */
	public function getMerchantOrder($id)
	{
		$access_token = $this->getAccessToken();

		$uri_prefix = $this->sandbox ? '/sandbox' : '';
		$merchant_order = MPRestClient::get($uri_prefix.'/merchant_orders/'.$id.'?access_token='.$access_token);
		return $merchant_order;
	}

	/**
	 * Get all payment methods for merchant country
	 * @return array(json)
	 */
	public function getPaymentMethods()
	{
		$access_token = $this->getAccessToken();	
		$result = MPRestClient::get('/v1/payment_methods/?access_token='.$access_token);
		$result = $result['response'];

		// remove account_money
		foreach($result as $key => $value)
		{	
			if($value['payment_type_id'] == 'account_money')
				unset($result[$key]);
		}
		return $result;
	}


	/**
	 * Get all offline payment methods for merchant country
	 * @return array(json)
	 */
	public function getOfflinePaymentMethods()
	{
		$access_token = $this->getAccessToken();	
		$result = MPRestClient::get('/v1/payment_methods/?access_token='.$access_token);
		$result = $result['response'];

		// remove account_money
		foreach($result as $key => $value)
		{	
			if($value['payment_type_id'] == 'account_money' || $value['payment_type_id'] == 'credit_card' 
				|| $value['payment_type_id'] == 'debit_card' || $value['payment_type_id'] == 'prepaid_card')
				unset($result[$key]);
		}
		return $result;
	}

	/**
	 * Create a checkout preference
	 * @param array $preference
	 * @return array(json)
	 */
	public function createPreference($preference)
	{
		$access_token = $this->getAccessToken();

		$preference_result = MPRestClient::post('/checkout/preferences?access_token='.$access_token, $preference);
		return $preference_result;
	}

	/*
		v1
	*/
	public function createCustomPayment($info)
	{
		$access_token = $this->getAccessToken();		
		$preference_result = MPRestClient::post('/v1/payments?access_token='.$access_token, $info);
	
		return $preference_result;
	}

	public static function getCategories()
	{
		$response = MPRestClient::get('/item_categories');
		$response = $response['response'];
		return $response;
	}

	private function buildQuery($params)
	{
		if (function_exists('http_build_query'))
			return http_build_query($params, '', '&');
		else
		{
			$elements = array ();
			foreach ($params as $name => $value)
				$elements[] = '{$name}='.urlencode($value);
			return implode('&', $elements);
		}
	}

}

/**
 * MercadoPago cURL RestClient
 */
class MPRestClient {

	const API_BASE_URL = 'https://api.mercadopago.com';

	private static function getConnect($uri, $method, $content_type)
	{
		$connect = curl_init(self::API_BASE_URL.$uri);

		curl_setopt($connect, CURLOPT_USERAGENT, 'MercadoPago Prestashop v'.MP::VERSION);
		curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($connect, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: '.$content_type));

		return $connect;
	}

	private static function setData(&$connect, $data, $content_type)
	{
		if ($content_type == 'application/json')
		{
			if (gettype($data) == 'string'){
				Tools::jsonDecode($data, true);
			}
			else{
				$data = Tools::jsonEncode($data);
			}

			if (function_exists('json_last_error'))
			{
				$json_error = json_last_error();
				if ($json_error != JSON_ERROR_NONE)
					throw new Exception('JSON Error [{$json_error}] - Data: {$data}');
			}
		}

		curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
	}

	private static function exec($method, $uri, $data, $content_type)
	{
		$connect = self::getConnect($uri, $method, $content_type);
		if ($data){
			self::setData($connect, $data, $content_type);
		}

		$api_result = curl_exec($connect);
		$api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

		$response = array(
			'status' => $api_http_code,
			'response' => Tools::jsonDecode($api_result, true)
		);
		if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
			PrestaShopLogger::addLog('MercadoPago.exec :: data = '.Tools::jsonEncode($data), MP::INFO ,  0, null, null, true);
			PrestaShopLogger::addLog('MercadoPago.exec :: response = '.$api_result, MP::INFO , $response['status'], null, null, true);
		}

		if ($response['status'] == 0) {
			$error = 'Can not call the API, status code 0.';
   			throw new Exception($error);
		} else if ($response['status'] > 202){
			PrestaShopLogger::addLog("MercadoPago::exec = ".$response['response']['message'], MP::ERROR , $response['status']);
		}

		curl_close($connect);

		return $response;
	}

	public static function get($uri, $content_type = 'application/json')
	{

		return self::exec('GET', $uri, null, $content_type);
	}

	public static function post($uri, $data, $content_type = 'application/json')
	{
		return self::exec('POST', $uri, $data, $content_type);
	}

	public static function put($uri, $data, $content_type = 'application/json')
	{
		return self::exec('PUT', $uri, $data, $content_type);
	}

}

?>
