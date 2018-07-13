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
                                                'started' => 'MERCADOPAGO_STATUS_11'
                                            );
    public static function logMensagem($mensagem, $nivel)
    {
        $version = UtilMercadoPago::getPrestashopVersion();
        $data_hora = date("F j, Y, g:i a");
        if ($version >= 6) {
            PrestaShopLogger::addLog(
                $data_hora."===".$mensagem,
                $nivel,
                0,
                null,
                null,
                true
            );
        } else {
            error_log($data_hora."===".$mensagem);
        }
    }  

    /*
    User Errors...
    */
    public static function log($msg, $exceptionMessage)
    {
        $date = date('d.m.Y h:i:s');
        $log = "Date:  ".$date."  | ".$msg.
        "|  " . $exceptionMessage . "\n";
        error_log($log, 3, _PS_ROOT_DIR_ . '/modules/mercadopago/logs/mercadopago.log');
    }    

    public static function setNamePaymentType($payment_type_id)
    {
        if ($payment_type_id == "ticket") {
            $displayName = "Mercado Pago - ticket";
        } elseif ($payment_type_id == "atm") {
            $displayName = "Mercado Pago - ATM";
        } elseif ($payment_type_id == "credit_card") {
            $displayName = "Mercado Pago - Credit card";
        } elseif ($payment_type_id == "debit_card") {
            $displayName = "Mercado Pago - Debit card";
        } elseif ($payment_type_id == "prepaid_card") {
            $displayName = "Mercado Pago - Prepaid card";
        } else {
            $displayName = "Mercado Pago";
        }
        return $displayName;
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

        $sql = "SELECT id_product
                FROM "._DB_PREFIX_."product
                WHERE width = 0 OR height = 0 OR depth = 0 OR weight = 0";

        $dados = Db::getInstance()->executeS($sql);

        if ($dados) {
            $requirements['dimensoes'] = 'negative';
        } else {
            $requirements['dimensoes'] = 'positive';
        }

        $requirements['ssl'] = Configuration::get('PS_SSL_ENABLED') == 0 ? "negative" : "positive";

        return $requirements;
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

    public static function getOrderTotalMLC_MCO($value)
    {
        if (is_null($value) || empty($value)) {
            return 0;
        }
        return strpos($value,".") ? (double)substr($value, 0, strpos($value,".")) : $value;
    }


    /**
     * Get an order by its cart id.
     *
     * @param int $id_cart Cart id
     *
     * @return array Order details
     */
    public static function getOrderByCartId($id_cart)
    {
        $sql = 'SELECT `id_order`
            FROM `'._DB_PREFIX_.'orders`
            WHERE `id_cart` = '.(int) $id_cart
            .Shop::addSqlRestriction().' order by id_order desc';
        $result = Db::getInstance()->getRow($sql);

        return isset($result['id_order']) ? $result['id_order'] : false;
    }

}
