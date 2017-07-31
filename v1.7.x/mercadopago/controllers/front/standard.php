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
 *  @author    henriqueleite
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */
class MercadoPagoStandardModuleFrontController extends ModuleFrontController
{
    protected $paymentMethod = '';
    protected $templateRedirectName = 'module:mercadopago/views/templates/front/payment_infos.tpl';
    protected $templateIframeName = 'module:mercadopago/views/templates/front/iframe.tpl';
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $data = array();
        Tools::getValue('token');
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'mercadopago') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'standard'));
        }

        $cart = $this->context->cart;
        $messageLog =
            'MercadoPago - start payment process, method : '. $this->paymentMethod .
            ' by customer id : ' . $cart->id_customer;
        PrestaShopLogger::addLog($messageLog, 1, null, 'Cart', $cart->id, true);

        PrestaShopLogger::addLog('MercadoPago - get post parameters', 1, null, 'Cart', $cart->id, true);
        $postParameters = $this->getPreferencesStandard();

        $messageLog = 'MercadoPago - post parameters : ' . print_r($postParameters, true);
        PrestaShopLogger::addLog($messageLog, 1, null, 'Cart', $cart->id, true);

        PrestaShopLogger::addLog('MercadoPago - request sid', 1, null, 'Cart', $cart->id, true);

        error_log("=======postParameters=====".Tools::jsonEncode($postParameters));

        try {
            $result = MPApi::getInstanceMP()->createPreference($postParameters);
            error_log("=====RESULT====".Tools::jsonEncode($result));
            if (array_key_exists('init_point', $result['response'])) {
                $data['preferences_url'] = $result['response']['init_point'];
            } else {
                $data['preferences_url'] = null;
                PrestaShopLogger::addLog(
                    'MercadoPago::postProcess - An error occurred during preferences creation.'.
                    'Please check your credentials and try again.: ',
                    MPApi::ERROR,
                    0
                );
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mercado Pago - prefence not created', 3, null, 'Cart', $cart->id, true);
            $this->redirectError('ERROR_GENERAL_REDIRECT');
        }

        Tools::redirect($result['response']['init_point']);

    }

    public function createStandardCheckoutPreference()
    {
        $preferences = $this->getPrestashopPreferencesStandard(null);
        if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
            PrestaShopLogger::addLog("=====preferences=====".Tools::jsonEncode($preferences), MPApi::INFO, 0);
        }
        return $this->mercadopago->createPreference($preferences);
    }

    private function getPreferencesStandard()
    {
        $customer_fields = Context::getContext()->customer->getFields();
        $cart = Context::getContext()->cart;

        $mercadopagoSettings = $this->getMercadoPagoSettings();

        // Get costumer data
        $address_invoice = new Address((integer) $cart->id_address_invoice);
        $phone = $address_invoice->phone;
        $phone .= $phone == '' ? '' : '|';
        $phone .= $address_invoice->phone_mobile;
        $customer_data = array(
            'first_name' => $customer_fields['firstname'],
            'last_name' => $customer_fields['lastname'],
            'email' => $customer_fields['email'],
            'phone' => array(
                'area_code' => '-',
                'number' => $phone,
            ),
            'address' => array(
                'zip_code' => $address_invoice->postcode,
                'street_name' => $address_invoice->address1.' - '.$address_invoice->address2.' - '.
                     $address_invoice->city.'/'.$address_invoice->country,
                    'street_number' => '-',
            ),
            // just have this data when using credit card
            'identification' => array(
                'number' => '',
                'type' => '',
            ),
        );

        // items
        $products = $cart->getProducts();
        $items = array();
        $summary = '';
        $round_place = 2;

        if ($mercadopagoSettings['country'] == 'MCO') {
            $round_place = 0;
        }

        foreach ($products as $key => $product) {
            $image = Image::getCover($product['id_product']);
            $product_image = new Product($product['id_product'], false, Context::getContext()->language->id);
            $link = new Link();//because getImageLInk is not static function
            $imagePath = $link->getImageLink(
                $product_image->link_rewrite,
                $image['id_image'],
                ""
            );

            $item = array(
                'id' => $product['id_product'],
                'title' => $product['name'],
                'description' => $product['description_short'],
                'quantity' => $product['quantity'],
                'unit_price' => round($product['price_wt'], $round_place),
                'picture_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$imagePath,
                'category_id' => $mercadopagoSettings['category_id'],
            );
            if ($key == 0) {
                $summary .= $product['name'];
            } else {
                $summary .= ', '.$product['name'];
            }
            $items[] = $item;
        }
        // include wrapping cost
        $wrapping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
        if ($wrapping_cost > 0) {
            $item = array(
                'title' => 'Wrapping',
                'description' => 'Wrapping service used by store',
                'quantity' => 1,
                'unit_price' => $wrapping_cost,
                'category_id' => $mercadopagoSettings['category_id'],
                'currency_id' => $cart->id_currency,
            );
            $items[] = $item;
        }
        // include discounts
        $discounts = (double) $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        if ($discounts > 0) {
            $item = array(
                'title' => 'Discount',
                'description' => 'Discount provided by store',
                'quantity' => 1,
                'unit_price' => -$discounts,
                'category_id' => $mercadopagoSettings['category_id'],
            );
            $items[] = $item;
        }

        $shipments = array();
        // include shipping cost
        $shipping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        if ($shipping_cost > 0) {
            $item = array(
                'title' => 'Shipping',
                'description' => 'Shipping service used by store',
                'quantity' => 1,
                'unit_price' => $shipping_cost,
                'category_id' => $mercadopagoSettings['category_id'],
            );
            $items[] = $item;
        }

        $data = array(
            'external_reference' => $cart->id,
            'customer' => $customer_data,
            'items' => $items,
            'shipments' => $shipments,
        );
        if (!MPApi::getInstanceMP()->isTestUser()) {
            switch ($mercadopagoSettings['country']) {
                case 'MLB':
                    $data['sponsor_id'] = 178326379;
                    break;
                case 'MLM':
                    $data['sponsor_id'] = 187899553;
                    break;
                case 'MLA':
                    $data['sponsor_id'] = 187899872;
                    break;
                case 'MCO':
                    $data['sponsor_id'] = 187900060;
                    break;
                case 'MLV':
                    $data['sponsor_id'] = 187900246;
                    break;
                case 'MLC':
                    $data['sponsor_id'] = 187900485;
                    break;
                case 'MPE':
                    $data['sponsor_id'] = 217182014;
                    break;
                case 'MLU':
                    $data['sponsor_id'] = 241730009;
                    break;
            }
        }
        $data['auto_return'] = $mercadopagoSettings['auto_return'] == 'approved' ? 'approved' : '';
        $data['back_urls']['success'] = $this->getURLReturn($cart->id, $mercadopagoSettings, 'success');
        $data['back_urls']['failure'] = $this->getURLReturn($cart->id, $mercadopagoSettings, 'failure');
        $data['back_urls']['pending'] = $this->getURLReturn($cart->id, $mercadopagoSettings, 'pending');
        $data['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentMethods();
        $data['payment_methods']['excluded_payment_types'] = array();
        $data['payment_methods']['installments'] = (integer) $mercadopagoSettings['installments'];

        $data['notification_url'] = $this->context->link->getModuleLink(
            'mercadopago',
            'standardreturn',
            array('checkout' => 'standard',
            'cart_id' => $cart_id),
            true
        );

        // swap to payer index since customer is only for transparent
        $data['customer']['name'] = $data['customer']['first_name'];
        $data['customer']['surname'] = $data['customer']['last_name'];
        $data['payer'] = $data['customer'];
        unset($data['customer']);

        return $data;
    }

    private function getURLReturn($cart_id, $mercadopagoSettings, $typeReturn)
    {
        $statusUrl = $this->context->link->getModuleLink(
            'mercadopago',
            'validationstandard',
            array('checkout' => 'standard',
            'cart_id' => $cart_id,
            'typeReturn' => $typeReturn),
            true
        );

        error_log("=====URL DE RETORNO NOVA=====".$statusUrl);

        return $statusUrl;
    }

    private function redirectError($returnMessage)
    {
        $this->errors[] = $this->module->getLocaleErrorMapping($returnMessage);
        $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, array(
            'step' => '3')));
    }

    private function getMercadoPagoSettings()
    {
        $mercadoPagoSettings = array();
        $mercadoPagoSettings['client_id']      = Configuration::get('MERCADOPAGO_CLIENT_ID');
        $mercadoPagoSettings['client_secret'] = Configuration::get('MERCADOPAGO_CLIENT_SECRET');
        $mercadoPagoSettings['standardActive'] = Configuration::get('MERCADOPAGO_STARDAND_ACTIVE');
        $mercadoPagoSettings['country'] = Configuration::get('MERCADOPAGO_COUNTRY');
        $mercadoPagoSettings['auto_return'] = true;
        $mercadoPagoSettings['category_id'] = Configuration::get('MERCADOPAGO_CATEGORY');
        $mercadoPagoSettings['ssl_enabled'] = Configuration::get('PS_SSL_ENABLED');
        $mercadoPagoSettings['installments'] = Configuration::get('MERCADOPAGO_INSTALLMENTS');


        error_log("====PS_SSL_ENABLED======".Configuration::get('PS_SSL_ENABLED'));


        return $mercadoPagoSettings;
    }

    private function getExcludedPaymentMethods()
    {
        $payment_methods = MPApi::getInstanceMP()->getPaymentMethods();
        $excluded_payment_methods = array();

        foreach ($payment_methods as $payment_method) {
            $pm_variable_name = 'MERCADOPAGO_'.$payment_method['id'].'_ACTIVE';

            $value = Configuration::get($pm_variable_name);

            error_log("=====value payment ===" . $value);
            if ($value == '0') {
                $excluded_payment_methods[] = array(
                    'id' => $payment_method['id'],
                );
            }
        }
        return $excluded_payment_methods;
    }

}
