<?php

/**
 * MercadoPago Integration Library
 * Access MercadoPago for payments integration
 * 
 * @author hcasatti
 *
 */
$GLOBALS["LIB_LOCATION"] = dirname(__FILE__);

class MP {

    const version = "0.2.1";

    private $client_id;
    private $client_secret;
    private $access_data;
    private $sandbox = FALSE;

    function __construct($client_id, $client_secret) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function sandbox_mode($enable = NULL) {
        if (!is_null($enable)) {
            $this->sandbox = $enable === TRUE;
        }

        return $this->sandbox;
    }

    /**
     * Get Access Token for API use
     */
    public function get_access_token() {
        $app_client_values = $this->build_query(array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
                ));

        $access_data = MPRestClient::post("/oauth/token", $app_client_values, "application/x-www-form-urlencoded");

        $this->access_data = $access_data['response'];

        return $this->access_data['access_token'];
    }

    /**
     * Get information for specific payment
     * @param int $id
     * @return array(json)
     */
    public function get_payment($id) {
        $access_token = $this->get_access_token();

        $uri_prefix = $this->sandbox ? "/sandbox" : "";
        $payment_info = MPRestClient::get($uri_prefix."/collections/notifications/" . $id . "?access_token=" . $access_token);
        return $payment_info;
    }
    public function get_payment_info($id) {
        return $this->get_payment($id);
    }

    /**
     * Get information for specific authorized payment
     * @param id
     * @return array(json)
    */    
    public function get_authorized_payment($id) {
        $access_token = $this->get_access_token();

        $authorized_payment_info = MPRestClient::get("/authorized_payments/" . $id . "?access_token=" . $access_token);
        return $authorized_payment_info;
    }

    /**
     * Refund accredited payment
     * @param int $id
     * @return array(json)
     */
    public function refund_payment($id) {
        $access_token = $this->get_access_token();

        $refund_status = array(
            "status" => "refunded"
        );

        $response = MPRestClient::put("/collections/" . $id . "?access_token=" . $access_token, $refund_status);
        return $response;
    }

    /**
     * Cancel pending payment
     * @param int $id
     * @return array(json)
     */
    public function cancel_payment($id) {
        $access_token = $this->get_access_token();

        $cancel_status = array(
            "status" => "cancelled"
        );

        $response = MPRestClient::put("/collections/" . $id . "?access_token=" . $access_token, $cancel_status);
        return $response;
    }

    /**
     * Cancel preapproval payment
     * @param int $id
     * @return array(json)
     */
    public function cancel_preapproval_payment($id) {
        $access_token = $this->get_access_token();

        $cancel_status = array(
            "status" => "cancelled"
        );

        $response = MPRestClient::put("/preapproval/" . $id . "?access_token=" . $access_token, $cancel_status);
        return $response;
    }

    /**
     * Search payments according to filters, with pagination
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return array(json)
     */
    public function search_payment($filters, $offset = 0, $limit = 0) {
        $access_token = $this->get_access_token();

        $filters["offset"] = $offset;
        $filters["limit"] = $limit;

        $filters = $this->build_query($filters);

        $uri_prefix = $this->sandbox ? "/sandbox" : "";
            
        $collection_result = MPRestClient::get($uri_prefix."/collections/search?" . $filters . "&access_token=" . $access_token);
        return $collection_result;
    }

    /**
     * Create a checkout preference
     * @param array $preference
     * @return array(json)
     */
    public function create_preference($preference) {
        $access_token = $this->get_access_token();

        $preference_result = MPRestClient::post("/checkout/preferences?access_token=" . $access_token, $preference);
        return $preference_result;
    }

    /**
     * Update a checkout preference
     * @param string $id
     * @param array $preference
     * @return array(json)
     */
    public function update_preference($id, $preference) {
        $access_token = $this->get_access_token();

        $preference_result = MPRestClient::put("/checkout/preferences/{$id}?access_token=" . $access_token, $preference);
        return $preference_result;
    }

    /**
     * Get a checkout preference
     * @param string $id
     * @return array(json)
     */
    public function get_preference($id) {
        $access_token = $this->get_access_token();

        $preference_result = MPRestClient::get("/checkout/preferences/{$id}?access_token=" . $access_token);
        return $preference_result;
    }

    /**
     * Create a preapproval payment
     * @param array $preapproval_payment
     * @return array(json)
     */
    public function create_preapproval_payment($preapproval_payment) {
        $access_token = $this->get_access_token();

        $preapproval_payment_result = MPRestClient::post("/preapproval?access_token=" . $access_token, $preapproval_payment);
        return $preapproval_payment_result;
    }

    /**
     * Get a preapproval payment
     * @param string $id
     * @return array(json)
     */
    public function get_preapproval_payment($id) {
        $access_token = $this->get_access_token();

        $preapproval_payment_result = MPRestClient::get("/preapproval/{$id}?access_token=" . $access_token);
        return $preapproval_payment_result;
    }

	/**
     * Update a preapproval payment
     * @param string $preapproval_payment, $id
     * @return array(json)
     */	
	
	public function update_preapproval_payment($id, $preapproval_payment) {
        $access_token = $this->get_access_token();

        $preapproval_payment_result = MPRestClient::put("/preapproval/" . $id . "?access_token=" . $access_token, $preapproval_payment);
        return $preapproval_payment_result;
    }

    /* **************************************************************************************** */

    private function build_query($params) {
        if (function_exists("http_build_query")) {
            return http_build_query($params, "", "&");
        } else {
            foreach ($params as $name => $value) {
                $elements[] = "{$name}=" . urlencode($value);
            }

            return implode("&", $elements);
        }
    }

}

/**
 * MercadoPago cURL RestClient
 */
class MPRestClient {

    const API_BASE_URL = "https://api.mercadolibre.com";

    private static function get_connect($uri, $method, $content_type) {
        $connect = curl_init(self::API_BASE_URL . $uri);

        curl_setopt($connect, CURLOPT_USERAGENT, "MercadoPago Prestashop-v1.5.3-1.6.x Cart v1.0.1");
        curl_setopt($connect, CURLOPT_CAINFO, $GLOBALS["LIB_LOCATION"] . "/cacert.pem");
        curl_setopt($connect, CURLOPT_SSLVERSION, 3);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connect, CURLOPT_HTTPHEADER, array("Accept: application/json", "Content-Type: " . $content_type));

        return $connect;
    }

    private static function set_data(&$connect, $data, $content_type) {
        if ($content_type == "application/json") {
            if (gettype($data) == "string") {
                json_decode($data, true);
            } else {
                $data = json_encode($data);
            }

            if(function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new Exception("JSON Error [{$json_error}] - Data: {$data}");
                }
            }
        }

        curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
    }

    private static function exec($method, $uri, $data, $content_type) {
        $connect = self::get_connect($uri, $method, $content_type);
        if ($data) {
            self::set_data($connect, $data, $content_type);
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        $response = array(
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        );

        if ($response['status'] >= 400) {
            throw new Exception ($response['response']['message'], $response['status']);
        }

        curl_close($connect);

        return $response;
    }

    public static function get($uri, $content_type = "application/json") {
        return self::exec("GET", $uri, null, $content_type);
    }

    public static function post($uri, $data, $content_type = "application/json") {
        return self::exec("POST", $uri, $data, $content_type);
    }

    public static function put($uri, $data, $content_type = "application/json") {
        return self::exec("PUT", $uri, $data, $content_type);
    }

}

?>
