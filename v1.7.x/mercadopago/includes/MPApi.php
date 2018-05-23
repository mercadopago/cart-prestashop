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
 * @author    MERCADOPAGO.COM REPRESENTA&Ccedil;&Otilde;ES LTDA.
 * @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *          International Registered Trademark & Property of MercadoPago
 */

$GLOBALS['LIB_LOCATION'] = dirname(__FILE__);

include_once 'UtilMercadoPago.php';
include_once 'MPRestCli.php';

class MPApi
{
    const VERSION = '3.4.1';

    /* Info */
    const INFO = 1;

    /* Warning */
    const WARNING = 2;

    /* Error */
    const ERROR = 3;

    /* Fatal Error */
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

    public static function getInstanceMP()
    {
        static $mercadopago = null;
        if (null === $mercadopago) {
            $mercadopago = new MPApi(
                Configuration::get('MERCADOPAGO_CLIENT_ID'),
                Configuration::get('MERCADOPAGO_CLIENT_SECRET')
            );
        }

        return $mercadopago;
    }

    /**
     * Get Access Token for API use
     */
    public function getAccessToken()
    {
        $app_client_values = $this->buildQuery(
            array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials'
            )
        );

        $access_data = MPRestCli::post('/oauth/token', $app_client_values, 'application/x-www-form-urlencoded');

        $this->access_data = $access_data['response'];

