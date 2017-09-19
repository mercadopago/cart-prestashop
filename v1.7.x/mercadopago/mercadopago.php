<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined("_PS_VERSION_")) {
    exit;
}

include dirname(__FILE__)."/includes/MPApi.php";

class MercadoPago extends PaymentModule
{
    protected $html = "";
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    protected $selectedTab = false;

    public $secret_key;
    public $client_id;

    private static $dimensionUnit = '';
    private static $weightUnit = '';
    private $dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
    private $weightUnitList = array('KG' => 'KGS', 'KGS' => 'KGS', 'LBS' => 'LBS', 'LB' => 'LBS');

    public $id_carrier;
    public static $listShipping;
    public static $listCache = array();
    public static $countryOptions = array(
        'MLA' => array(
            'normal' => array(
                'value' => 73328, 'label' => 'MercadoEnvios - OCA Estándar',
                'description' => 'MercadoEnvios - OCA Estándar',
            ),
            'expresso' => array(
                'value' => 73330, 'label' => 'MercadoEnvios - OCA Prioritario',
                'description' => 'MercadoEnvios - OCA Prioritario',
            ),
            'MP_SHIPPING_MIN_W' => 10,
            'MP_SHIPPING_MAX_W' => 70,
            'MP_SHIPPING_MIN_H' => 10,
            'MP_SHIPPING_MAX_H' => 70,
            'MP_SHIPPING_MIN_D' => 10,
            'MP_SHIPPING_MAX_D' => 70,
            'MP_SHIPPING_MIN_WE' => 100,
            'MP_SHIPPING_MAX_WE' => 25000
        ),
        'MLB' => array(
            'normal' => array(
                'value' => 100009, 'label' => 'MercadoEnvios - Normal',
                'description' => 'MercadoEnvios - Normal',
            ),
            'expresso' => array(
                'value' => 182, 'label' => 'MercadoEnvios - Expresso',
                'description' => 'MercadoEnvios - Expresso',
            ),
            'MP_SHIPPING_MIN_W' => 16,
            'MP_SHIPPING_MAX_W' => 105,
            'MP_SHIPPING_MIN_H' => 11,
            'MP_SHIPPING_MAX_H' => 105,
            'MP_SHIPPING_MIN_D' => 2,
            'MP_SHIPPING_MAX_D' => 105,
            'MP_SHIPPING_MIN_WE' => 100,
            'MP_SHIPPING_MAX_WE' => 15000
        ),
        'MLM' => array(
            'normal' => array(
                'value' => 501245, 'label' => 'MercadoEnvios - DHL Estándar',
                'description' => 'MercadoEnvios - DHL Estándar',
            ),
            'expresso' => array(
                'value' => 501345, 'label' => 'MercadoEnvios - DHL Express',
                'description' => 'MercadoEnvios - DHL Express',
            ),
            'MP_SHIPPING_MIN_W' => 10,
            'MP_SHIPPING_MAX_W' => 80,
            'MP_SHIPPING_MIN_H' => 10,
            'MP_SHIPPING_MAX_H' => 80,
            'MP_SHIPPING_MIN_D' => 10,
            'MP_SHIPPING_MAX_D' => 120,
            'MP_SHIPPING_MIN_WE' => 100,
            'MP_SHIPPING_MAX_WE' => 30000
        ),
    );

