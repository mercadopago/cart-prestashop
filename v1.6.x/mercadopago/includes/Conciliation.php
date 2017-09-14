<?php
/**
 * 2007-2015 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License(OSL 3.0)
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
 *  @author    MERCADOPAGO.COM REPRESENTA&Ccedil;&Otilde;ES LTDA.
 *  @copyright Copyright(c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License(OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

if (!defined('_PS_VERSION_')) {
    exit();
}
class Conciliation
{
    public function getPayments($date_start, $date_end)
    {
        error_log("====insertMercadoPagoOrder====".$date_start);
        error_log("====insertMercadoPagoOrder====".$date_end);
        return "";
    }

    public function insertMercadoPagoOrder($values)
    {
        error_log("====insertMercadoPagoOrder====".Tools::jsonEncode($values));
        $returnInsert = "";
        /*
        $insertOrder = 'INSERT INTO ' .
        _DB_PREFIX_ . 'mercadopago_orders (cart_id, added, valid, ipn_status,
        transaction_id, email_client, payment_method, product) VALUES(' .
        $values['cart_id'] . ',\'' . pSql(date('Y-m-d h:i:s')) . '\','.$values['valid'].',\''.$values['ipn_status'].
        ',\''.$values['transaction_id']',\''.$values['email_client']',\''.$values['payment_method']',\''.$values['product'].
        '\')';

        try {
            $returnInsert = Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($insertOrder);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'MercadoPago :: ======returnInsert erro===== = '.
                $e->getMessage(),
                MPApi::ERROR,
                0
            );
        }*/
        return $returnInsert;
    }
}