        return $this->access_data['access_token'];
    }

    /**
     * Get Access Token for API use
     */
    public function getAccessTokenResponse()
    {
        $app_client_values = $this->buildQuery(
            array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials'
            )
        );

        $access_data = MPRestCli::post('/oauth/token', $app_client_values, 'application/x-www-form-urlencoded');

        $this->access_data = $access_data['response'];

        return $access_data['response'];
    }

    /**
     * Get Access Token for API use
     */
    public function getAccessTokenV1()
    {
        return trim(Configuration::get('MERCADOPAGO_ACCESS_TOKEN'));
    }

    /*
     * v0
     */
    public function isTestUser()
    {
        $access_token = $this->getAccessToken();
        $result = MPRestCli::get('/users/me?access_token=' . $access_token);

        return in_array('test_user', $result['response']['tags']);
    }

    public function getCountry()
    {
        $access_token = $this->getAccessToken();
        $result = MPRestCli::get('/users/me?access_token=' . $access_token);

        return $result['response']['site_id'];
    }

    /*
     * v0
     */
    public function calculateEnvios($params)
    {
        $access_token = $this->getAccessToken();

        $uri = "/shipping_options";
        $params["access_token"] = $access_token;

        $uri .= (strpos($uri, "?") === false) ? "?" : "&";
        $uri .= $this->buildQuery($params);

        $result = MPRestCli::get($uri);

        return  $result;
    }
    /**
     * Get information for specific payment
     *
     * @param int $id
     * @return array(json)
     */
    public function getPayment($id)
    {
        $access_token = $this->getAccessTokenV1();
        $uri_prefix = $this->sandbox ? '/sandbox' : '';
        $payment_info = MPRestCli::get($uri_prefix . '/v1/payments/' . $id . '?access_token=' . $access_token);
        return $payment_info;
    }

    /**
     * Get information for specific payment
     * https://api.mercadolibre.com/shipments/$id_shipment?access_token='
     * @param int $id
     * @return array(json)
     */
    public function getTracking($id_shipment)
    {
        $access_token = $this->getAccessToken();

        $tracking = MPRestCli::getShipment('/shipments/' . $id_shipment . '?access_token=' . $access_token);

        return $tracking;
    }

    public function getTagShipment($id_shipment)
    {
        $access_token = $this->getAccessToken();
        $tag_shipment = '/shipment_labels?savePdf=Y&shipment_ids='.$id_shipment.'&access_token=' . $access_token;

        return MPRestCli::API_BASE_MELI_URL.$tag_shipment;
    }

    public function getTagShipmentZebra($id_shipment)
    {
        $access_token = $this->getAccessToken();
        $tag_shipment = '/shipment_labels?response_type=zpl2&shipment_ids='.
            $id_shipment.'&access_token=' .
            $access_token;

        return MPRestCli::API_BASE_MELI_URL.$tag_shipment;
    }

    /**
     * Get information for specific payment
     *
     * @param int $id
     * @return array(json)
     */
    public function getPaymentStandard($id)
    {
        $access_token = $this->getAccessToken();

        $uri_prefix = $this->sandbox ? '/sandbox' : '';
        $payment_info = MPRestCli::get(
            $uri_prefix . '/v1/notifications/' . $id . '?access_token=' . $access_token
        );
        error_log("getPaymentStandard". Tools::jsonEncode($payment_inforesult));
        return $payment_info;
    }

    /**
     * Get information for specific payment
     *
     * @param int $id
     * @return array(json)
     */
    public function getMerchantOrder($id)
    {
        $access_token = $this->getAccessToken();

        $uri_prefix = $this->sandbox ? '/sandbox' : '';
        $merchant_order = MPRestCli::get($uri_prefix . '/merchant_orders/' . $id . '?access_token=' . $access_token);
        return $merchant_order;
    }

    /**
     * Get all payment methods for merchant country
     *
     * @return array(json)
     */
    public function getPaymentMethods()
    {
        $result = MPRestCli::get('/sites/' . $this->getCountry() . '/payment_methods?marketplace=NONE');
        $result = $result['response'];

        // remove account_money
        foreach ($result as $key => $value) {
            if ($value['payment_type_id'] == 'account_money') {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * Get all offline payment methods for merchant country
     *
     * @return array(json)
     */
    public function getOfflinePaymentMethods()
    {
        $access_token = $this->getAccessTokenV1();
        $result = MPRestCli::get('/v1/payment_methods?access_token=' . $access_token);
        $result = $result['response'];

        // remove account_money
        foreach ($result as $key => $value) {
            if ($value['payment_type_id'] == 'account_money' || $value['payment_type_id'] == 'credit_card' ||
                 $value['payment_type_id'] == 'debit_card' || $value['payment_type_id'] == 'prepaid_card') {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Get all offline payment methods for merchant country
     *
     * @return array(json)
     */
    public function getPaymentCreditsMLM()
    {
        $access_token = $this->getAccessTokenV1();
        $result = MPRestCli::get('/v1/payment_methods/?access_token=' . $access_token);
        $result = $result['response'];
        // remove account_money
        foreach ($result as $key => $value) {
            if ($value['payment_type_id'] == 'ticket' ||
                $value['payment_type_id'] == 'bank_transfer') {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Create a checkout preference
     *
     * @param array $preference
     * @return array(json)
     */
    public function createPreference($preference)
    {
        $access_token = $this->getAccessToken();
        $trackingID = "platform:desktop,type:prestashop,so:".MPApi::VERSION;
        $preference_result = MPRestCli::postTracking(
            '/checkout/preferences?access_token=' . $access_token,
            $preference,
            $trackingID
        );
        return $preference_result;
    }

    /*
     * Create payment v1
     */
    public function createCustomPayment($info)
    {
        $access_token = $this->getAccessTokenV1();
        $trackingID = "platform:v1-whitelabel,type:prestashop,so:".MPApi::VERSION;
        $preference_result = MPRestCli::postTracking(
            '/v1/payments?access_token=' .
            $access_token,
            $info,
            $trackingID
        );

        return $preference_result;
    }

    /*
     * getCustomer
     */
    public function getCustomer($params)
    {
        $access_token = $this->getAccessTokenV1();

        $uri = "/v1/customers/search";
        $params["access_token"] = $access_token;

        $uri .= (strpos($uri, "?") === false) ? "?" : "&";
        $uri .= $this->buildQuery($params);

        $customer = MPRestCli::get($uri);

        return $customer;
    }

    /*
     * getCustomerCards
     */
    public function getCustomerCards($customerID)
    {
        $access_token = $this->getAccessTokenV1();
        $uri = "/v1/customers/" . $customerID . "?access_token=" . $access_token;
        $customerCards = MPRestCli::get($uri);
        return $customerCards;
    }

    /*
     * Create customerCard v1
     * $mp->post ("/v1/customers", array("email" => "test@test.com"));
     */
    public function createCustomerCard($params)
    {
        $access_token = $this->getAccessTokenV1();
        $customerResponse = MPRestCli::post("/v1/customers?access_token=" . $access_token, $params);

        if ($customerResponse == null || $customerResponse["status"] != "200") {
            UtilMercadoPago::logMensagem(
                'MercadoPago::createCustomerCard - Error: Doens\'t possibled to create the Customer',
                MPApi::WARNING
            );
        }
        return $customerResponse;
    }

    /*
     * Create customerCard v1
     */
    public function addCustomerCard($token, $customerId)
    {
        $access_token = $this->getAccessTokenV1();
        $uri = "/v1/customers/" . $customerId . "/cards?access_token=" . $access_token;

        $result_response = MPRestCli::post($uri, $token);
        return $result_response;
    }

    public static function getCategories()
    {
        $response = MPRestCli::get('/item_categories');
        $response = $response['response'];
        return $response;
    }

    public function getCheckConfigCard()
    {
        $access_token = $this->getAccessTokenV1();
        $uri = "/settings?access_token=".$access_token;

        error_log("======url======".$uri);

        $result = MPRestCli::getConfig($uri);
        return $result;
    }

    /*
     * v1
     * active/inactive
     */
    public function setEnableDisableTwoCard($params)
    {
        $access_token = $this->getAccessTokenV1();
        error_log("=====params two cards=====".$params);

        $params = array(
            "two_cards" => $params
        );
        $result = MPRestCli::putConfig("/settings?access_token=" . $access_token, $params);
        error_log("=====result two cards=====".Tools::jsonEncode($result));
        return  $result;
    }

    public function getTestUser($siteID)
    {
        $access_token = $this->getAccessToken();
        $uri = "/users/test_user?access_token=" . $access_token;
        error_log("====uri=====".$uri);
        $result = MPRestCli::post($uri, $siteID);

        error_log("=====getTestUser======".Tools::jsonEncode($result));

        return $result;
    }

    public function getDiscount($params)
    {
        $access_token = $this->getAccessToken();
        $uri = "/discount_campaigns";
        $params["access_token"] = $access_token;

        if (count($params) > 0) {
            $uri .= (strpos($uri, "?") === false) ? "?" : "&";
            $uri .= $this->buildQuery($params);
        }
        $result = MPRestCli::get($uri);
        return $result;
    }

    /*
     * Save settings
     */
    public function saveSettings($params)
    {
        $access_token = $this->getAccessTokenV1();
        $uri = "/modules/tracking/saveSettings?access_token=" . $access_token;

        $result_response = MPRestCli::post($uri, $params);

        return $result_response;
    }

    private function buildQuery($params)
    {
        if (function_exists('http_build_query')) {
            return http_build_query($params, '', '&');
        } else {
            $elements = array();
            foreach ($params as $value) {
                $elements[] = '{$name}=' . urlencode($value);
            }
            return implode('&', $elements);
        }
    }
}
