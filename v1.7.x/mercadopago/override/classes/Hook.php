<?php
/**
* 2007-2011 PrestaShop
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
* International Registered Trademark & Property of PrestaShop SA
*
*  @author    Mercado Pago
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of Mercado Pago
*/

class Hook extends HookCore
{
    public static function getHookModuleExecList($hook_name = null)
    {
        $base = HookCore::getHookModuleExecList($hook_name);
        if (Configuration::get('MERCADOPAGO_CARRIER') != null
            && Configuration::get('MERCADOENVIOS_ACTIVATE') == "true") {
            $lista_shipping = (array)Tools::jsonDecode(
                Configuration::get('MERCADOPAGO_CARRIER'),
                true
            );
            $mpCarrier = $lista_shipping['MP_SHIPPING'];
            if ($base && ($hook_name == 'displayPayment' || $hook_name == 'paymentOptions')) {
                $cart = Context::getContext()->cart;
                if (in_array($cart->id_carrier, $mpCarrier)) {
                    foreach ($base as $id => $data) {
                        if ($data['module'] != 'mercadopago') {
                            unset($base[$id]);
                        }
                    }
                }
            }
        }
        return $base;
    }
}
