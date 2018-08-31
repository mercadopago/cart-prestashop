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
 *  @author    Mercado Pago
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of Mercado Pago
 */

class UtilMercadoPago
{

    public static $DEFAULT_BANNER = array(
        'MLB' => "https://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/PSJ/575x40_banner_psj_6x.jpg",
        'MLM' => "https://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_575X40_new.jpg",
        'MLA' => "https://imgmp.mlstatic.com/org-img/banners/ar/medios/575X40.jpg",
        'MCO' => "https://secure.mlstatic.com/developers/site/cloud/banners/co/575x40_Todos-los-medios-de-pago.jpg",
        'MLV' => "https://imgmp.mlstatic.com/org-img/banners/ve/medios/575X40.jpg",
        'MLC' => "https://www.mercadopago.cl/banner/575x40_banner.jpg",
        'MPE' => "",
        'MLU' => ""
    );

    public static $DEFAULT_SPONSOR_ID = array(
        'MLB' => 178326379,
        'MLM' => 187899553,
        'MLA' => 187899872,
        'MCO' => 187900060,
        'MLV' => 187900246,
        'MLC' => 187900485,
        'MPE' => 217182014,
        'MLU' => 241730009,
    );

    public static $statusMercadoPagoPresta = array(
                                                'in_process' => 'MERCADOPAGO_STATUS_0',
                                                'approved' => 'MERCADOPAGO_STATUS_1',
                                                'cancelled' => 'MERCADOPAGO_STATUS_2',
                                                'refunded' => 'MERCADOPAGO_STATUS_4',
                                                'charged_back' => 'MERCADOPAGO_STATUS_5',
                                                'in_mediation' => 'MERCADOPAGO_STATUS_6',
                                                'pending' => 'MERCADOPAGO_STATUS_7',
                                                'rejected' => 'MERCADOPAGO_STATUS_3',
                                                'ready_to_ship' => 'MERCADOPAGO_STATUS_8',
                                                'shipped' => 'MERCADOPAGO_STATUS_9',
                                                'delivered' => 'MERCADOPAGO_STATUS_10',
                                                'waiting_POS' => 'MERCADOPAGO_STATUS_11',
                                                'started' => 'MERCADOPAGO_STATUS_12'
                                            );

    public static function logMensagem($message, $nivel, $exceptionMessage, $logApi, $data, $methodOrUri)
    {
        UtilMercadoPago::log($message, $exceptionMessage);

        if ($logApi) {
            $errors = array(
            "endpoint" => $methodOrUri,
                "message" => $message,
                "payloads" => $data
            );
            MPApi::sendErrorLog($nivel, $errors);
        }
    }

    /*
    User Errors...
    */
    public static function log($msg, $exceptionMessage)
    {
        $date = date('d.m.Y h:i:s');
        $log = "Date:  ".$date."  | ".$msg.
        "| " . $exceptionMessage . "\n";
        error_log($log, 3, _PS_ROOT_DIR_ . '/modules/mercadopago/logs/mercadopago.log');
    }

    public static function getPrestashopVersion()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $version = 7;
        } elseif (version_compare(_PS_VERSION_, '1.6.0.1', '>=')) {
            $version = 6;
        } elseif (version_compare(_PS_VERSION_, '1.5.0.1', '>=')) {
            $version = 5;
        } else {
            $version = 4;
        }
        return $version;
    }

    /***
     * Check the requirements of module
     * @return array
     */
    public static function checkRequirements()
    {
        $requirements = array(
            'dimensoes' => '',
            'version' => '',
            'curl' => '',
            'ssl' => ''
        );

        $version = str_replace('.', '', phpversion());

        if ($version < 533) {
            $requirements['version'] = 'negative';
        } else {
            $requirements['version'] = 'positive';
        }

        if (!function_exists('curl_init')) {
            $requirements['curl'] = 'negative';
        } else {
            $requirements['curl'] = 'positive';
        }

        if (Configuration::get('MERCADOENVIOS_ACTIVATE') == 'true') {
            $sql = "SELECT id_product
            FROM "._DB_PREFIX_."product WHERE (width = 0 OR height = 0
            OR depth = 0
            OR weight = 0)
            AND online_only = 0
            AND available_for_order = 1
            AND active =1;";

            $dados = Db::getInstance()->executeS($sql);

            $requirements['dimensoes'] = $dados ? 'negative' : 'positive';
        }

        $requirements['ssl'] = Configuration::get('PS_SSL_ENABLED') == 0 ? "negative" : "positive";

        return $requirements;
    }

    public static function checkValueNull($value)
    {
        if (is_null($value) || empty($value)) {
            return "false";
        }
        return $value;
    }
    
    public static function getString($value)
    {
        if (is_null($value) || empty($value)) {
            return "";
        }
        return $value;
    }

    public static function getOrderTotalWithoutDecimals($value)
    {
        if (is_null($value) || empty($value)) {
            return 0;
        }
        return strpos($value, ".") ? (double)Tools::substr($value, 0, strpos($value, ".")) : $value;
    }


    public static function getCodigoPostal($value)
    {
        if (is_null($value) || empty($value)) {
            return $value;
        }
        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLB') {
            $value = str_replace('-', '', $value);
        } elseif (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLA') {
            $value = preg_replace("/[^0-9,.]/", "", $value);
        }
        return $value;
    }

    public static function getAttributesProduct($combinations)
    {
        $color  = "";
        $size  = "";
        foreach ($combinations as $value) {
            if ($value['group_name'] == 'Color') {
                $color = ' Color = ' .$value['attribute_name']. ' ';
                continue;
            }
            if ($value['group_name'] == 'Size') {
                $size = ' Size = ' .$value['attribute_name']. ' ';
                continue;
            }
        }
        return $color + $size;
    }

    public static function getIsoCodeStateById($id_state)
    {
        $result = Db::getInstance()->getRow('
        SELECT s.`iso_code` AS iso_code
        FROM `'._DB_PREFIX_.'state` s
        WHERE s.`id_state` = '.(int)$id_state);
        return isset($result['iso_code']) ? $result['iso_code'] : false;
    }
}
