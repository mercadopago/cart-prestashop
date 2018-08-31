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
 *  @author    MercadoPago
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

include_once dirname(__FILE__).'/../../mercadopago.php';
class MercadoPagoStandardPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        $this->placeOrder();
    }

    private function placeOrder()
    {
        $mercadopago = $this->module;
        $cart = Context::getContext()->cart;
        $result = $mercadopago->createStandardCheckoutPreference();
        UtilMercadoPago::log("response createStandardCheckoutPreference", Tools::jsonEncode($result));
        if (array_key_exists('init_point', $result['response'])) {
            $init_point = $result['response']['init_point'];
            Db::getInstance()->insert('mercadopago_orders_initpoint', array(
                'cart_id' => (int)$cart->id,
                'init_point'      => pSQL($init_point),
            ));
            Tools::redirect($init_point);
            return;
        } else {
            $data = array();
            $data['typeReturn'] = "failure";
            $data['init_point'] = "";
            $data['standard'] = "true";
            $data['status_detail'] = '';
            $data['one_step'] = Configuration::get('PS_ORDER_PROCESS_TYPE');
            $data['show_QRCode'] = "false";
            $data['this_path_ssl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                                     htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;


            UtilMercadoPago::logMensagem(
                'Occurred an error in payment, the id cart is ' .$cart->id,
                MPApi::ERROR,
                isset($result['response']['message']) ? $result['response']['message'] : "",
                true,
                null,
                "standardPayment->placeOrder"
            );
            $this->context->smarty->assign($data);
            $this->setTemplate('error.tpl');
        }
    }
}
