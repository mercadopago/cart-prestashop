<?php

class Hook extends HookCore
{
    public static function getHookModuleExecList($hook_name = null)
    {
        $base = HookCore::getHookModuleExecList($hook_name);
        if (Configuration::get('MERCADOPAGO_CARRIER') != null && Configuration::get('MERCADOENVIOS_ACTIVATE') == "true") {
            $lista_shipping = (array)Tools::jsonDecode(
                Configuration::get('MERCADOPAGO_CARRIER'),true
            );
            $mpCarrier = $lista_shipping['MP_SHIPPING'];
            if ($hook_name == 'displayPayment') {
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