    public function __construct()
    {
        $this->name = "mercadopago";
        $this->tab = "payments_gateways";
        $this->version = "1.0.8";
        $this->ps_versions_compliancy = array("min" => "1.7", "max" => _PS_VERSION_);
        $this->author = "Mercado Pago";
        $this->controllers = array("validationstandard", "standardreturn");
        $this->has_curl = function_exists('curl_version');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = "checkbox";
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?', array(), 'Modules.Mercadopago.Admin');
        if (!isset($this->access_key) || !isset($this->secret_key)) {
            $this->warning = $this->l('Your Mercado Pago details must be configured before using this module.', array(), 'Modules.Mercadopago.Admin');
        }
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l("Mercado Pago");
        $this->description = $this->l('Receive your payments using Mercado Pago, you can using the Checkout Standard.', array(), 'Modules.Mercadopago.Admin');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l("No currency has been set for this module.", array(), 'Modules.Mercadopago.Admin');
        }
    }

    public function install()
    {
        $returnStatus = $this->createStates();
        return parent::install() &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('displayOrderDetail') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayPayment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('payment') &&
            $this->registerHook('header');
    }


    public function hookPayment($params)
    {
        error_log("entro no hookPayment");
    }
    public function hookDisplayPayment($params)
    {
        error_log("entro no hookDisplayPayment");
        return $this->hookPayment($params);
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int) $params['id_order']);

        $statusOrder = '';
        $id_order_state = $this->getOrderStatePending($order->id);

        if ($id_order_state == Configuration::get('MERCADOPAGO_STATUS_7')) {
            $statusOrder = 'Pendente';
        }

        $token_form = Tools::getAdminToken('AdminOrder'.Tools::getValue('id_order'));

        $data = array(
            'id_order' => $params['id_order'],
            'token_form' => $token_form,
            'statusOrder' => $statusOrder,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                                     htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
            'cancel_action_url' => $this->context->link->getModuleLink(
                'mercadopago',
                'cancelorder',
                array(),
                true
            )
        );
        $data["payment_pos_action_url"] = $this->context->link->getModuleLink(
            'mercadopago',
            'paymentpos',
            array(),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );

        $this->context->smarty->assign('pos_active', Configuration::get('MERCADOPAGO_POINT'));
        error_log("==MERCADOPAGO_POINT==" . Configuration::get('MERCADOPAGO_POINT'));
        error_log("==MERCADOPAGO_POINT==" . $id_order_state);
        error_log("==MERCADOPAGO_STATUS_11==" . Configuration::get('MERCADOPAGO_STATUS_11'));

        if (Configuration::get('MERCADOPAGO_POINT') == "true" &&
            $id_order_state == Configuration::get('MERCADOPAGO_STATUS_11')) {
            $data['pos_options'] = $this->loadPoints();
            $data['showPoint'] = 'true';
        } else {
            $data['showPoint'] = 'false';
        }

        $id_order_carrier = $order->getIdOrderCarrier();

        $order_carrier = new OrderCarrier($id_order_carrier);
        $id_mercadoenvios_service_code = $this->isMercadoEnvios($order_carrier->id_carrier);
        if ($id_mercadoenvios_service_code > 0) {
            $order_payments = $order->getOrderPayments();
            foreach ($order_payments as $order_payment) {
                $result = MPApi::getInstanceMP()->getPaymentStandard($order_payment->transaction_id);
                error_log(Tools::jsonEncode($result));
                if ($result['status'] == '200') {
                    $payment_info = $result['response'];
                    if (isset($payment_info['collection'])) {
                        $merchant_order_id = $payment_info['collection']['merchant_order_id'];
                        $result_merchant = MPApi::getInstanceMP()->getMerchantOrder($merchant_order_id);
                        $return_tracking = $this->setTracking(
                            $order,
                            $result_merchant['response']['shipments'],
                            false
                        );
                        $tag_shipment = MPApi::getInstanceMP()->getTagShipment(
                            $return_tracking['shipment_id']
                        );
                        $tag_shipment_zebra = MPApi::getInstanceMP()->getTagShipmentZebra(
                            $return_tracking['shipment_id']
                        );

                        $return_tracking['tag_shipment_zebra'] = $tag_shipment_zebra;

                        $return_tracking['tag_shipment'] = $tag_shipment;
                        $this->context->smarty->assign($return_tracking);
                    }
                }
                break;
            }
        }

        $this->context->smarty->assign($data);

        return $this->display(__file__, '/views/templates/hook/display_admin_order.tpl');
    }

    private function setTracking($order, $shipments, $update)
    {
        error_log("entrou aqui setTracking " . $update);
        $shipment_id = null;
        $retorno = null;
        foreach ($shipments as $shipment) {
            if ($shipment['shipping_mode'] != 'me2') {
                continue;
            }

            $shipment_id = $shipment['id'];
            $response_shipment = MPApi::getInstanceMP()->getTracking($shipment_id);
            $response_shipment = $response_shipment['response'];
            $tracking_number = $response_shipment['tracking_number'];

            if ($response_shipment['tracking_number'] != 'pending') {
                $status = '';
                switch ($response_shipment['status']) {
                    case 'ready_to_ship':
                        $status = $this->l('Ready to ship', array(), 'Modules.Mercadopago.Admin');
                        break;
                    default:
                        $status = $response_shipment['status'];
                        break;
                }

                switch ($response_shipment['substatus']) {
                    case 'ready_to_print':
                        $substatus_description = $this->l('Tag ready to print', array(), 'Modules.Mercadopago.Admin');
                        break;
                    case 'printed':
                        $substatus_description = $this->l('Tag printed', array(), 'Modules.Mercadopago.Admin');
                        break;
                    case 'stale':
                        $substatus_description = $this->l('Unsuccessful', array(), 'Modules.Mercadopago.Admin');
                        break;
                    case 'delayed':
                        $substatus_description = $this->l('Sending the delayed path', array(), 'Modules.Mercadopago.Admin');
                        break;
                    case 'receiver_absent':
                        $substatus_description = $this->l('Missing recipient for delivery', array(), 'Modules.Mercadopago.Admin');
                        break;
                    case 'returning_to_sender':
                        $substatus_description = $this->l('In return to sender', array(), 'Modules.Mercadopago.Admin');
                        break;
                    case 'claimed_me':
                        $substatus_description = $this->l('Buyer initiates complaint and requested a refund.', array(), 'Modules.Mercadopago.Admin');
                        break;
                    default:
                        $substatus_description = $response_shipment['substatus'];
                        break;
                }
                $estimated_delivery = new DateTime(
                    $response_shipment['shipping_option']
                    ['estimated_delivery_time']
                    ['date']
                );
                $estimated_handling_limit = new DateTime(
                    $response_shipment['shipping_option']
                    ['estimated_handling_limit']
                    ['date']
                );
                $estimated_delivery_final = new DateTime(
                    $response_shipment['shipping_option']
                    ['estimated_delivery_final']
                    ['date']
                );
                $retorno = array(
                    'shipment_id' => $shipment_id,
                    'tracking_number' => $tracking_number,
                    'name' => $response_shipment['shipping_option']['name'],
                    'status' => $status,
                    'substatus' => $response_shipment['substatus'],
                    'substatus_description' => $substatus_description,
                    'estimated_delivery' => $estimated_delivery->format('d/m/Y'),
                    'estimated_handling_limit' => $estimated_handling_limit->format('d/m/Y'),
                    'estimated_delivery_final' => $estimated_delivery_final->format('d/m/Y'),
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                );
                error_log("update===". $update);
                error_log("tracking_number===". $tracking_number);
                $id_order_carrier = $order->getIdOrderCarrier();
                $order_carrier = new OrderCarrier($id_order_carrier);
                $order_carrier->tracking_number = $tracking_number;
                $order_carrier->update();
                // if ($update) {
                //     $id_order_carrier = $order->getIdOrderCarrier();
                //     $order_carrier = new OrderCarrier($id_order_carrier);
                //     $order_carrier->tracking_number = $tracking_number;
                //     $order_carrier->update();
                // }
            } else {
                $retorno = array(
                    'shipment_id' => $shipment_id,
                    'tracking_number' => '',
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                );
            }
        }

        return $retorno;
    }

    private function isMercadoEnvios($id_carrier)
    {
        error_log("=====isMercadoEnvios id_carrier=====" . $id_carrier);
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );

        error_log("=====isMercadoEnvios id_carrier=====" . Tools::jsonEncode(Configuration::get('MERCADOPAGO_CARRIER')));

        $id_mercadoenvios_service_code = 0;
        if (isset($lista_shipping['MP_CARRIER']) &&
            array_key_exists($id_carrier, $lista_shipping['MP_CARRIER'])) {
            $id_mercadoenvios_service_code = $lista_shipping['MP_CARRIER'][$id_carrier];
        }

        return $id_mercadoenvios_service_code;
    }

    public function hookDisplayOrderDetail($params)
    {
        error_log("entrou aqui hookDisplayOrderDetail");
        if ($params['order']->module == 'mercadopago') {
            $order = new Order(Tools::getValue('id_order'));
            $order_payments = $order->getOrderPayments();
            foreach ($order_payments as $order_payment) {
                $result = MPApi::getInstanceMP()->getPayment($order_payment->transaction_id);
                if ($result['status'] == '404' || $result['status'] == '401' ) {
                    $result = MPApi::getInstanceMP()->getPaymentStandard($order_payment->transaction_id);

                    $result_merchant = MPApi::getInstanceMP()->getMerchantOrder(
                        $result['response']['collection']['merchant_order_id']
                    );
                }
                if ($result['status'] == 200) {
                    $payment_info = $result['response']['collection'];

                    $id_mercadoenvios_service_code = $this->isMercadoEnvios($order->id_carrier);
                    error_log("entrou aqui hookDisplayOrderDetail id_mercadoenvios_service_code = " .$id_mercadoenvios_service_code);
                    if ($id_mercadoenvios_service_code > 0) {
                        $merchant_order_id = $payment_info['merchant_order_id'];
                        $result_merchant = MPApi::getInstanceMP()->getMerchantOrder($merchant_order_id);
                        $return_tracking = $this->setTracking(
                            $order,
                            $result_merchant['response']['shipments'],
                            true
                        );
                        $this->context->smarty->assign($return_tracking);
                    }

                    $payment_type_id = isset($payment_info['payment_type_id']) ?
                    isset($payment_info['payment_type_id']) : $payment_info['payment_type'];


                    if ($payment_type_id == 'ticket' || $payment_type_id == 'atm') {
                        $settings = array(
                            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                            'boleto_url' => urldecode($payment_info['transaction_details']['external_resource_url']),
                            'payment_type_id' => $payment_type_id,
                        );
                        $this->context->smarty->assign($settings);
                    }
                }
                break;
            }
            return $this->display(__file__, '/views/templates/hook/print_details_order.tpl');
        }
    }

    /**
     * Verify if there is state approved for order.
     */
    public static function getOrderStatePending($id_order)
    {
        $select = 'SELECT id_order_state FROM '._DB_PREFIX_.'order_history WHERE id_order = '.
        (int) $id_order.
        ' order by date_add desc limit 1;';

        error_log('====select=====' . $select);

        $result = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($select));

        if ($result) {
            return $result[0]['id_order_state'];
        } else {
            return 0;
        }
    }

    /**
     * Create the states, we need to check if doens`t exists.
     */
    private function createStates()
    {
        $order_states = array(
            array(
                '#ccfbff',
                $this->l('Transaction in Process', array(), 'Modules.Mercadopago.Admin'),
                'in_process',
                '010010000',
            ),
            array(
                '#c9fecd',
                $this->l('Transaction Finished', array(), 'Modules.Mercadopago.Admin'),
                'payment',
                '110010010',
            ),
            array(
                '#fec9c9',
                $this->l('Transaction Cancelled', array(), 'Modules.Mercadopago.Admin'),
                'order_canceled',
                '010010000',
            ),
            array(
                '#fec9c9',
                $this->l('Transaction Rejected', array(), 'Modules.Mercadopago.Admin'),
                'payment_error',
                '010010000',
            ),
            array(
                '#ffeddb',
                $this->l('Transaction Refunded', array(), 'Modules.Mercadopago.Admin'),
                'refund',
                '110010000',
            ),
            array(
                '#c28566',
                $this->l('Transaction Chargedback', array(), 'Modules.Mercadopago.Admin'),
                'charged_back',
                '010010000',
            ),
            array(
                '#b280b2',
                $this->l('Transaction in Mediation', array(), 'Modules.Mercadopago.Admin'),
                'in_mediation',
                '010010000',
            ),
            array(
                '#fffb96',
                $this->l('Transaction Pending', array(), 'Modules.Mercadopago.Admin'),
                'pending',
                '010010000',
            ),
            array(
                '#3333FF',
                $this->l('Ready to Ship', array(), 'Modules.Mercadopago.Admin'),
                'ready_to_ship',
                '010010000',
            ),
            array(
                '#8A2BE2',
                $this->l('Shipped', array(), 'Modules.Mercadopago.Admin'),
                'shipped',
                '010010000',
            ),
            array(
                '#ffeddb',
                $this->l('Delivered'),$this->l('Delivered', array(), 'Modules.Mercadopago.Admin'),
                'delivered',
                '010010000',
            ),
            array(
                '#37bf3a',
                $this->l('Transaction Started'),
                'started',
                '010010000',
            )

        );

        foreach ($order_states as $key => $value) {
            if (!is_null($this->orderStateAvailable(Configuration::get('MERCADOPAGO_STATUS_'.$key)))) {
                continue;
            } else {
                $order_state = new OrderState();
                $order_state->name = array();
                $order_state->module_name = $this->name;
                $order_state->send_email = true;
                $order_state->color = $value[0];
                $order_state->hidden = false;
                $order_state->delivery = false;
                $order_state->logable = true;
                $order_state->invoice = false;
                $order_state->paid = false;

                if ($value[2] == 'payment' || $value[2] == 'refund') {
                    // $order_state->send_email = false;
                    $order_state->invoice = true;
                }
                if ($value[2] == 'payment') {
                    // $order_state->send_email = false;
                    $order_state->paid = true;
                }
                if ($value[2] == 'delivered') {
                    // $order_state->send_email = false;
                    $order_state->delivery = true;
                }
                if ($value[2] == 'shipped') {
                    // $order_state->send_email = false;
                    $order_state->shipped = true;
                }
                if ($value[2] == 'started') {
                    // $order_state->send_email = false;
                    $order_state->logable = false;
                    $order_state->invoice = false;
                }

                $order_state->name = array();
                $order_state->template = array();

                foreach (Language::getLanguages(false) as $language) {
                    $order_state->name[(int) $language['id_lang']] = $value[1];
                    $order_state->template[$language['id_lang']] = $value[2];

                    if ($value[2] == 'in_process' || $value[2] == 'pending' || $value[2] == 'charged_back' ||
                         $value[2] == 'in_mediation') {
                        $this->populateEmail($language['iso_code'], $value[2], 'html');
                        $this->populateEmail($language['iso_code'], $value[2], 'txt');
                    }
                }

                if (!$order_state->add()) {
                    return false;
                }

                $file = _PS_ROOT_DIR_.'/img/os/'.(int) $order_state->id.'.gif';
                copy((dirname(__file__).'/views/img/mp_icon.gif'), $file);

                Configuration::updateValue('MERCADOPAGO_STATUS_'.$key, $order_state->id);
            }
        }
        return true;
    }

    private function populateEmail($lang, $name, $extension)
    {
        if (!file_exists(_PS_MAIL_DIR_.$lang)) {
            mkdir(_PS_MAIL_DIR_.$lang, 0777, true);
        }
        $new_template = _PS_MAIL_DIR_.$lang.'/'.$name.'.'.$extension;

        if (!file_exists($new_template)) {
            $template = dirname(__file__).'/mails/'.$name.'.'.$extension;
            copy($template, $new_template);
        }
    }

    /**
     * Check if the state exist before create another one.
     *
     * @param int $id_order_state
     *                            State ID
     *
     * @return bool availability
     */
    public static function orderStateAvailable($id_order_state)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            '
            SELECT `id_order_state` AS ok
            FROM `'._DB_PREFIX_.'order_state`
            WHERE `id_order_state` = '.(int) $id_order_state
        );
        error_log("===result status===".$result['ok']);
        return $result['ok'];
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName("MERCADOPAGO_CHECKOUT_DISPLAY")
            || !Configuration::deleteByName("MERCADOPAGO_STARDAND_ACTIVE")
            || !Configuration::deleteByName("MERCADOENVIOS_ACTIVATE")
            // || !Configuration::deleteByName("MERCADOENVIOS_ACTIVATE")
            // || !Configuration::deleteByName("MERCADOPAGO_CLIENT_SECRET")
            // || !Configuration::deleteByName("MERCADOPAGO_CLIENT_ID")
            // || !Configuration::deleteByName("MERCADOPAGO_INSTALLMENTS")
            // || !Configuration::deleteByName("MERCADOPAGO_CATEGORY")

            || !$this->unregisterHook("paymentOptions")
            || !$this->unregisterHook("displayOrderDetail")
            || !$this->unregisterHook("displayAdminOrder")
            || !$this->unregisterHook("displayPayment")
            || !$this->unregisterHook("paymentReturn")
            || !$this->unregisterHook("payment")
            || !$this->unregisterHook("header")
            || !parent::uninstall()) {
                return false;
        }

        return true;
    }

    public function hookHeader($parameters)
    {
        $this->context->controller->addCSS(($this->_path).'views/css/mercadopago.css', 'all');
    }

    public function getContent()
    {
        $shopDomainSsl = Tools::getShopDomainSsl(true, true);
        $backOfficeCssUrl = $shopDomainSsl.__PS_BASE_URI__."modules/".$this->name."/views/css/backoffice.css";
        $marketingCssUrl = $shopDomainSsl.__PS_BASE_URI__."modules/".$this->name."/views/css/marketing.css";
        $backOfficeJsUrl = $shopDomainSsl.__PS_BASE_URI__."modules/".$this->name."/views/js/backoffice.js";

        $tplVars = array(
            "tabs" => $this->getConfigurationTabs(),
            "selectedTab" => $this->getSelectedTab(),
            "backOfficeJsUrl" => $backOfficeJsUrl,
            "backOfficeCssUrl" => $backOfficeCssUrl,
            "marketingCssUrl" => $marketingCssUrl
        );

        if (isset($this->context->cookie->mercadoPagoConfigMessage)) {
            $tplVars["message"]["success"] = $this->context->cookie->mercadoPagoMessageSuccess;
            $tplVars["message"]["text"] = $this->context->cookie->mercadoPagoConfigMessage;
            unset($this->context->cookie->mercadoPagoConfigMessage);
        } else {
            $tplVars["message"] = false;
        }

        $this->context->smarty->assign($tplVars);

        return $this->display(__FILE__, "views/templates/admin/tabs.tpl");
    }

    protected function getSelectedTab()
    {
        if ($this->selectedTab) {
            return $this->selectedTab;
        }

        if (Tools::getValue("selected_tab")) {
            return Tools::getValue("selected_tab");
        }

        return "presentation";
    }

    protected function getConfigurationTabs()
    {
        $tabsLocale = $this->getTabsLocale();
        $tabs = array();

        // $tabs[] = array(
        //     "id" => "presentation",
        //     "title" => $tabsLocale["presentation"],
        //     "content" => $this->getPresentationTemplate()
        // );

        /*$tabs[] = array(
            "id" => "requirements",
            "title" => $tabsLocale["requirements"],
            "content" => $this->getPageRequirements()
        );*/

        $tabs[] = array(
            "id" => "general_setting",
            "title" => $tabsLocale["settings"],
            "content" => $this->salveGeneralSetting()
        );

        $tabs[] = array(
            "id" => "payment_configuration",
            "title" => $tabsLocale["paymentsConfig"],
            "content" => $this->getPaymentConfigurationTemplate()
        );

        return $tabs;
    }
    protected function getPaymentConfigurationTemplate()
    {
        $locale = $this->getPaymentConfigurationLocale();
        if ($this->existCredentials()) {
            if (Tools::isSubmit("btnSubmitPaymentConfig")) {
                $this->selectedTab = "payment_configuration";
                $this->updatePaymentConfig();
            }
            $paymentsResult = MPApi::getInstanceMP()->getPaymentMethods();

            $i = 0;
            $payments = array();
            foreach ($paymentsResult as $paymentMethod) {
                $paymentTypeLowerCase = Tools::strtolower($paymentMethod["name"]);

                $activeConfigName = Configuration::get("MERCADOPAGO_".$paymentMethod["id"]."_ACTIVE");
                $modeConfigName = Configuration::get("MERCADOPAGO_".$paymentMethod["id"]."_MODE");

                $payments[$i]["id"] = $paymentMethod["id"];
                $payments[$i]["title"] = $paymentTypeLowerCase;
                $payments[$i]["type"] = $paymentMethod["payment_type_id"];
                $payments[$i]["active"] = Tools::getValue("MERCADOPAGO_".$paymentMethod["id"]."_ACTIVE", $activeConfigName);
                $payments[$i]["mode"] = Tools::getValue("MERCADOPAGO_".$paymentMethod["id"]."_MODE", $modeConfigName);
                $payments[$i]["brand"] = $paymentMethod["secure_thumbnail"];
                $payments[$i]["tooltips"] = "";

                error_log("".$paymentMethod["id"]);

                $i++;
            }

            $tplVars = array(
                "country" => Configuration::get("MERCADOPAGO_COUNTRY"),
                "show" => true,
                "mercadoPagoActive" => Configuration::get("MERCADOPAGO_STARDAND_ACTIVE"),
                "mercadoEnviosActivate" => Configuration::get("MERCADOENVIOS_ACTIVATE"),
                "panelTitle" => "teste",
                "payments" => $payments,
                "thisPath" => Tools::getShopDomain(true, true).__PS_BASE_URI__."modules/mercadopago/",
                "fieldsValue" => $this->getPaymentConfiguration(),
                "currentIndex" => $this->getAdminModuleLink(),
                "label" => $locale["label"],
                "button" => $locale["button"]
            );
        } else {
            $tplVars = array(
                "show" => false,
                "mercadoPagoActive" => false,
                "mercadoEnviosActivate" => false,
                "panelTitle" => "teste",
                "payments" => array(),
                "thisPath" => Tools::getShopDomain(true, true).__PS_BASE_URI__."modules/mercadopago/",
                "fieldsValue" => $this->getPaymentConfiguration(),
                "currentIndex" => $this->getAdminModuleLink(),
                "label" => $locale["label"],
                "button" => $locale["button"]
            );
        }
        $this->context->smarty->assign($tplVars);

        return $this->display(__FILE__, "views/templates/admin/paymentConfiguration.tpl");
    }


    protected function getPaymentConfigurationLocale()
    {
        $locale = array();

        $locale["label"]["active"] = $this->l("Enabled");
        $locale["label"]["disable"] = $this->l("Disable");

        $locale["paymentsConfig"] = $this->l("Payment Configuration");


        $locale["flexible"]["tooltips"] =
                $this->l("When enabled, all single payment methods will be disabled");

        $locale["button"]["save"] = $this->l("Save");
        $locale["button"]["yes"] = $this->l("Yes");
        $locale["button"]["no"] = $this->l("No");

        return $locale;
    }

    protected function getAdminModuleLink()
    {
        $adminLink = $this->context->link->getAdminLink("AdminModules", false);
        $module = "&configure=".$this->name."&tab_module=".$this->tab."&module_name=".$this->name;
        $adminToken = Tools::getAdminTokenLite("AdminModules");

        return $adminLink.$module."&token=".$adminToken;
    }

    protected function getPaymentConfiguration()
    {
        $saveConfig = array();
        return $saveConfig;
    }

    protected function salveGeneralSetting()
    {
        if (Tools::isSubmit("btnSubmit")) {
            error_log("getGeneralSetting");
            $this->selectedTab = "general_setting";
            $this->validateGeneralSetting();
        }

        $this->html .= $this->renderGeneralSettingForm();

        return $this->html;
    }

    protected function validateGeneralSetting()
    {
        if (Tools::isSubmit("btnSubmit")) {
            $locale = $this->getGeneralSettingLocale();
            $isRequired = false;
            $fieldsRequired = array();

            if (trim(Tools::getValue("MERCADOPAGO_CLIENT_ID")) == ""
            && trim(Configuration::get("MERCADOPAGO_CLIENT_ID")) == "") {
                $fieldsRequired[] =  $locale["client_id"]["label"];
                $isRequired = true;
            }
            if (trim(Tools::getValue("MERCADOPAGO_CLIENT_SECRET")) == ""
            && trim(Configuration::get("MERCADOPAGO_CLIENT_SECRET")) == "") {
                $fieldsRequired[] =  $locale["client_secret"]["label"];
                $isRequired = true;
            }

            if (trim(Tools::getValue("MERCADOPAGO_CHECKOUT_DISPLAY")) == ""
            && trim(Configuration::get("MERCADOPAGO_CHECKOUT_DISPLAY")) == "") {
                $fieldsRequired[] =  $locale["checkout_display"]["label"];
                $isRequired = true;
            }

            if (trim(Tools::getValue("MERCADOPAGO_INSTALLMENTS")) == ""
            && trim(Configuration::get("MERCADOPAGO_INSTALLMENTS")) == "") {
                $fieldsRequired[] =  $locale["checkout_installments"]["label"];
                $isRequired = true;
            }

            if ($isRequired) {
                $warning = implode(", ", $fieldsRequired) . " ";
                if ($this->l("ERROR_MANDATORY") == "ERROR_MANDATORY") {
                    $warning .= $this->l("is required. Please fill this field.");
                } else {
                    $warning .= $this->l("ERROR_MANDATORY");
                }
                $this->context->cookie->mercadoPagoMessageSuccess = false;
                $this->context->cookie->mercadoPagoConfigMessage = $warning;
            } else {
                $this->updateGeneralSetting();

            }
        }
    }

    protected function updateGeneralSetting()
    {
        if (Tools::isSubmit("btnSubmit")) {
            $client_id = Tools::getValue("MERCADOPAGO_CLIENT_ID");
            $client_secret = Tools::getValue("MERCADOPAGO_CLIENT_SECRET");

            Configuration::updateValue("MERCADOPAGO_CLIENT_ID", Tools::getValue("MERCADOPAGO_CLIENT_ID"));
            Configuration::updateValue("MERCADOPAGO_CLIENT_SECRET", Tools::getValue("MERCADOPAGO_CLIENT_SECRET"));
            Configuration::updateValue("MERCADOPAGO_CHECKOUT_DISPLAY", Tools::getValue("MERCADOPAGO_CHECKOUT_DISPLAY"));
            Configuration::updateValue("MERCADOPAGO_CATEGORY", Tools::getValue("MERCADOPAGO_CATEGORY"));

            error_log("====MERCADOPAGO_INSTALLMENTS====".Tools::getValue("MERCADOPAGO_INSTALLMENTS"));

            Configuration::updateValue("MERCADOPAGO_INSTALLMENTS", Tools::getValue("MERCADOPAGO_INSTALLMENTS"));

            Configuration::updateValue("MERCADOPAGO_COUNTRY", MPApi::getInstanceMP()->getCountry());

            //BY DEFAULT SET THE BUTTON THAT ACTIVE THE PAYMENT FOR ENABLE
            Configuration::updateValue("MERCADOPAGO_STARDAND_ACTIVE", "true");
            $this->selectedTab = "payment_configuration";

            error_log("====selectedTab====".$this->selectedTab);
            $successMessage = $this->l("Settings successfully saved");
            $this->context->cookie->mercadoPagoMessageSuccess = true;
            $this->context->cookie->mercadoPagoConfigMessage = $successMessage;
        }
    }


    private function existCredentials()
    {
        $client_id = Configuration::get("MERCADOPAGO_CLIENT_ID");
        $client_secret = Configuration::get("MERCADOPAGO_CLIENT_SECRET");
        if (trim($client_id) == "" || trim($client_secret) == "") {
            return false;
        }
        return true;
    }

    protected function getPresentationTemplate()
    {
        $vars = array(
            "thisPath" => $this->_path
        );
        $this->context->smarty->assign($vars);
        return $this->display(__FILE__, "views/templates/admin/presentation.tpl");
    }

    protected function getPageRequirements()
    {
        $tplVars = array(
            "thisPath" => $this->_path
        );
        $this->context->smarty->assign($tplVars);
        return $this->display(__FILE__, "views/templates/admin/requirements.tpl");
    }


    protected function getTabsLocale()
    {
        $locale = array();
        $locale["presentation"] = $this->l("Presentation");
        $locale["requirements"] = $this->l("Requirement");
        $locale["settings"] = $this->l("Basic Settings");
        $locale["paymentsConfig"] = $this->l("Payment Settings");

        return $locale;
    }

    protected function updatePaymentConfig()
    {
        if (Tools::isSubmit("btnSubmitPaymentConfig")) {

            foreach (MPApi::getInstanceMP()->getPaymentMethods() as $paymentMethod) {
                $active = Tools::getValue("MERCADOPAGO_".$paymentMethod["id"]."_ACTIVE");
                $mode = Tools::getValue("MERCADOPAGO_".$paymentMethod["id"]."_MODE");
                Configuration::updateValue("MERCADOPAGO_".$paymentMethod["id"]."_ACTIVE", $active);
                Configuration::updateValue("MERCADOPAGO_".$paymentMethod["id"]."_MODE", $mode);
            }

            Configuration::updateValue("MERCADOPAGO_STARDAND_ACTIVE", Tools::getValue("MERCADOPAGO_STARDAND_ACTIVE"));

            $mercadoenvios_activate = Tools::getValue("MERCADOENVIOS_ACTIVATE");
            Configuration::updateValue("MERCADOENVIOS_ACTIVATE", $mercadoenvios_activate);
            if ($mercadoenvios_activate == 1 &&
                count(Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'))) == 0) {
                $this->setCarriers();
            } elseif ($mercadoenvios_activate == 0 && count(Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'))) > 0) {
                $this->removeMercadoEnvios();
            }

            if ($this->l("SUCCESS_GENERAL_PAYMENTCONFIG") == "SUCCESS_GENERAL_PAYMENTCONFIG") {
                $successMessage = $this->l("Congratulations, your payments configuration were successfully updated.");
            } else {
                $successMessage = $this->l("SUCCESS_GENERAL_PAYMENTCONFIG");
            }

            $this->context->cookie->mercadoPagoMessageSuccess = true;
            $this->context->cookie->mercadoPagoConfigMessage = $successMessage;
        }
    }

    protected function renderGeneralSettingForm()
    {
        $locale = $this->getGeneralSettingLocale();

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get("PS_LANG_DEFAULT"));
        $helper->default_form_language = $lang->id;
        if (Configuration::get("PS_BO_ALLOW_EMPLOYEE_FORM_LANG")) {
            $helper->allow_employee_form_lang =  Configuration::get("PS_BO_ALLOW_EMPLOYEE_FORM_LANG");
        } else {
            $helper->allow_employee_form_lang =  0;
        }
        $this->fields_form = array();
        $this->fields_form = $this->getGeneralSettingForm($locale);

        $helper->id = (int)Tools::getValue("id_carrier");
        $helper->identifier = $this->identifier;
        $helper->submit_action = "btnSubmit";
        $helper->currentIndex = $this->getAdminModuleLink();
        $helper->token = Tools::getAdminTokenLite("AdminModules");
        $helper->tpl_vars = array(
            "fields_value" => $this->getGeneralSetting(),
            "languages" => $this->context->controller->getLanguages(),
            "id_language" => $this->context->language->id
        );

        return $helper->generateForm($this->fields_form);
    }

    protected function getGeneralSettingForm($locale)
    {
        $getDisplayList = $this->getDisplayList($locale["checkout_display"]);
        $getDisplayCategoryList = $this->getDisplayCategoryList($locale["checkout_display_category"]);

        $generalForm = array();
        $generalForm[] = array(
            "form" => array(
                "input" => array(
                    $this->getTextForm("CLIENT_ID", $locale["client_id"], true),
                    $this->getTextForm("CLIENT_SECRET", $locale["client_secret"], true),
                    $this->getTextForm("INSTALLMENTS", $locale["checkout_installments"], true),
                    $this->getSelectForm("CHECKOUT_DISPLAY", $locale["checkout_display"], $getDisplayList),
                    $this->getSelectForm("CATEGORY", $locale["checkout_display_category"], $getDisplayCategoryList),
                ),
                "submit" => array(
                    "title" => $locale["save"]
                )
            )
        );

        return $generalForm;
    }

    private function getTextForm($pm, $locale, $requirement = false)
    {
        $textForm =
            array(
               "type" => "text",
               "label" => @$locale["label"],
               "name" => "MERCADOPAGO_".$pm,
               "required" => $requirement,
               "desc" => @$locale["desc"]
            );

        return $textForm;
    }

    private function getPasswordForm($pm, $locale, $requirement = false)
    {
        $passwordForm =
            array(
               "type" => "password",
               "label" => $locale["label"],
               "name" => "MERCADOPAGO_".$pm,
               "required" => $requirement,
               "desc" => $locale["desc"]
            );

        return $passwordForm;
    }

    private function getSelectForm($pm, $locale, $selectList)
    {
        $selectForm = array(
            "type"      => "select",
            "label"     => $locale["label"],
            "name"      => "MERCADOPAGO_".$pm,
            "desc"      => $locale["desc"],
            "options"   => array(
               "query" => $selectList,
               "id" => "id",
               "name"   => "name"
            )
        );
        return $selectForm;
    }

    private function getDisplayList($display)
    {
        $displayList = array (
            // array(
            //    "id" => "IFRAME",
            //    "name"   => $display["iframe"]
            // ),
            array(
               "id"     => "REDIRECT",
               "name"   => $display["redirect"]
            )
        );

        return $displayList;
    }


    private function getDisplayCategoryList($display)
    {
        $displayList = array (
            array(
               "id" => "others",
               "name"   => $display["others"]
            ),
            array(
               "id" => "art",
               "name"   => $display["art"]
            ),
            array(
               "id"     => "baby",
               "name"   => $display["baby"]
            ),
            array(
               "id"     => "coupons",
               "name"   => $display["coupons"]
            ),
            array(
               "id"     => "donations",
               "name"   => $display["donations"]
            ),
            array(
               "id"     => "cameras",
               "name"   => $display["cameras"]
            ),
            array(
               "id"     => "video_games",
               "name"   => $display["video_games"]
            ),
            array(
               "id"     => "television",
               "name"   => $display["television"]
            ),
            array(
               "id"     => "car_electronics",
               "name"   => $display["car_electronics"]
            ),
            array(
               "id"     => "electronics",
               "name"   => $display["electronics"]
            ),
            array(
               "id"     => "automotive",
               "name"   => $display["automotive"]
            ),
            array(
               "id"     => "entertainment",
               "name"   => $display["entertainment"]
            )

            ,
            array(
               "id"     => "fashion",
               "name"   => $display["fashion"]
            ),
            array(
               "id"     => "games",
               "name"   => $display["games"]
            ),
            array(
               "id"     => "home",
               "name"   => $display["home"]
            ),
            array(
               "id"     => "musical",
               "name"   => $display["musical"]
            ),
            array(
               "id"     => "phones",
               "name"   => $display["phones"]
            ),
            array(
               "id"     => "services",
               "name"   => $display["services"]
            ),
            array(
               "id"     => "learnings",
               "name"   => $display["learnings"]
            ),
            array(
               "id"     => "tickets",
               "name"   => $display["tickets"]
            ),
            array(
               "id"     => "travels",
               "name"   => $display["travels"]
            ),
            array(
               "id"     => "virtual_goods",
               "name"   => $display["virtual_goods"]
            )
        );

        return $displayList;
    }

    protected function getGeneralSettingLocale()
    {
        $locale = array();
        $locale["setting"]["label"] = $this->l("Configuration");

        $locale["client_id"]["label"] = "Client ID";
        $locale["client_secret"]["label"] = "Client Secret";

        $locale["checkout_display"]["label"] = $this->l("Visualization mode");
        $locale["checkout_display"]["label"] = "Display";
        $locale["checkout_display"]["iframe"] = "iFrame";
        $locale["checkout_display"]["redirect"] = "Redirect";

        $locale["checkout_display"]["desc"] =
                $this->l("iFrame – We enable within your checkout an area with enviroment of Mercado Mercado,
                Redirect – The client will be redirect to Mercadopago environment (Recommended).");

        //descrições
        $locale["client_id"]["desc"] =
                $this->l("This field is required and you can't to show to other people.
                For more information: https://www.mercadopago.com.br/developers/en/solutions/payments/basic-checkout/receive-payments.");
        $locale["client_secret"]["desc"] =
               $this->l("This field is required and you can't to show to other people.
                For more information: https://www.mercadopago.com.br/developers/en/solutions/payments/basic-checkout/receive-payments.");

        $locale["checkout_installments"]["label"] = $this->l("Installments");
        $locale["checkout_installments"]["desc"] = $this->l("Inform the allowed amount of parcels that the customers can install, maximum 24.");

        $locale["checkout_display_category"]["label"] = "Categoria";
        $locale["checkout_display_category"]["others"] = "Other categories";

        $locale["checkout_display_category"]["desc"] = "Selecione a categoria da sua loja.";
        $locale["checkout_display_category"]["art"] = "Collectibles & Art";
        $locale["checkout_display_category"]["baby"] = "Toys for Baby, Stroller, Stroller Accessories, Car Safety Seats";

        $locale["checkout_display_category"]["coupons"] = "Coupons";
        $locale["checkout_display_category"]["donations"] = "Donations";

        $locale["checkout_display_category"]["computing"] = "Computers & Tablets";
        $locale["checkout_display_category"]["cameras"] = "Cameras & Photography";
        $locale["checkout_display_category"]["video_games"] = "Video Games & Consoles";
        $locale["checkout_display_category"]["television"] = "LCD, LED, Smart TV, Plasmas, TVs";
        $locale["checkout_display_category"]["car_electronics"] = "Car Audio, Car Alarm Systems & Security, Car DVRs, Car Video Players, Car PC";
        $locale["checkout_display_category"]["electronics"] = "Audio & Surveillance, Video & GPS, Others";
        $locale["checkout_display_category"]["automotive"] = "Parts & Accessories";
        $locale["checkout_display_category"]["entertainment"] = "Music, Movies & Series, Books, Magazines & Comics, Board Games & Toys";

        $locale["checkout_display_category"]["fashion"] = "Men\'s, Women\'s, Kids & baby, Handbags & Accessories, Health & Beauty, Shoes, Jewelry & Watches";
        $locale["checkout_display_category"]["games"] = "Online Games & Credits";
        $locale["checkout_display_category"]["home"] = "Home appliances. Home & Garden";
        $locale["checkout_display_category"]["musical"] = "Instruments & Gear";
        $locale["checkout_display_category"]["phones"] = "Cell Phones & Accessories";
        $locale["checkout_display_category"]["services"] = "General services";
        $locale["checkout_display_category"]["learnings"] = "Trainings, Conferences, Workshops";
        $locale["checkout_display_category"]["tickets"] = "Tickets for Concerts, Sports, Arts, Theater, Family, Excursions tickets, Events & more";
        $locale["checkout_display_category"]["travels"] = "Plane tickets, Hotel vouchers, Travel vouchers";
        $locale["checkout_display_category"]["virtual_goods"] = "E-books, Music Files, Software, Digital Images,".
        "PDF Files and any item which can be electronically stored in a file, Mobile Recharge, DTH Recharge and any Online Recharge";

        $locale["save"] = $this->l("Save");

        return $locale;
    }

    protected function getGeneralSetting()
    {
        $configClient_id = Configuration::get("MERCADOPAGO_CLIENT_ID");
        $configClientSecret = Configuration::get("MERCADOPAGO_CLIENT_SECRET");
        $configDisplay = Configuration::get("MERCADOPAGO_CHECKOUT_DISPLAY");
        $configDisplayCategory = Configuration::get("MERCADOPAGO_CATEGORY");

        $configDisplayInstallments = Configuration::get("MERCADOPAGO_INSTALLMENTS");

        if ((int)$configDisplayInstallments == 0) {
            $configDisplayInstallments  = 12;
        }

        $generalSetting = array();
        $generalSetting["MERCADOPAGO_CLIENT_ID"] =
            Tools::getValue("MERCADOPAGO_CLIENT_ID", $configClient_id);
        $generalSetting["MERCADOPAGO_CLIENT_SECRET"] =
            Tools::getValue("MERCADOPAGO_CLIENT_SECRET", $configClientSecret);

        $generalSetting["MERCADOPAGO_CHECKOUT_DISPLAY"] =
            Tools::getValue("MERCADOPAGO_CHECKOUT_DISPLAY", $configDisplay);

        $generalSetting["MERCADOPAGO_CATEGORY"] =
            Tools::getValue("MERCADOPAGO_CATEGORY", $configDisplayCategory);

        $generalSetting["MERCADOPAGO_INSTALLMENTS"] =
            Tools::getValue("MERCADOPAGO_INSTALLMENTS", $configDisplayInstallments);

        return $generalSetting;
    }

    /*fim admin*/

    public function hookPaymentOptions($params)
    {
        error_log("======ENTROU NO hookPaymentOptions=======");

        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params["cart"])) {
            return;
        }
        $payment_options = [
            $this->getExternalPaymentOption()
        ];

        return $payment_options;
    }

    public function hookPaymentReturn($parameters)
    {
        error_log("==========hookPaymentReturn=====");
        if (!$this->active) {
            return;
        }
        error_log("==========hookPaymentReturn=====");
        $currency = $this->context->currency;

        $logo_mercadopago = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/logo-mercadopago.png');

        if (Tools::getValue('payment_method_id') == 'bolbradesco' ||
            Tools::getValue('payment_type') == 'bank_transfer' ||
            Tools::getValue('payment_type') == 'atm' || Tools::getValue('payment_type') == 'ticket') {
            error_log("====boleto_url====". Tools::getValue('boleto_url'));
            $boleto_url = Tools::getValue('boleto_url');
            if (Configuration::get('PS_SSL_ENABLED')) {
                $boleto_url = str_replace("http", "https", $boleto_url);
            }

            $this->context->smarty->assign(
                array(
                    'logo_mercadopago' => $logo_mercadopago,
                    'payment_id' => Tools::getValue('payment_id'),
                    'payment_status' => Tools::getValue('payment_status'),
                    'boleto_url' => Tools::getValue('boleto_url'),
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                )
            );
            error_log("vai retornar aqui no boleto");
            return $this->display(__FILE__, 'views/templates/hook/displayStatusOrderTicket.tpl');
        } else {
            $this->context->smarty->assign(
                array(
                    'logo_mercadopago' => $logo_mercadopago,
                    'payment_status' => Tools::getValue('payment_status'),
                    'status_detail' => Tools::getValue('status_detail'),
                    'card_holder_name' => Tools::getValue('card_holder_name'),
                    'four_digits' => Tools::getValue('four_digits'),
                    'payment_method_id' => Tools::getValue('payment_method_id'),
                    'installments' => Tools::getValue('installments'),
                    'transaction_amount' => Tools::displayPrice(
                        Tools::getValue('amount'),
                        $currency,
                        false
                    ),
                    'statement_descriptor' => Tools::getValue('statement_descriptor'),
                    'payment_id' => Tools::getValue('payment_id'),
                    'amount' => Tools::displayPrice(
                        Tools::getValue('amount'),
                        $currency,
                        false
                    ),
                    'this_path_ssl' => (
                        Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://'
                        ).htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                )
            );
            error_log("vai retornar o cartão");
            return $this->display(__FILE__, 'views/templates/hook/displayStatusOrder.tpl');
        }
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module["id_currency"]) {
                    return true;
                }
            }
        }
        return false;
    }
            //die($this->module->getTranslator()->trans('This payment method is not available.', array(), 'Modules.Wirepayment.Shop'));
    public function getExternalPaymentOption()
    {
        error_log("URL DE ENVIO DO PAGAMENTO====" . $this->context->link->getModuleLink($this->name, "standard", array(), true));
        $country = strtoupper(MPApi::getInstanceMP()->getCountry());
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Mercado Pago Redirect'))
                       ->setAction($this->context->link->getModuleLink($this->name, "standard", array(), true))
                       ->setModuleName($this->name)
                       ->setInputs([
                            "token" => [
                                "name" =>"token",
                                "type" =>"hidden",
                                "value" =>"12345689",
                            ],
                        ]);

// ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name."/views/img/".$country."/mercadopago_468X60.jpg")

        return $externalOption;
    }

    protected function generateForm()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = sprintf("%02d", $i);
        }

        $years = [];
        for ($i = 0; $i <= 10; $i++) {
            $years[] = date("Y", strtotime("+".$i." years"));
        }

        $this->context->smarty->assign([
            "action" => $this->context->link->getModuleLink($this->name, "validation", array(), true),
            "months" => $months,
            "years" => $years,
        ]);

        return $this->context->smarty->fetch("module:paymentexample/views/templates/front/payment_form.tpl");
    }

    public function getMappingError($idError)
    {
        switch ($idError) {
            case 'ERROR_PENDING':
                $message = $this->l('Unfortunately, the confirmation of your payment failed.
                    Please contact your merchant for clarification.');
            break;
            default:
                $message = "";
            break;
        }
        return $message;
    }

    private function getCountry($client_id, $client_secret)
    {
        $mp = new MPApi($client_id, $client_secret);

        return $mp->getCountry();
    }

    public function setCarriers()
    {
        $country = $this->getCountry(
            Configuration::get('MERCADOPAGO_CLIENT_ID'),
            Configuration::get('MERCADOPAGO_CLIENT_SECRET')
        );
        $normal = self::$countryOptions[$country]['normal'];
        $expresso = self::$countryOptions[$country]['expresso'];

        $carrierConfig = array(
            0 => array('name' => $normal['label'],
                'carrier_code' => $normal['value'],
                'id_tax_rules_group' => 0,
                'active' => true,
                'deleted' => 0,
                'shipping_handling' => false,
                'range_behavior' => 0,
                'delay' => array(
                    'ar' => "Después de la publicación, recibirá el producto en",
                    'br' => "Após a postagem, você o receberá o produto em até",
                    'mx' => "Después de la publicación, recibirá el producto en",
                    'es' => "After the posting, you will receive the product within",
                    Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => $normal['description'],
                ),
                'id_zone' => 1,
                'is_module' => true,
                'shipping_external' => true,
                'external_module_name' => 'mercadopago',
                'need_range' => true,
            ),
            1 => array('name' => $expresso['label'],
                'carrier_code' => $expresso['value'],
                'id_tax_rules_group' => 0,
                'active' => true,
                'deleted' => 0,
                'shipping_handling' => false,
                'range_behavior' => 0,
                'delay' => array(
                    'ar' => "Después de la publicación, recibirá el producto en",
                    'br' => "Após a postagem, você o receberá o produto em até",
                    'mx' => "Después de la publicación, recibirá el producto en",
                    'es' => "After the posting, you will receive the product within",
                    Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => $expresso['description'],
                ),
                'id_zone' => 1,
                'is_module' => true,
                'shipping_external' => true,
                'external_module_name' => 'mercadopago',
                'need_range' => true,
            ),
        );

        $id_carrier1 = $this->installExternalCarrier($carrierConfig[0]);
        $id_carrier2 = $this->installExternalCarrier($carrierConfig[1]);

        Configuration::updateValue('MERCADOPAGO_CARRIER_ID_1', (int) $id_carrier1);
        Configuration::updateValue('MERCADOPAGO_CARRIER_ID_2', (int) $id_carrier2);

        $shipping = array();

        $shipping['MP_CARRIER'][$id_carrier1] = $normal['value'];
        $shipping['MP_CARRIER'][$id_carrier2] = $expresso['value'];

        $shipping['MP_SHIPPING'][$normal['value']] = $id_carrier1;
        $shipping['MP_SHIPPING'][$expresso['value']] = $id_carrier2;

        self::$listShipping = $shipping;

        Configuration::updateValue(
            'MERCADOPAGO_CARRIER',
            Tools::jsonEncode($shipping)
        );
    }
    public static function installExternalCarrier($config)
    {
        $carrier = new Carrier();
        $carrier->name = $config['name'];
        $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
        $carrier->id_zone = $config['id_zone'];
        $carrier->active = $config['active'];
        $carrier->deleted = $config['deleted'];
        $carrier->delay = $config['delay'];
        $carrier->shipping_handling = $config['shipping_handling'];
        $carrier->range_behavior = $config['range_behavior'];
        $carrier->is_module = $config['is_module'];
        $carrier->shipping_external = $config['shipping_external'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->need_range = $config['need_range'];

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'br') {
                $carrier->delay[(int) $language['id_lang']] = $config['delay'][$language['iso_code']];
            } elseif ($language['iso_code'] == 'es') {
                $carrier->delay[(int) $language['id_lang']] = $config['delay'][$language['iso_code']];
            } elseif ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int) $language['id_lang']] = $config['delay'][$language['iso_code']];
            }
        }

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->insert(
                    'carrier_group',
                    array('id_carrier' => (int) ($carrier->id),
                    'id_group' => (int) ($group['id_group']),
                    )
                );
            }

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '30.000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);
            foreach ($zones as $zone) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_zone' => (int) ($zone['id_zone']),
                    )
                );
                Db::getInstance()->insert(
                    'delivery',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_range_price' => (int) ($rangePrice->id),
                        'id_range_weight' => null,
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                    ),
                    null
                );
                Db::getInstance()->insert(
                    'delivery',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_range_price' => null,
                        'id_range_weight' => (int) ($rangeWeight->id),
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                    ),
                    null
                );
            }

            // Copy Logo
            @copy(dirname(__FILE__).'/views/img/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int) $carrier->id.'.jpg');

            // Return ID Carrier
            return (int) ($carrier->id);
        }

        return false;
    }

    private function removeMercadoEnvios()
    {
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER')
        );
        if (isset($lista_shipping['MP_SHIPPING'])) {
            foreach ($lista_shipping['MP_SHIPPING'] as $id_carrier) {
                $carrier = new Carrier($id_carrier);
                $carrier->deleted = true;
                $carrier->active = false;
                $carrier->save();
            }
            Configuration::deleteByName('MERCADOPAGO_CARRIER');
        }
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );

        $mpCarrier = $lista_shipping['MP_SHIPPING'];
        if (in_array($this->id_carrier, $mpCarrier)) {
            $retorno = $this->calculate($params, $this->id_carrier);
            $shipping_cost = (float) $retorno['cost'];
            if ($retorno != null) {
                return (float)$shipping_cost;
            }
        }

        return false;
    }

    public function getDimensions(&$products)
    {
        $width = 0;
        $height = 0;
        $depth = 0;
        $weight = 0;

        foreach ($products as &$product) {
            if ($product['weight']) {
                if (self::$weightUnit == 'KGS') {
                    $product['weight2'] = $product['weight'] * 1000;
                } elseif (self::$weightUnit == 'LBS') {
                    $product['weight2'] = $product['weight'] * 453.59237;
                } else {
                    $product['weight2'] = 0;
                }
            } else {
                $product['weight2'] = 0;
            }
            if (self::$dimensionUnit == 'CM') {
                $product['width2'] = $product['width'];
                $product['height2'] = $product['height'];
                $product['depth2'] = $product['depth'];
            } elseif (self::$dimensionUnit == 'IN') {
                $product['width2'] = $product['width'] * 2.54;
                $product['height2'] = $product['height'] * 2.54;
                $product['depth2'] = $product['depth'] * 2.54;
            } else {
                $product['width2'] = 0;
                $product['height2'] = 0;
                $product['depth2'] = 0;
            }
        }
        if (Configuration::get('shipping_calc_mode') == 'longer_side') {
            foreach ($products as $p) {
                if ($p['width2'] && $p['width2'] > $width) {
                    $width = $p['width2'];
                }
                if ($p['height2'] && $p['height2'] > $height) {
                    $height = $p['height2'];
                }
                if ($p['depth2'] && $p['depth2'] > $depth) {
                    $depth = $p['depth2'];
                }
                if ($p['weight2']) {
                    $weight += ($p['weight2'] * $p['quantity']);
                } else {
                    $weight += $this->config['default_weight'];
                }
            }
        } else {
            foreach ($products as $p) {
                $dimensions = array(0, 0, 0);
                $dimensions[0] = $p['width2'] > 0.01 ? $p['width2'] : Configuration::get('default_width');
                $dimensions[1] = $p['height2'] > 0.01 ? $p['height2'] : Configuration::get('default_height');
                $dimensions[2] = $p['depth2'] > 0.01 ? $p['depth2'] : Configuration::get('default_depth');
                sort($dimensions);
                for ($i = 0; $i < $p['quantity']; ++$i) {
                    $width = max($width, $dimensions[1]);
                    $height = max($height, $dimensions[2]);
                    $depth += $dimensions[0];
                    $sort_dim = array( $width, $height, $depth );
                    sort($sort_dim);
                    $depth = $sort_dim[0];
                    $height = $sort_dim[1];
                    $width = $sort_dim[2];
                }
                $weight += ($p['weight2'] > 0.1 ? $p['weight2'] : Configuration::get('default_weight')) * $p['quantity'];
            }
        }

        $config_shipment = MercadoPago::$countryOptions[Configuration::get('MERCADOPAGO_COUNTRY')];

        $width = max($width, $config_shipment['MP_SHIPPING_MIN_W']);
        //$width = min($width, $this->settings['MP_SHIPPING_MAX_W']);
        $height = max($height, $config_shipment['MP_SHIPPING_MIN_H']);
        //$height = min($height, $this->settings['MP_SHIPPING_MAX_H']);
        $depth = max($depth, $config_shipment['MP_SHIPPING_MIN_D']);
        //$depth = min($depth, $this->settings['MP_SHIPPING_MAX_D']);
        $weight = max($weight, $config_shipment['MP_SHIPPING_MIN_WE']);
        //$weight = min($weight, $this->settings['MP_SHIPPING_MAX_WE']);
        return array(
            'width' => (int)Tools::ps_round($width, 0),// > 0.01 ? $width : $this->config['default_width'], 0),
            'height' => (int)Tools::ps_round($height, 0),// > 0.01 ? $height : $this->config['default_height'], 0),
            'depth' => (int)Tools::ps_round($depth, 0),// > 0.01 ? $depth : $this->config['default_depth'], 0),
            'weight' => (int)Tools::ps_round($weight, 0)// > 0.1 ? $weight : $this->config['default_weight'], 0),
        );
    }

    private function calculate($params, $id_carrier)
    {
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );
        $id_mercadoenvios_service_code = $lista_shipping['MP_CARRIER'][$id_carrier];

        $cart = Context::getContext()->cart;
        $price_total = 0;

        // Init var
        $address = new Address($params->id_address_delivery);
        $products = $cart->getProducts();
        $mp = MPApi::getInstanceMP();

        $dimensions = $this->getDimensions($products);

        $postcode = $address->postcode;
        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLB') {
            $postcode = str_replace('-', '', $postcode);
        } /*elseif (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLA') {
            $postcode = Tools::substr($postcode, 1);
        }*/
        $return = null;
        $paramsMP = array(
        'dimensions' =>
                "{$dimensions['width']}x{$dimensions['height']}x".
                "{$dimensions['depth']},{$dimensions['weight']}",

        'zip_code' => $postcode,

        'item_price' => (double) number_format($price_total, 2, '.', ''),
        'free_method' => '', // optional
        );

        $response = $mp->calculateEnvios($paramsMP);

        if ($response['status'] == '200' && isset($response['response']['options'])) {
            $shipping_options = $response['response']['options'];
            foreach ($shipping_options as $shipping_option) {
                $value = $shipping_option['shipping_method_id'];
                $shipping_speed = $shipping_option['estimated_delivery_time']['shipping'];
                if ($value == $id_mercadoenvios_service_code) {
                    $return = array(
                        'name' => $shipping_option['name'],
                        'checked' => $shipping_option['display'] == 'recommended' ? "checked='checked'" : '',
                        'shipping_speed' => $shipping_speed,
                        'estimated_delivery' => $shipping_speed < 24 ? 1 : ceil($shipping_speed / 24),
                        'cost' => $shipping_option['cost'] == 0 ? 'FREE' : $shipping_option['cost'],
                    );
                    break;
                }
            }
        } else {
            $this->context->smarty->assign(
                $this->setErrorMercadoEnvios(
                    $this->errorMercadoEnvios(
                        $response
                    )
                )
            );
        }

        return $return;
    }
    private function calculateListCache($postcode)
    {
        $retorno = $this->calculateList($postcode);
        return $retorno;
    }

    private function calculateList($postcode)
    {
        $cart = Context::getContext()->cart;
        $products = $cart->getProducts();
        $price_total = 0;

        $mp = MPApi::getInstanceMP();

        // pega medidas dos produtos
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
        $error = "";
        foreach ($products as $product) {
            for ($qty = 0; $qty < $product['quantity']; ++$qty) {
                if ($product['width'] == 0) {
                    if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
                        $error = 'Invalid dimensions cart [width].';
                        PrestaShopLogger::addLog("=====dimensions=====".
                        $error, MPApi::ERROR, 0);
                    }

                    $this->context->smarty->assign(
                        $this->setErrorMercadoEnvios(
                            $error
                        )
                    );
                    return;
                }

                $price_total += $product['price_wt'];
                $width  += $product['width'];
                $height += $product['height'];
                $length += $product['depth'];
                $weight += $product['weight'] * 1000;
            }
        }

        $height = ceil($height);
        $width = ceil($width);
        $length = ceil($length);
        $weight = ceil($weight);

        if (!($height > 0 && $length > 0 && $width > 0 && $weight > 0)) {
            $error = 'Invalid dimensions cart [height,length, width, weight].';
            if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
                PrestaShopLogger::addLog("=====dimensions=====".$error, MPApi::ERROR, 0);
            }

            $this->context->smarty->assign(
                $this->setErrorMercadoEnvios($error)
            );

            return;
            //throw new Exception($error);
        }

        $dimensions = $height.'x'.$width.'x'.$length.','.$weight;
        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLB') {
            $postcode = str_replace('-', '', $postcode);
        } /*elseif (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLA') {
            $postcode = Tools::substr($postcode, 1);
        }*/

        $return = array();
        $paramsMP = array(
            'dimensions' => $dimensions,
            'zip_code' => $postcode,
            //'zip_code' => "5700",
            'item_price' => (double) number_format($price_total, 2, '.', ''),
            'free_method' => '', // optional
        );

        $response = $mp->calculateEnvios($paramsMP);

        if ($response['status'] == '200' && isset($response['response']['options'])) {
            $shipping_options = $response['response']['options'];
            foreach ($shipping_options as $shipping_option) {
                $value = $shipping_option['shipping_method_id'];
                $shipping_speed = $shipping_option['estimated_delivery_time']['shipping'];

                $return[$value] = array(
                    'name' => $shipping_option['name'],
                    'checked' => $shipping_option['display'],
                    'shipping_speed' => $shipping_speed,
                    'estimated_delivery' => $shipping_speed < 24 ? 1 : ceil($shipping_speed / 24),
                    'cost' => $shipping_option['cost'] == 0 ? 'FREE' : $shipping_option['cost'],
                );
            }
        } else {
            $this->context->smarty->assign(
                'mensagem',
                $this->errorMercadoEnvios(
                    $response
                )
            );

            return;
        }

        return $return;
    }


    private function setErrorMercadoEnvios($messageError)
    {
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );

        $errorShipment = array(
            'mensagem' => $this->l($messageError),
            'code_shipment' => Tools::jsonEncode($lista_shipping['MP_CARRIER']),
        );

        return $errorShipment;
    }

    private function errorMercadoEnvios($response)
    {
        $mensagem = '';

        if ($this->context->customer->isLogged()) {
            $status = $response['status'];
            if ($status == 200) {
                $mensagem = $this->l('Mercado Envios not loading.');
            } else {
                $error = $response['response']['error'];
                if ($error == 'invalid_zip_code') {
                    $mensagem = $this->l('Invalid zip code.');

                    return $mensagem;
                }
                switch ($status) {
                    case 404:
                        $mensagem = $this->l('Not found receiver address.');
                        break;
                    case 400:
                        $mensagem = $this->l('Invalid dimensions.');
                        break;
                    default:
                        $mensagem = $this->l('Mercado Envios not loading.');
                        break;
                }
            }
        }
        return $mensagem;
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
    /**
     * Check, if SSL is enabled during current connection
     * @return boolean
     */
    public function isSSLEnabled()
    {
        if (isset($_SERVER['HTTPS'])) {
            if (Tools::strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1') {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')) {
            return true;
        }
        return false;
    }
}
