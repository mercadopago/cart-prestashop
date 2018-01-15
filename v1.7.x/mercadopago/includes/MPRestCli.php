<?php
/**
 * 2007-2015 PrestaShop.
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
 * @author    MERCADOPAGO.COM REPRESENTA&Ccedil;&Otilde;ES LTDA
 * @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *          International Registered Trademark & Property of MercadoPago
 */

include_once 'MPApi.php';
class MPRestCli
{
    const API_BASE_SETTINGS_URL = 'http://localhost:8080';

    const API_BASE_URL = 'https://api.mercadopago.com';
    const API_BASE_MELI_URL = 'https://api.mercadolibre.com';

    const API_CONFIG_BASE_URL = 'https://api.mercadopago.com/account';


    private static function getConnect($uri, $method, $content_type, $uri_base)
    {
        $connect = curl_init($uri_base.$uri);
        curl_setopt($connect, CURLOPT_USERAGENT, 'MercadoPago Prestashop v'.MPApi::VERSION);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt(
            $connect,
            CURLOPT_HTTPHEADER,
            array(
                'Accept: application/json',
                'Content-Type: '.$content_type,
            )
        );

        return $connect;
    }

    private static function getConnectTracking($uri, $method, $content_type, $trackingID)
    {
        $connect = curl_init(self::API_BASE_URL.$uri);

        curl_setopt($connect, CURLOPT_USERAGENT, 'MercadoPago Prestashop v'.MPApi::VERSION);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt(
            $connect,
            CURLOPT_HTTPHEADER,
            array(
                'Accept: application/json',
                'Content-Type: '.$content_type,
                'X-Tracking-Id:'.$trackingID,
            )
        );

        return $connect;
    }

    private static function execTracking($method, $uri, $data, $content_type, $trackingID)
    {
        $connect = self::getConnectTracking($uri, $method, $content_type, $trackingID);

        if ($data) {
            self::setData($connect, $data, $content_type);
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);
        $response = array(
            'status' => $api_http_code,
            'response' => Tools::jsonDecode($api_result, true),
            );

        if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
            UtilMercadoPago::logMensagem('MercadoPago.exec :: data = '.Tools::jsonEncode($data), MPApi::INFO);
            UtilMercadoPago::logMensagem('MercadoPago.exec :: response = '.$api_result, MPApi::INFO);
        }

        if ($response['status'] == 0) {
            $error = 'Can not call the API, status code 0.';
            throw new Exception($error);
        } else {
            if ($response['status'] > 202) {
                UtilMercadoPago::logMensagem('MercadoPago::exec = '.$response['response']['message'], MPApi::ERROR);
            }
        }
        curl_close($connect);

        return $response;
    }

    private static function setData($connect, $data, $content_type)
    {
        if ($content_type == 'application/json') {
            if (gettype($data) == 'string') {
                Tools::jsonDecode($data, true);
            } else {
                $data = Tools::jsonEncode($data);
            }

            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new Exception('JSON Error [{$json_error}] - Data: {$data}');
                }
            }
        }

        curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
    }

    private static function exec($method, $uri, $data, $content_type, $uri_base)
    {
        $connect = self::getConnect($uri, $method, $content_type, $uri_base);

        if ($data) {
            self::setData($connect, $data, $content_type);
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);
        $response = array(
            'status' => $api_http_code,
            'response' => Tools::jsonDecode($api_result, true),
            );

        if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
            UtilMercadoPago::logMensagem('MercadoPago.exec :: data = '.Tools::jsonEncode($data), MPApi::INFO);
            UtilMercadoPago::logMensagem('MercadoPago.exec :: response = '.$api_result, MPApi::INFO);
        }

        if ($response['status'] == 0) {
            $error = 'Can not call the API, status code 0.';
            throw new Exception($error);
        } else {
            if ($response['status'] > 202) {
                UtilMercadoPago::logMensagem('MercadoPago::exec = '.$response['response']['message'], MPApi::ERROR);
            }
        }
        curl_close($connect);

        return $response;
    }

    public static function getShipment($uri, $content_type = 'application/json')
    {
        return self::exec('GET', $uri, null, $content_type, self::API_BASE_MELI_URL);
    }

    public static function getConfig($uri, $content_type = 'application/json')
    {
        return self::exec('GET', $uri, null, $content_type, self::API_CONFIG_BASE_URL);
    }

    public static function get($uri, $content_type = 'application/json')
    {
        return self::exec('GET', $uri, null, $content_type, self::API_BASE_URL);
    }

    public static function post($uri, $data, $content_type = 'application/json')
    {
        return self::exec('POST', $uri, $data, $content_type, self::API_BASE_URL);
    }

    public static function postTracking($uri, $data, $trackingID, $content_type = 'application/json')
    {
        return self::execTracking('POST', $uri, $data, $content_type, $trackingID);
    }

    public static function put($uri, $data, $content_type = 'application/json')
    {
        return self::exec('PUT', $uri, $data, $content_type, self::API_BASE_URL);
    }

    public static function putConfig($uri, $data, $content_type = 'application/json')
    {
        return self::exec('PUT', $uri, $data, $content_type, self::API_CONFIG_BASE_URL);
    }
}
