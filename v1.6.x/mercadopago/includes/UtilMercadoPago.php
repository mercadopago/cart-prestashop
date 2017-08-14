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
    public static function logMensagem($mensagem, $nivel)
    {
        $version = UtilMercadoPago::getPrestashopVersion();
        $data_hora = date("F j, Y, g:i a");
        if ($version >= 6) {
            PrestaShopLogger::addLog(
                $data_hora."===".$mensagem,
                $nivel,
                0,
                'MercadoPago',
                null,
                true
            );
        }
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
            error_log("Mercado envios ativado");
            $sql = "SELECT id_product
            FROM "._DB_PREFIX_."product WHERE (width = 0 OR height = 0
            OR depth = 0
            OR weight = 0)
            AND online_only = 0
            AND available_for_order = 1
            AND active =1;";

            $dados = Db::getInstance()->executeS($sql);
            if ($dados) {
                $requirements['dimensoes'] = 'negative';
            } else {
                $requirements['dimensoes'] = 'positive';
            }
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
            error_log('==postcode===' . preg_replace("/[^0-9,.]/", "", $value));
            $value = preg_replace("/[^0-9,.]/", "", $value);
        }
        return $value;
    }

    public static function getAttributesProduct($combinations)
    {
        $color  = "";
        $size  = "";
        foreach ($combinations as $value) {
            error_log('==group_name===' . $value['group_name']);
            error_log('==attribute_name===' . $value['attribute_name']);
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
