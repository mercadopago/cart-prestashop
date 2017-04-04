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

function_exists('curl_init');
include dirname(__FILE__).'/includes/MPApi.php';

class MercadoPago extends PaymentModule
{
    public static $listShipping;

    public static $appended_text;

    public static $listCache = array();
    public $id_carrier;

    private $dimensionUnitList = array(
        'CM' => 'CM',
        'IN' => 'IN',
        'CMS' => 'CM',
        'INC' => 'IN',
    );
    private $weightUnitList = array('KG' => 'KGS',
        'KGS' => 'KGS',
        'LBS' => 'LBS',
        'LB' => 'LBS',
    );

    public static $countryOptions = array(
        'MLA' => array(
            'normal' => array(
                'value' => 73328, 'label' => 'MercadoEnvios - OCA Estándar',
                'description' => 'Después de la publicación, recibirá el producto en',
            ),
            'expresso' => array(
                'value' => 73330, 'label' => 'MercadoEnvios - OCA Prioritario',
                'description' => 'Después de la publicación, recibirá el producto en',
            ),
        ),
        'MLB' => array(
            'normal' => array(
                'value' => 100009, 'label' => 'MercadoEnvios - Normal',
                'description' => 'Após a postagem, você o receberá o produto em até',
            ),
            'expresso' => array(
                'value' => 182, 'label' => 'MercadoEnvios - Expresso',
                'description' => 'Após a postagem, você o receberá o produto em até',
            ),
        ),
        'MLM' => array(
            'normal' => array(
                'value' => 501245, 'label' => 'MercadoEnvios - DHL Estándar',
                'description' => 'Después de la publicación, recibirá el producto en',
            ),
            'expresso' => array(
                'value' => 501345, 'label' => 'MercadoEnvios - DHL Express',
                'description' => 'Después de la publicación, recibirá el producto en',
            ),
        ),
    );
    public function __construct()
    {
        $this->name = 'mercadopago';
        $this->tab = 'payments_gateways';
        $this->version = '3.4.1';
        $this->currencies = true;
        //$this->currencies_mode = 'radio';
        $this->need_instance = 0;
        $this->module_key = '4380f33bbe84e7899aacb0b7a601376f';
        $this->ps_versions_compliancy = array(
            'min' => '1.6',
            'max' => '1.7',
        );

        parent::__construct();

        $this->page = basename(__file__, '.php');
        $this->displayName = 'Mercado Pago';
        $this->description = $this->l(
            'Receive your payments using Mercado Pago, you can using the Custom Checkout or Checkout Standard.'
        );

        //Receba seus pagamentos utilizando o Mercado
        //Pago e receba em cartão de crédito e boletos através
        //no nosso checkout Transparente ou Padrão.

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall MercadoPago?');
        $this->textshowemail = $this->l('You must follow MercadoPago rules for purchase to be valid');
        $this->author = $this->l('MERCADOPAGO.COM Representações LTDA.');
        $this->link = new Link();
        $this->mercadopago = new MPApi(
            Configuration::get('MERCADOPAGO_CLIENT_ID'),
            Configuration::get('MERCADOPAGO_CLIENT_SECRET')
        );

        $this->currencies_mode = 'checkbox';

        //$this->bootstrap = true;
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

        return $result['ok'];
    }

    /**
     * Create the states, we need to check if doens`t exists.
     */
    public function createStates()
    {
        $order_states = array(
            array(
                '#ccfbff',
                $this->l('Transaction in Process'),
                'in_process',
                '010010000',
            ),
            array(
                '#c9fecd',
                $this->l('Transaction Finished'),
                'payment',
                '110010010',
            ),
            array(
                '#fec9c9',
                $this->l('Transaction Cancelled'),
                'order_canceled',
                '010010000',
            ),
            array(
                '#fec9c9',
                $this->l('Transaction Rejected'),
                'payment_error',
                '010010000',
            ),
            array(
                '#ffeddb',
                $this->l('Transaction Refunded'),
                'refund',
                '110010000',
            ),
            array(
                '#c28566',
                $this->l('Transaction Chargedback'),
                'charged_back',
                '010010000',
            ),
            array(
                '#b280b2',
                $this->l('Transaction in Mediation'),
                'in_mediation',
                '010010000',
            ),
            array(
                '#fffb96',
                $this->l('Transaction Pending'),
                'pending',
                '010010000',
            ),
            array(
                '#3333FF',
                $this->l('Ready to Ship'),
                'ready_to_ship',
                '010010000',
            ),
            array(
                '#8A2BE2',
                $this->l('Shipped'),
                'shipped',
                '010010000',
            ),
            array(
                '#ffeddb',
                $this->l('Delivered'),
                'delivered',
                '010010000',
            ),

        );

        foreach ($order_states as $key => $value) {
            if (!is_null($this->orderStateAvailable(Configuration::get('MERCADOPAGO_STATUS_'.$key)))) {
                continue;
            } else {
                $order_state = new OrderState();
                $order_state->invoice = $value[3][0];
                $order_state->send_email = $value[3][1];
                $order_state->module_name = 'mercadopago';
                $order_state->color = $value[0];
                $order_state->unremovable = $value[3][2];
                $order_state->hidden = $value[3][3];
                $order_state->logable = $value[3][4];
                $order_state->delivery = $value[3][5];
                $order_state->shipped = $value[3][6];

                $order_state->paid = $value[3][7];
                $order_state->deleted = $value[3][8];
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

    private function deleteStates()
    {
        for ($index = 0; $index <= 10; ++$index) {
            $order_state = new OrderState(Configuration::get('MERCADOPAGO_STATUS_'.$index));
            if (!$order_state->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * install module.
     */
    public function install()
    {
        $errors = array();
        if (!function_exists('curl_version')) {
            $errors[] = $this->l('Curl not installed');

            return false;
        }

        if (!parent::install() || !$this->createStates() || !$this->registerHook('payment') ||
            !$this->registerHook('paymentReturn') || !$this->registerHook('displayHeader') ||
            !$this->registerHook('displayOrderDetail')
            ||
            !$this->registerHook('displayAdminOrder')
            ||
            !$this->registerHook('backOfficeHeader')
            ||
            !$this->registerHook('displayBackOfficeHeader')

            //||
            //!$this->registerHook('beforeCarrier')
            ||
            !$this->registerHook('displayBeforeCarrier')
            ||
            !$this->registerHook('displayFooter')

            ) {
            return false;
        }

        return true;
    }

    private function isMercadoEnvios($id_carrier)
    {
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );

        $id_mercadoenvios_service_code = 0;
        if (isset($lista_shipping['MP_CARRIER']) &&
            array_key_exists($id_carrier, $lista_shipping['MP_CARRIER'])) {
            $id_mercadoenvios_service_code = $lista_shipping['MP_CARRIER'][$id_carrier];
        }

        return $id_mercadoenvios_service_code;
    }

    /**
     * Verify if there is state approved for order.
     */
    public static function getOrderStatePending($id_order)
    {
        return (int) Db::getInstance()->getValue(
            '
        SELECT MAX(id_order_state)
        FROM '._DB_PREFIX_.'order_history
        WHERE id_order =  '.(int) $id_order
        );
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
            'token_form' => $token_form,
            'statusOrder' => $statusOrder,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                                     htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
            'cancel_action_url' => $this->link->getModuleLink(
                'mercadopago',
                'cancelorder',
                array(),
                Configuration::get('PS_SSL_ENABLED'),
                null,
                null,
                false
            ),
        );

        $id_order_carrier = $order->getIdOrderCarrier();

        $order_carrier = new OrderCarrier($id_order_carrier);
        $id_mercadoenvios_service_code = $this->isMercadoEnvios($order_carrier->id_carrier);

        if ($id_mercadoenvios_service_code > 0) {
            $order_payments = $order->getOrderPayments();
            foreach ($order_payments as $order_payment) {
                $result = $this->mercadopago->getPaymentStandard($order_payment->transaction_id);
                if ($result['status'] == '200') {
                    $payment_info = $result['response'];
                    if (isset($payment_info['collection'])) {
                        $merchant_order_id = $payment_info['collection']['merchant_order_id'];
                        $result_merchant = $this->mercadopago->getMerchantOrder($merchant_order_id);
                        $return_tracking = $this->setTracking(
                            $order,
                            $result_merchant['response']['shipments'],
                            false
                        );
                        $tag_shipment = $this->mercadopago->getTagShipment(
                            $return_tracking['shipment_id']
                        );
                        $tag_shipment_zebra = $this->mercadopago->getTagShipmentZebra(
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

        return $this->display(__file__, '/views/templates/hook/imprimir_etiqueta.tpl');
    }

    public function hookDisplayOrderDetail($params)
    {
        if ($params['order']->module == 'mercadopago') {
            $order = new Order(Tools::getValue('id_order'));
            $order_payments = $order->getOrderPayments();
            foreach ($order_payments as $order_payment) {
                $result = $this->mercadopago->getPayment($order_payment->transaction_id);
                if ($result['status'] == '404') {
                    $result = $this->mercadopago->getPaymentStandard($order_payment->transaction_id);

                    $result_merchant = $this->mercadopago->getMerchantOrder(
                        $result['response']['collection']['merchant_order_id']
                    );
                }
                if ($result['status'] == 200) {
                    $payment_info = $result['response']['collection'];

                    $id_mercadoenvios_service_code = $this->isMercadoEnvios($order->id_carrier);
                    if ($id_mercadoenvios_service_code > 0) {
                        $merchant_order_id = $payment_info['merchant_order_id'];
                        $result_merchant = $this->mercadopago->getMerchantOrder($merchant_order_id);
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

    private function setTracking($order, $shipments, $update)
    {
        $shipment_id = null;
        $retorno = null;
        foreach ($shipments as $shipment) {
            if ($shipment['shipping_mode'] != 'me2') {
                continue;
            }

            $shipment_id = $shipment['id'];
            $response_shipment = $this->mercadopago->getTracking($shipment_id);
            $response_shipment = $response_shipment['response'];
            $tracking_number = $response_shipment['tracking_number'];

            if ($response_shipment['tracking_number'] != 'pending') {
                $status = '';
                switch ($response_shipment['status']) {
                    case 'ready_to_ship':
                        $status = $this->l('Ready to ship');
                        break;
                    default:
                        $status = $response_shipment['status'];
                        break;
                }

                switch ($response_shipment['substatus']) {
                    case 'ready_to_print':
                        $substatus_description = $this->l('Tag ready to print');
                        break;
                    case 'printed':
                        $substatus_description = $this->l('Tag printed');
                        break;
                    case 'stale':
                        $substatus_description = $this->l('Unsuccessful');
                        break;
                    case 'delayed':
                        $substatus_description = $this->l('Sending the delayed path');
                        break;
                    case 'receiver_absent':
                        $substatus_description = $this->l('Missing recipient for delivery');
                        break;
                    case 'returning_to_sender':
                        $substatus_description = $this->l('In return to sender');
                        break;
                    case 'claimed_me':
                        $substatus_description = $this->l('Buyer initiates complaint and requested a refund.');
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
                if ($update) {
                    $id_order_carrier = $order->getIdOrderCarrier();
                    $order_carrier = new OrderCarrier($id_order_carrier);
                    $order_carrier->tracking_number = $tracking_number;
                    $order_carrier->update();
                }
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

    public function uninstall()
    {
        $this->removeMercadoEnvios();
        $this->uninstallModule();
        $this->setSettings();

        // continue the states
        if (!$this->uninstallPaymentSettings() || !Configuration::deleteByName('MERCADOPAGO_PUBLIC_KEY') ||
             !Configuration::deleteByName('MERCADOPAGO_CLIENT_ID') ||
             !Configuration::deleteByName('MERCADOPAGO_CLIENT_SECRET') ||
             !Configuration::deleteByName('MERCADOPAGO_CATEGORY') ||
             !Configuration::deleteByName('MERCADOPAGO_CREDITCARD_BANNER') ||
             !Configuration::deleteByName('MERCADOPAGO_CREDITCARD_ACTIVE') ||
             !Configuration::deleteByName('MERCADOPAGO_ACCESS_TOKEN') ||
             !Configuration::deleteByName('MERCADOPAGO_STANDARD_ACTIVE') ||
             !Configuration::deleteByName('MERCADOPAGO_LOG') ||
             !Configuration::deleteByName('MERCADOPAGO_STANDARD_BANNER') ||
             !Configuration::deleteByName('MERCADOPAGO_WINDOW_TYPE') ||
             !Configuration::deleteByName('MERCADOPAGO_IFRAME_WIDTH') ||
             !Configuration::deleteByName('MERCADOPAGO_IFRAME_HEIGHT') ||
             !Configuration::deleteByName('MERCADOPAGO_INSTALLMENTS') ||
             !Configuration::deleteByName('MERCADOPAGO_AUTO_RETURN') ||
             !Configuration::deleteByName('MERCADOPAGO_COUNTRY') ||
             !Configuration::deleteByName('MERCADOPAGO_COUPON_ACTIVE') ||
             !Configuration::deleteByName('MERCADOPAGO_COUPON_TICKET_ACTIVE') ||
             !Configuration::deleteByName('MERCADOENVIOS_ACTIVATE') ||
             !Configuration::deleteByName('MERCADOPAGO_CARRIER') ||
             !Configuration::deleteByName('MERCADOPAGO_CARRIER_ID_1') ||
             !Configuration::deleteByName('MERCADOPAGO_CARRIER_ID_2') ||

             !Configuration::deleteByName('MERCADOPAGO_DISCOUNT_PERCENT') ||
             !Configuration::deleteByName('MERCADOPAGO_ACTIVE_CREDITCARD') ||
             !Configuration::deleteByName('MERCADOPAGO_ACTIVE_BOLETO') ||

            !parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function uninstallPaymentSettings()
    {
        $client_id = Configuration::get('MERCADOPAGO_CLIENT_ID');
        $client_secret = Configuration::get('MERCADOPAGO_CLIENT_SECRET');

        if ($client_id != '' && $client_secret != '') {
            $payment_methods = $this->mercadopago->getPaymentMethods();
            foreach ($payment_methods as $payment_method) {
                $pm_variable_name = 'MERCADOPAGO_'.Tools::strtoupper($payment_method['id']);
                if (!Configuration::deleteByName($pm_variable_name)) {
                    return false;
                }
            }

            $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();
            foreach ($offline_methods_payments as $offline_payment) {
                $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');
                $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');
                if (!Configuration::deleteByName($op_banner_variable) ||
                     !Configuration::deleteByName($op_active_variable)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getContent()
    {
        $errors = array();
        $success = false;
        $payment_methods = null;
        $payment_methods_settings = null;
        $offline_payment_settings = null;
        $offline_methods_payments = null;

        $this->context->controller->addCss($this->_path.'views/css/settings.css', 'all');
        $this->context->controller->addCss($this->_path.'views/css/bootstrap.css', 'all');
        $this->context->controller->addCss($this->_path.'views/css/style.css', 'all');

        $this->smarty->assign(array(
            'percent' => Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT'),
            'active_credicard' => Configuration::get('MERCADOPAGO_ACTIVE_CREDITCARD'),
            'active_boleto' => Configuration::get('MERCADOPAGO_ACTIVE_BOLETO')
        ));


        if (Tools::getValue('login')) {
            $client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
            $client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');
            $access_token = Tools::getValue('MERCADOPAGO_ACCESS_TOKEN');

            if (empty($client_id) || empty($client_secret) || empty($access_token)) {
                $errors[] = $this->l('Please, complete the fieds Client Id, Client Secret and Access Token.');
                $success = false;

                $settings = array(
                    'errors' => $errors,
                    'version' => $this->getPrestashopVersion(),
                );
                $this->context->smarty->assign($settings);

                return $this->display(__file__, '/views/templates/admin/settings.tpl');
            }

            if (!$this->validateCredential($client_id, $client_secret)) {
                $errors[] = $this->l('Client Id or Client Secret invalid.');
                $success = false;
            } else {
                Configuration::updateValue('MERCADOPAGO_ACCESS_TOKEN', $access_token);
                $this->setDefaultValues($client_id, $client_secret);

                // populate all payments accoring to country
                $mp = new MPApi($client_id, $client_secret);
                $payment_methods = $mp->getPaymentMethods();

                // load all offline payment method settings
                $offline_methods_payments = $mp->getOfflinePaymentMethods();

                $offline_payment_settings = array();
                foreach ($offline_methods_payments as $offline_payment) {
                    $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');
                    $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');

                    $offline_payment_settings[$offline_payment['id']] = array(
                        'name' => $offline_payment['name'],
                        'banner' => Configuration::get($op_banner_variable),
                        'active' => Configuration::get($op_active_variable),
                    );

                    if ($offline_payment['payment_type_id'] == "ticket") {
                        $ticket_active = Configuration::get('MERCADOPAGO_'.
                            Tools::strtoupper($offline_payment['id'].'_ACTIVE'));
                        Configuration::updateValue('MERCADOPAGO_ACTIVE_BOLETO', $ticket_active);
                    }
                }
            }
        } elseif (Tools::getValue('submitmercadopago')) {
            $client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
            $client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');
            $access_token = Tools::getValue('MERCADOPAGO_ACCESS_TOKEN');

            Configuration::updateValue(
                'MERCADOPAGO_DISCOUNT_PERCENT',
                (float) Tools::getValue('MERCADOPAGO_DISCOUNT_PERCENT')
            );
            Configuration::updateValue(
                'MERCADOPAGO_ACTIVE_CREDITCARD',
                (int) Tools::getValue('MERCADOPAGO_ACTIVE_CREDITCARD')
            );

            Configuration::updateValue(
                'MERCADOPAGO_ACTIVE_BOLETO',
                (int) Tools::getValue('MERCADOPAGO_ACTIVE_BOLETO')
            );

            $this->smarty->assign(array(
                'percent' => Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT'),
                'active_credicard' => Configuration::get('MERCADOPAGO_ACTIVE_CREDITCARD'),
                'active_boleto' => Configuration::get('MERCADOPAGO_ACTIVE_BOLETO')
            ));

            if (empty($client_id) || empty($client_secret) || empty($access_token)) {
                $errors[] = $this->l('Please, complete the fieds Client Id, Client Secret and Access Token.');
                $success = false;
                $settings = array(
                    'errors' => $errors,
                    'version' => $this->getPrestashopVersion(),
                );
                $this->context->smarty->assign($settings);
                return $this->display(__file__, '/views/templates/admin/settings.tpl');
            }

            $client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
            $client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');
            $access_token = Tools::getValue('MERCADOPAGO_ACCESS_TOKEN');

            $public_key = Tools::getValue('MERCADOPAGO_PUBLIC_KEY');
            $creditcard_active = Tools::getValue('MERCADOPAGO_CREDITCARD_ACTIVE');
            $coupon_active = Tools::getValue('MERCADOPAGO_COUPON_ACTIVE');
            $coupon_ticket_active = Tools::getValue('MERCADOPAGO_COUPON_TICKET_ACTIVE');

            $boleto_active = Tools::getValue('MERCADOPAGO_BOLETO_ACTIVE');
            $standard_active = Tools::getValue('MERCADOPAGO_STANDARD_ACTIVE');
            $mercadopago_log = Tools::getValue('MERCADOPAGO_LOG');

            $mercadoenvios_activate = Tools::getValue('MERCADOENVIOS_ACTIVATE');

            $new_country = false;

            try {
                if (!$this->validateCredential($client_id, $client_secret)) {
                    $errors[] = $this->l('Client Id or Client Secret invalid.');
                    $success = false;
                } else {
                    $previous_country = $this->getCountry(
                        Configuration::get('MERCADOPAGO_CLIENT_ID'),
                        Configuration::get('MERCADOPAGO_CLIENT_SECRET')
                    );
                    $current_country = $this->getCountry($client_id, $client_secret);
                    $new_country = $previous_country == $current_country ? false : true;

                    Configuration::updateValue('MERCADOPAGO_CLIENT_ID', $client_id);
                    Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', $client_secret);
                    Configuration::updateValue('MERCADOPAGO_COUNTRY', $this->getCountry($client_id, $client_secret));
                    $success = true;
                    if ($creditcard_active == 'true' && !empty($public_key)) {
                        Configuration::updateValue('MERCADOPAGO_PUBLIC_KEY', $public_key);
                    }
                    Configuration::updateValue('MERCADOPAGO_ACCESS_TOKEN', $access_token);
                    if ($mercadoenvios_activate == 'true' &&
                        count(Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'))) == 0) {
                        $this->setCarriers();
                    } elseif (count(Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'))) > 0) {
                        $this->removeMercadoEnvios();
                    }

                    // populate all payments accoring to country
                    $this->mercadopago = new MPApi(
                        Configuration::get('MERCADOPAGO_CLIENT_ID'),
                        Configuration::get('MERCADOPAGO_CLIENT_SECRET')
                    );
                    $payment_methods = $this->mercadopago->getPaymentMethods();
                    $configCard = $this->mercadopago->setEnableDisableTwoCard(Tools::getValue('MERCADOPAGO_TWO_CARDS'));

                    $two_cards = $configCard['response']['two_cards'];
                    Configuration::updateValue('MERCADOPAGO_TWO_CARDS', $two_cards);
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    'MercadoPago::getContent - Fatal Error: '.$e->getMessage(),
                    MPApi::FATAL_ERROR,
                    0
                );
                $this->context->smarty->assign(
                    array(
                        'message_error' => $e->getMessage(),
                        'version' => $this->getPrestashopVersion(),
                    )
                );
                return $this->display(__file__, '/views/templates/front/error_admin.tpl');
            }
            $category = Tools::getValue('MERCADOPAGO_CATEGORY');
            Configuration::updateValue('MERCADOPAGO_CATEGORY', $category);

            $creditcard_banner = Tools::getValue('MERCADOPAGO_CREDITCARD_BANNER');
            Configuration::updateValue('MERCADOPAGO_CREDITCARD_BANNER', $creditcard_banner);

            Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', $standard_active);
            Configuration::updateValue('MERCADOENVIOS_ACTIVATE', $mercadoenvios_activate);
            Configuration::updateValue('MERCADOPAGO_LOG', $mercadopago_log);

            Configuration::updateValue('MERCADOPAGO_BOLETO_ACTIVE', $boleto_active);
            Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', $creditcard_active);
            Configuration::updateValue('MERCADOPAGO_COUPON_ACTIVE', $coupon_active);
            Configuration::updateValue('MERCADOPAGO_COUPON_TICKET_ACTIVE', $coupon_ticket_active);

            $standard_banner = Tools::getValue('MERCADOPAGO_STANDARD_BANNER');
            Configuration::updateValue('MERCADOPAGO_STANDARD_BANNER', $standard_banner);

            $window_type = Tools::getValue('MERCADOPAGO_WINDOW_TYPE');
            Configuration::updateValue('MERCADOPAGO_WINDOW_TYPE', $window_type);

            $iframe_width = Tools::getValue('MERCADOPAGO_IFRAME_WIDTH');
            Configuration::updateValue('MERCADOPAGO_IFRAME_WIDTH', $iframe_width);

            $iframe_height = Tools::getValue('MERCADOPAGO_IFRAME_HEIGHT');
            Configuration::updateValue('MERCADOPAGO_IFRAME_HEIGHT', $iframe_height);

            $installments = Tools::getValue('MERCADOPAGO_INSTALLMENTS');
            Configuration::updateValue('MERCADOPAGO_INSTALLMENTS', $installments);

            $auto_return = Tools::getValue('MERCADOPAGO_AUTO_RETURN');
            Configuration::updateValue('MERCADOPAGO_AUTO_RETURN', $auto_return);

            $exclude_all = true;

            foreach ($payment_methods as $payment_method) {
                $pm_variable_name = 'MERCADOPAGO_'.Tools::strtoupper($payment_method['id']);
                $value = Tools::getValue($pm_variable_name);

                if ($value != 'on') {
                    $exclude_all = false;
                }
                // current settings
                $payment_methods_settings[$payment_method['id']] = Configuration::get($pm_variable_name);
            }

            if (!$exclude_all) {
                $payment_methods_settings = array();
                foreach ($payment_methods as $payment_method) {
                    $pm_variable_name = 'MERCADOPAGO_'.Tools::strtoupper($payment_method['id']);
                    $value = Tools::getValue($pm_variable_name);
                    // save setting per payment_method
                    Configuration::updateValue($pm_variable_name, $value);
                    $payment_methods_settings[$payment_method['id']] = Configuration::get($pm_variable_name);
                }
            } else {
                $errors[] = $this->l('Cannnot exclude all payment methods.');
                $success = false;
            }
            // if it is new country, reset values
            if ($new_country) {
                $this->setCustomSettings($client_id, $client_secret, $this->getCountry($client_id, $client_secret));

                $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();
                $offline_payment_settings = array();
                foreach ($offline_methods_payments as $offline_payment) {
                    $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');

                    $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');

                    $op_banner = Configuration::get($op_banner_variable);
                    $op_active = Configuration::get($op_banner_variable);

                    $offline_payment_settings[$offline_payment['id']] = array(
                        'name' => $offline_payment['name'],
                        'banner' => Configuration::get($op_banner_variable),
                        'active' => Configuration::get($op_active_variable),
                    );

                    if ($offline_payment['payment_type_id'] == "ticket") {
                        $ticket_active = Configuration::get('MERCADOPAGO_'.
                            Tools::strtoupper($offline_payment['id'].'_ACTIVE'));
                        Configuration::updateValue('MERCADOPAGO_CUSTOM_BOLETO', $ticket_active);
                    }
                }
            } else {
                // save offline payment settings
                $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();
                $offline_payment_settings = array();
                foreach ($offline_methods_payments as $offline_payment) {
                    $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');
                    $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');

                    $op_banner = Tools::getValue($op_banner_variable);

                    // save setting per payment_method
                    Configuration::updateValue($op_banner_variable, $op_banner);

                    $op_active = Tools::getValue($op_active_variable);
                    // save setting per payment_method
                    Configuration::updateValue($op_active_variable, $op_active);

                    $offline_payment_settings[$offline_payment['id']] = array(
                        'name' => $offline_payment['name'],
                        'banner' => Configuration::get($op_banner_variable),
                        'active' => Configuration::get($op_active_variable),
                    );
                    if ($offline_payment['payment_type_id'] == "ticket") {
                        $ticket_active = Configuration::get('MERCADOPAGO_'.
                            Tools::strtoupper($offline_payment['id'].'_ACTIVE'));
                        Configuration::updateValue('MERCADOPAGO_CUSTOM_BOLETO', $ticket_active);
                    }
                }
            }
            $this->setSettings();
        } else {
            // populate all payments according to country
            if (Configuration::get('MERCADOPAGO_CLIENT_ID') != '' &&
                 Configuration::get('MERCADOPAGO_CLIENT_SECRET') != '') {
                $this->mercadopago = new MPApi(
                    Configuration::get('MERCADOPAGO_CLIENT_ID'),
                    Configuration::get('MERCADOPAGO_CLIENT_SECRET')
                );

                // load payment method settings for standard
                $payment_methods = $this->mercadopago->getPaymentMethods();
                $payment_methods_settings = array();
                foreach ($payment_methods as $payment_method) {
                    $pm_variable_name = 'MERCADOPAGO_'.Tools::strtoupper($payment_method['id']);
                    $value = Configuration::get($pm_variable_name);

                    $payment_methods_settings[$payment_method['id']] = Configuration::get($pm_variable_name);
                }
            }
        }

        // load all offline payment method settings
        $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();
        $offline_payment_settings = array();
        foreach ($offline_methods_payments as $offline_payment) {
            $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');
            $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');

            $offline_payment_settings[$offline_payment['id']] = array(
                'name' => $offline_payment['name'],
                'banner' => Configuration::get($op_banner_variable),
                'active' => Configuration::get($op_active_variable),
            );
        }

        $this->mercadopago = new MPApi(
            Configuration::get('MERCADOPAGO_CLIENT_ID'),
            Configuration::get('MERCADOPAGO_CLIENT_SECRET')
        );

        $site_id = array(
            'site_id' => Configuration::get('MERCADOPAGO_COUNTRY'),
        );
        $test_user = $this->mercadopago->getTestUser($site_id);

        $requirements = UtilMercadoPago::checkRequirements();

        $configCard = $this->mercadopago->getCheckConfigCard();

        $two_cards = $configCard['response']['two_cards'];
        Configuration::updateValue('MERCADOPAGO_TWO_CARDS', $two_cards);

        $notification_url = $this->link->getModuleLink(
            'mercadopago',
            'notification',
            array(),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        $settings = array(
            'test_user' => $test_user,
            'requirements' => $requirements,
            'two_cards' => htmlentities(Configuration::get('MERCADOPAGO_TWO_CARDS'), ENT_COMPAT, 'UTF-8'),
            'public_key' => htmlentities(Configuration::get('MERCADOPAGO_PUBLIC_KEY'), ENT_COMPAT, 'UTF-8'),
            'access_token' => htmlentities(Configuration::get('MERCADOPAGO_ACCESS_TOKEN'), ENT_COMPAT, 'UTF-8'),
            'client_id' => htmlentities(Configuration::get('MERCADOPAGO_CLIENT_ID'), ENT_COMPAT, 'UTF-8'),
            'client_secret' => htmlentities(Configuration::get('MERCADOPAGO_CLIENT_SECRET'), ENT_COMPAT, 'UTF-8'),
            'country' => htmlentities(Configuration::get('MERCADOPAGO_COUNTRY'), ENT_COMPAT, 'UTF-8'),
            'category' => htmlentities(Configuration::get('MERCADOPAGO_CATEGORY'), ENT_COMPAT, 'UTF-8'),
            'notification_url' => htmlentities($notification_url, ENT_COMPAT, 'UTF-8'),
            'creditcard_banner' => htmlentities(
                Configuration::get('MERCADOPAGO_CREDITCARD_BANNER'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'creditcard_active' => htmlentities(
                Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'coupon_active' => htmlentities(Configuration::get('MERCADOPAGO_COUPON_ACTIVE'), ENT_COMPAT, 'UTF-8'),
            'coupon_ticket_active' => htmlentities(
                Configuration::get('MERCADOPAGO_COUPON_TICKET_ACTIVE'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'boleto_active' => htmlentities(Configuration::get('MERCADOPAGO_BOLETO_ACTIVE'), ENT_COMPAT, 'UTF-8'),
            'standard_active' => htmlentities(Configuration::get('MERCADOPAGO_STANDARD_ACTIVE'), ENT_COMPAT, 'UTF-8'),
            'MERCADOENVIOS_ACTIVATE' => htmlentities(
                Configuration::get('MERCADOENVIOS_ACTIVATE'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'log_active' => htmlentities(Configuration::get('MERCADOPAGO_LOG'), ENT_COMPAT, 'UTF-8'),
            'standard_banner' => htmlentities(Configuration::get('MERCADOPAGO_STANDARD_BANNER'), ENT_COMPAT, 'UTF-8'),
            'window_type' => htmlentities(Configuration::get('MERCADOPAGO_WINDOW_TYPE'), ENT_COMPAT, 'UTF-8'),
            'iframe_width' => htmlentities(Configuration::get('MERCADOPAGO_IFRAME_WIDTH'), ENT_COMPAT, 'UTF-8'),
            'iframe_height' => htmlentities(Configuration::get('MERCADOPAGO_IFRAME_HEIGHT'), ENT_COMPAT, 'UTF-8'),
            'installments' => htmlentities(Configuration::get('MERCADOPAGO_INSTALLMENTS'), ENT_COMPAT, 'UTF-8'),
            'auto_return' => htmlentities(Configuration::get('MERCADOPAGO_AUTO_RETURN'), ENT_COMPAT, 'UTF-8'),
            'uri' => $_SERVER['REQUEST_URI'],
            'payment_methods' => $payment_methods ? $payment_methods : null,
            'payment_methods_settings' => $payment_methods_settings ? $payment_methods_settings : null,
            'offline_methods_payments' => $offline_methods_payments ? $offline_methods_payments : null,
            'offline_payment_settings' => $offline_payment_settings ? $offline_payment_settings : null,
            'errors' => $errors,
            'success' => $success,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                 htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                'version' => $this->getPrestashopVersion(),
        );

        $this->context->smarty->assign($settings);

        return $this->display(__file__, '/views/templates/admin/settings.tpl');
    }

    private function setDefaultValues($client_id, $client_secret)
    {
        $country = $this->getCountry($client_id, $client_secret);

        Configuration::updateValue('MERCADOPAGO_CLIENT_ID', $client_id);
        Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', $client_secret);
        Configuration::updateValue('MERCADOPAGO_COUNTRY', $country);
        Configuration::updateValue('MERCADOPAGO_WINDOW_TYPE', 'redirect');
        Configuration::updateValue('MERCADOPAGO_IFRAME_WIDTH', '725');
        Configuration::updateValue('MERCADOPAGO_IFRAME_HEIGHT', '570');
        Configuration::updateValue('MERCADOPAGO_INSTALLMENTS', '12');
        Configuration::updateValue('MERCADOPAGO_AUTO_RETURN', 'approved');

        $this->setCustomSettings($client_id, $client_secret, $country);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        return $this->hookBackOfficeHeader($params);
    }

    public function hookBackOfficeHeader($params)
    {
        if (Configuration::get('MERCADOPAGO_CARRIER') != null &&
            Configuration::get('MERCADOENVIOS_ACTIVATE') == 'true') {
            $lista_shipping = Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'), true);
            $lista_shipping = implode(',', $lista_shipping['MP_SHIPPING']);

            $javascript = '<script>var carrier_id = ['.$lista_shipping.'];';

            $javascript .= '$(document).ready(function(){';

            $javascript .= '$("table.carrier tr").each(function() {
                var tr_id = $(this).attr("id");
                for(var i in carrier_id) {
                    var re = new RegExp("tr_[0-9]+_"+carrier_id[i]+"_[0-9]+");
                    if ((tr_id+"").match(re)) {
                        $("#"+tr_id+" > td").first().html("");
                        $("#"+tr_id+" > td").last().html("");
                        $("#"+tr_id).attr("onclick", "");
                        $("#"+tr_id+" > td").attr("onclick", "");
                    }
                }
            });});</script>';

            return $javascript;
        }
    }
    private function setCustomSettings($client_id, $client_secret, $country)
    {
        if ($country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MLC' || $country == 'MCO' ||
             $country == 'MLV' ||
             $country == 'MPE'
            ) {
            Configuration::updateValue(
                'MERCADOPAGO_CREDITCARD_BANNER',
                (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.
                'modules/mercadopago/views/img/'.$country.'/credit_card.png'
            );
            Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', 'true');
            Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', 'false');
            Configuration::updateValue('MERCADOPAGO_COUPON_ACTIVE', 'false');
            Configuration::updateValue('MERCADOPAGO_COUPON_TICKET_ACTIVE', 'false');

            Configuration::updateValue('MERCADOPAGO_LOG', 'false');

            // set all offline payment settings
            $mp = new MPApi($client_id, $client_secret);

            $offline_methods_payments = $mp->getOfflinePaymentMethods();
            foreach ($offline_methods_payments as $offline_payment) {
                $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');
                Configuration::updateValue($op_banner_variable, $offline_payment['secure_thumbnail']);
                $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');
                Configuration::updateValue($op_active_variable, 'true');
            }
        } else {
            Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', 'true');
        }

        Configuration::updateValue(
            'MERCADOPAGO_STANDARD_BANNER',
            (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.
            'modules/mercadopago/views/img/'.$country.'/banner_all_methods.png'
        );
    }

    private function getCountry($client_id, $client_secret)
    {
        $mp = new MPApi($client_id, $client_secret);

        return $mp->getCountry();
    }

    private function validateCredential($client_id, $client_secret)
    {
        $mp = new MPApi($client_id, $client_secret);

        return $mp->getAccessToken() ? true : false;
    }

    public function hookDisplayHeader()
    {
        if (!$this->active) {
            return;
        }

        $data = array(
            'creditcard_active' => Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE'),
            'public_key' => Configuration::get('MERCADOPAGO_PUBLIC_KEY'),
            'access_token' => Configuration::get('MERCADOPAGO_ACCESS_TOKEN'),
        );

        $this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');
        $this->context->controller->addCss($this->_path.'views/css/chico.min.css', 'all');
        $this->context->controller->addCss($this->_path.'views/css/dd.css', 'all');
        $this->context->controller->addCss(
            $this->_path.'views/css/mercadopago_v6.css',
            'all'
        );

        $this->context->smarty->assign($data);

        return $this->display(__file__, '/views/templates/hook/header.tpl');
    }

    public function hookDisplayFooter()
    {
        if (!$this->active) {
            return;
        }

        return $this->display(__file__, '/views/templates/hook/display.tpl');
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        //calculo desconto parcela a vista
        $cart = $params['cart'];

        $active_credit_card = (int) Configuration::get('MERCADOPAGO_ACTIVE_CREDITCARD');
        $shipping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        $product_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);

        $discount = ($percent / 100) * $product_cost;

        $orderTotal =  number_format(($product_cost - $discount) + $shipping_cost, 2, ',', '.');

        $this->context->smarty->assign(array('orderTotal' => $orderTotal,'active_credit_card' => $active_credit_card));


        $creditcard_active = Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE');
        $mercadoenvios_activate = Configuration::get('MERCADOENVIOS_ACTIVATE');
        $boleto_active = Configuration::get('MERCADOPAGO_BOLETO_ACTIVE');
        if ($mercadoenvios_activate == 'true') {
            $creditcard_active = 'false';
            $boleto_active = 'false';
        } else {
            $mercadoenvios_activate = 'false';
        }

        $credit_card_discount = (int) Configuration::get('MERCADOPAGO_ACTIVE_CREDITCARD');
        $boleto_discount = (int) Configuration::get('MERCADOPAGO_ACTIVE_BOLETO');
        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        if ($this->hasCredential()) {
            $this_path_ssl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                 htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;
            $data = array(
                'credit_card_discount'=> $credit_card_discount,
                'boleto_discount'=> $boleto_discount,

                'percent'=> $percent,
                'this_path_ssl' => $this_path_ssl,
                'mercadoenvios_activate' => $mercadoenvios_activate,
                'boleto_active' => $boleto_active,
                'creditcard_active' => $creditcard_active,
                'coupon_active' => Configuration::get('MERCADOPAGO_COUPON_ACTIVE'),
                'coupon_ticket_active' => Configuration::get('MERCADOPAGO_COUPON_TICKET_ACTIVE'),

                'standard_active' => Configuration::get('MERCADOPAGO_STANDARD_ACTIVE'),
                'log_active' => Configuration::get('MERCADOPAGO_LOG'),
                'version' => $this->getPrestashopVersion(),
                'custom_action_url' => $this->link->getModuleLink(
                    'mercadopago',
                    'custompayment',
                    array(),
                    Configuration::get('PS_SSL_ENABLED'),
                    null,
                    null,
                    false
                ),
                'discount_action_url' => $this->link->getModuleLink(
                    'mercadopago',
                    'discount',
                    array(),
                    Configuration::get('PS_SSL_ENABLED'),
                    null,
                    null,
                    false
                ),
                'payment_status' => Tools::getValue('payment_status'),
                'status_detail' => Tools::getValue('status_detail'),
                'payment_method_id' => Tools::getValue('payment_method_id'),
                'installments' => Tools::getValue('installments'),
                'statement_descriptor' => Tools::getValue('statement_descriptor'),
                'window_type' => Configuration::get('MERCADOPAGO_WINDOW_TYPE'),
                'iframe_width' => Configuration::get('MERCADOPAGO_IFRAME_WIDTH'),
                'iframe_height' => Configuration::get('MERCADOPAGO_IFRAME_HEIGHT'),
                'country' => Configuration::get('MERCADOPAGO_COUNTRY'),
            );
            // send credit card configurations only activated
            if ($creditcard_active == 'true') {
                $data['public_key'] = Configuration::get('MERCADOPAGO_PUBLIC_KEY');
                $data['creditcard_banner'] = Configuration::get('MERCADOPAGO_CREDITCARD_BANNER');
                $data['amount'] = (double) number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, '.', '');

                // get the customer cards
                $customerID = $this->getCustomerID();
                // get customer cards
                if ($customerID != null) {
                    $data['customerID'] = $customerID;
                    $customerCards = $this->getCustomerCards($customerID);
                    $data['customerCards'] = Tools::jsonEncode($customerCards);
                } else {
                    $data['customerCards'] = null;
                    $data['customerID'] = null;
                }
            }
            // send standard configurations only activated
            if (Configuration::get('MERCADOPAGO_STANDARD_ACTIVE') == 'true') {
                $result = $this->createStandardCheckoutPreference();

                if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
                    $messageLog = "====result====".Tools::jsonEncode($result);
                    PrestaShopLogger::addLog(
                        $messageLog,
                        MPApi::ERROR,
                        0
                    );
                }

                if (array_key_exists('init_point', $result['response'])) {
                    $data['standard_banner'] = Configuration::get('MERCADOPAGO_STANDARD_BANNER');
                    $data['preferences_url'] = $result['response']['init_point'];
                } else {
                    $data['preferences_url'] = null;
                    PrestaShopLogger::addLog(
                        'MercadoPago::hookPayment - An error occurred during preferences creation.'.
                        'Please check your credentials and try again.: ',
                        MPApi::ERROR,
                        0
                    );
                }
            }
            // send offline settings
            $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();

            $offline_payment_settings = array();
            foreach ($offline_methods_payments as $offline_payment) {
                $op_banner_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_BANNER');
                $op_active_variable = 'MERCADOPAGO_'.Tools::strtoupper($offline_payment['id'].'_ACTIVE');

                $thumbnail = $offline_payment['thumbnail'];

                if (Configuration::get('PS_SSL_ENABLED')) {
                    $thumbnail = str_replace("http", "https", $offline_payment['thumbnail']);
                }
                $offline_payment_settings[$offline_payment['id']] = array(
                    'name' => $offline_payment['name'],
                    'banner' => Configuration::get($op_banner_variable),
                    'active' => Configuration::get($op_active_variable),
                    'thumbnail' => $thumbnail,
                );
            }


            $data['offline_payment_settings'] = $offline_payment_settings;

            if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLM' ||
                Configuration::get('MERCADOPAGO_COUNTRY') == 'MPE'
                ) {
                $payment_methods_credit = $this->mercadopago->getPaymentCreditsMLM();
                $data['payment_methods_credit'] = $payment_methods_credit;
            } else {
                $data['payment_methods_credit'] = array();
            }

            $this->context->smarty->assign($data);
            $this->context->smarty->assign($this->setPreModuleAnalytics());

            return $this->display(__file__, '/views/templates/hook/checkout.tpl');
        }
    }

    private function setPreModuleAnalytics()
    {
        $customer_fields = Context::getContext()->customer->getFields();

        $select = 'SELECT name FROM '. _DB_PREFIX_ .'module where active = 1 AND id_module IN (
            SELECT h.id_module
            FROM '. _DB_PREFIX_ .'hook_module h INNER JOIN '. _DB_PREFIX_ .'hook ph on ph.id_hook = h.id_hook
            WHERE ph.name = "displayPayment"
            )';
        $query = Db::getInstance()->executeS($select);

        $resultModules = array();

        foreach ($query as $result) {
            array_push($resultModules, $result['name']);
        }

        $return = array(
            'publicKey'=> Configuration::get('MERCADOPAGO_PUBLIC_KEY') ?
            Configuration::get('MERCADOPAGO_PUBLIC_KEY') : "",
            'token'=> Configuration::get('MERCADOPAGO_ACCESS_TOKEN'),
            'platform' => "PRESTASHOP",
            'platformVersion' => $this->getPrestashopVersion(),
            'moduleVersion' => $this->version,
            'payerEmail' => $customer_fields['email'],
            'userLogged' => $this->context->customer->isLogged() ? 1 : 0,
            'installedModules' => implode(', ', $resultModules),
            'additionalInfo' => ""
        );
        return $return;
    }

    /**
     * @param
     *            $params
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        if (Tools::getValue('payment_method_id') == 'bolbradesco' ||
            Tools::getValue('payment_type') == 'bank_transfer' ||
            Tools::getValue('payment_type') == 'atm' || Tools::getValue('payment_type') == 'ticket') {
            $this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');

            $boleto_url = Tools::getValue('boleto_url');
            if (Configuration::get('PS_SSL_ENABLED')) {
                $boleto_url = str_replace("http", "https", $boleto_url);
            }

            $this->context->smarty->assign(
                array(
                    'payment_id' => Tools::getValue('payment_id'),
                    'boleto_url' => Tools::getValue('boleto_url'),
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                )
            );

            return $this->display(__file__, '/views/templates/hook/boleto_payment_return.tpl');
        } else {
            $this->context->controller->addCss($this->_path.'views/css/mercadopago_core.css', 'all');
            $this->context->smarty->assign(
                array(
                    'payment_status' => Tools::getValue('payment_status'),
                    'status_detail' => Tools::getValue('status_detail'),
                    'card_holder_name' => Tools::getValue('card_holder_name'),
                    'four_digits' => Tools::getValue('four_digits'),
                    'payment_method_id' => Tools::getValue('payment_method_id'),
                    'installments' => Tools::getValue('installments'),
                    'transaction_amount' => Tools::displayPrice(
                        Tools::getValue('amount'),
                        $params['currencyObj'],
                        false
                    ),
                    'statement_descriptor' => Tools::getValue('statement_descriptor'),
                    'payment_id' => Tools::getValue('payment_id'),
                    'amount' => Tools::displayPrice(
                        Tools::getValue('amount'),
                        $params['currencyObj'],
                        false
                    ),
                    'this_path_ssl' => (
                        Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://'
                        ).htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__,
                )
            );

            return $this->display(__file__, '/views/templates/hook/creditcard_payment_return.tpl');
        }
    }

    /**
     * Verify the credentials.
     *
     * @return bool
     */
    private function hasCredential()
    {
        return Configuration::get('MERCADOPAGO_CLIENT_ID') != '' &&
             Configuration::get('MERCADOPAGO_CLIENT_SECRET') != '';
    }

    /**
     * @param
     *            $post
     */
    public function execPayment($post)
    {
        $preferences = $this->getPreferencesCustom($post);
        $result = $this->mercadopago->createCustomPayment($preferences);

        return $result['response'];
    }

    /**
     * @param
     *            $coupon_id
     *
     * @return $details_discount
     */
    public function validCoupon($coupon_id)
    {
        $cart = Context::getContext()->cart;

        $client_id = Configuration::get('MERCADOPAGO_CLIENT_ID');
        $client_secret = Configuration::get('MERCADOPAGO_CLIENT_SECRET');
        $mp = new MPApi($client_id, $client_secret);

        $customer_fields = Context::getContext()->customer->getFields();
        $transaction_amount = (double) number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
        $params = array(
            'transaction_amount' => $transaction_amount,
            'payer_email' => $customer_fields['email'],
            'coupon_code' => $coupon_id,
        );

        $details_discount = $mp->getDiscount($params);

        // add value on return api discount
        $details_discount['response']['transaction_amount'] = $params['transaction_amount'];
        $details_discount['response']['params'] = $params;

        return $details_discount;
    }

    /**
     * @param
     *            $post
     */
    private function getPreferencesCustom($post)
    {

        $customer_fields = Context::getContext()->customer->getFields();
        $cart = Context::getContext()->cart;

        // items
        $products = $cart->getProducts();
        $items = array();
        $summary = '';

        foreach ($products as $key => $product) {
            $image = Image::getCover($product['id_product']);
            $product_image = new Product($product['id_product'], false, Context::getContext()->language->id);
            $link = new Link();//because getImageLInk is not static function
            $imagePath = $link->getImageLink(
                $product_image->link_rewrite,
                $image['id_image'],
                ImageType::getFormatedName('home')
            );

            $item = array(
                'id' => $product['id_product'],
                'title' => $product['name'],
                'description' => $product['description_short'],
                'picture_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$imagePath,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'quantity' => $product['quantity'],
                'unit_price' => $product['price_wt'],
            );
            if ($key == 0) {
                $summary .= $product['name'];
            } else {
                $summary .= ', '.$product['name'];
            }

            $items[] = $item;
        }

        // include shipping cost
        $shipping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        if ($shipping_cost > 0) {
            $item = array(
                'title' => 'Shipping',
                'description' => 'Shipping service used by store',
                'quantity' => 1,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'unit_price' => $shipping_cost,
            );

            $items[] = $item;
        }

        // include wrapping cost
        $wrapping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
        if ($wrapping_cost > 0) {
            $item = array(
                'title' => 'Wrapping',
                'description' => 'Wrapping service used by store',
                'quantity' => 1,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'unit_price' => $wrapping_cost,
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
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'unit_price' => -$discounts,
            );

            $items[] = $item;
        }

        // Get payer address for additional_info
        $address_invoice = new Address((integer) $cart->id_address_invoice);
        $phone = $address_invoice->phone;
        $phone .= $phone == '' ? '' : '|';
        $phone .= $address_invoice->phone_mobile;
        $payer_additional_info = array(
            'first_name' => $customer_fields['firstname'],
            'last_name' => $customer_fields['lastname'],
            'registration_date' => $customer_fields['date_add'],
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
        );
        // Get shipment address for additional_info
        $address_delivery = new Address((integer) $cart->id_address_delivery);
        $shipments = array(
            'receiver_address' => array(
                'zip_code' => $address_delivery->postcode,
                'street_name' => $address_delivery->address1.' - '.$address_delivery->address2.' - '.
                     $address_delivery->city.'/'.$address_delivery->country,
                    'street_number' => '-',
                    'floor' => '-',
                    'apartment' => '-',
            ),
        );

        $notification_url = $this->link->getModuleLink(
            'mercadopago',
            'notification',
            array(),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );

        //aplicar desconto parcela a vista
        $installments = 1;
        $payment_mode = 'boleto';
        if (isset($post['opcaoPagamentoCreditCard']) && 'Customer' == $post['opcaoPagamentoCreditCard']) {
            $installments = (integer) $post['installmentsCust'];
        }

        if (isset($post['card_token_id'])) {
            $payment_mode = 'cartao';
        }

        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        if (count($percent) > 0) {
            $this->applyDiscount($cart, $payment_mode, $installments);
        }

        $ordelTotal = (double) number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');

        $payment_preference = array(
            'transaction_amount' => $ordelTotal,
            'external_reference' => $cart->id,
            'statement_descriptor' => '',
            'payment_method_id' => $post['payment_method_id'],
            'payer' => array(
                'email' => $customer_fields['email'],
            ),

            'additional_info' => array(
                'items' => $items,
                'payer' => $payer_additional_info,
                'shipments' => $shipments,
            ),
        );

        if ($post['payment_method_id'] == "webpay") {
            $payment_preference['callback_url'] = $this->link->getModuleLink(
                'mercadopago',
                'standardreturn',
                array(),
                Configuration::get('PS_SSL_ENABLED'),
                null,
                null,
                false
            );
            $payment_preference['transaction_details']['financial_institution'] = 1234;
            $ip = getenv('HTTP_CLIENT_IP')?:
            getenv('HTTP_X_FORWARDED_FOR')?:
            getenv('HTTP_X_FORWARDED')?:
            getenv('HTTP_FORWARDED_FOR')?:
            getenv('HTTP_FORWARDED')?:
            getenv('REMOTE_ADDR');
            $payment_preference['additional_info']['ip_address'] = $ip;

            //$payment_preference['payer']['entity_type'] = "individual";
        }

        if (isset($post['opcaoPagamentoCreditCard']) && $post['opcaoPagamentoCreditCard'] == 'Cards') {
            // salva o cartão
            $payment_preference['metadata'] = array(
                'opcao_pagamento' => $post['opcaoPagamentoCreditCard'],
                'customer_id' => $post['customerID'],
                'card_token_id' => $post['card_token_id'],
            );
        }

        if (!strrpos($notification_url, 'localhost')) {
            $payment_preference['notification_url'] = $notification_url.'?checkout=custom&';
        }

        $payment_preference['description'] = $summary;
        // add only for creditcard
        if (array_key_exists('card_token_id', $post)) {
            // add only it has issuer id
            if (array_key_exists('issuersOptions', $post) && $post['issuersOptions'] != '-1') {
                $payment_preference['issuer_id'] = (integer) $post['issuersOptions'];
            }

            if ('Customer' == $post['opcaoPagamentoCreditCard']) {
                $customerID = $post['customerID'];
                $payment_preference['payer']['id'] = $customerID;
                $payment_preference['installments'] = (integer) $post['installmentsCust'];
            } else {
                $payment_preference['installments'] = (integer) $post['installments'];
            }
            $payment_preference['token'] = $post['card_token_id'];
        }

        $mercadopago_coupon = isset($post['mercadopago_coupon']) ? $post['mercadopago_coupon'] : '';
        if ($mercadopago_coupon != '') {
            $coupon = $this->validCoupon($mercadopago_coupon);
            if ($coupon['status'] == 200) {
                $payment_preference['campaign_id'] = $coupon['response']['id'];
                $payment_preference['coupon_amount'] = (float) $coupon['response']['coupon_amount'];
                $payment_preference['coupon_code'] = Tools::strtoupper($mercadopago_coupon);
            } else {
                PrestaShopLogger::addLog($coupon['response']['error'].Tools::jsonEncode($coupon), MPApi::ERROR, 0);
                $this->context->smarty->assign(
                    array(
                        'message_error' => $coupon['response']['error'],
                        'version' => $this->getPrestashopVersion(),
                    )
                );

                return $this->display(__file__, '/views/templates/front/error_admin.tpl');
            }
        }

        //PRESTASHOP
        // if (!$this->mercadopago->isTestUser()) {
        //     switch (Configuration::get('MERCADOPAGO_COUNTRY')) {
        //         case 'MLB':
        //             $payment_preference['sponsor_id'] = 236914421;
        //             break;
        //         case 'MLM':
        //             $payment_preference['sponsor_id'] = 237793014;
        //             break;
        //         case 'MLA':
        //             $payment_preference['sponsor_id'] = 237788409;
        //             break;
        //         case 'MCO':
        //             $payment_preference['sponsor_id'] = 237788769;
        //             break;
        //         case 'MLV':
        //             $payment_preference['sponsor_id'] = 237789083;
        //             break;
        //         case 'MLC':
        //             $payment_preference['sponsor_id'] = 237788173;
        //             break;
        //         case 'MPE':
        //             $payment_preference['sponsor_id'] = 237791025;
        //             break;
        //         case 'MLU':
        //             $payment_preference['sponsor_id'] = 241730009;
        //             break;
        //     }
        // }

        //GIT HUB
        if (!$this->mercadopago->isTestUser()) {
            switch (Configuration::get('MERCADOPAGO_COUNTRY')) {
                case 'MLB':
                    $payment_preference['sponsor_id'] = 178326379;
                    break;
                case 'MLM':
                    $payment_preference['sponsor_id'] = 187899553;
                    break;
                case 'MLA':
                    $payment_preference['sponsor_id'] = 187899872;
                    break;
                case 'MCO':
                    $payment_preference['sponsor_id'] = 187900060;
                    break;
                case 'MLV':
                    $payment_preference['sponsor_id'] = 187900246;
                    break;
                case 'MLC':
                    $payment_preference['sponsor_id'] = 187900485;
                    break;
                case 'MPE':
                    $payment_preference['sponsor_id'] = 217182014;
                    break;
                case 'MLU':
                    $payment_preference['sponsor_id'] = 241730009;
                    break;
            }
        }

        $payment_preference['statement_descriptor'] = 'MERCADOPAGO';

        return $payment_preference;
    }


    private function getPrestashopPreferencesStandard()
    {
        $customer_fields = Context::getContext()->customer->getFields();
        $cart = Context::getContext()->cart;

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

        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO') {
            $round_place = 0;
        }

        foreach ($products as $key => $product) {
            $image = Image::getCover($product['id_product']);
            $product_image = new Product($product['id_product'], false, Context::getContext()->language->id);
            $link = new Link();//because getImageLInk is not static function
            $imagePath = $link->getImageLink(
                $product_image->link_rewrite,
                $image['id_image'],
                ImageType::getFormatedName('home')
            );

            $item = array(
                'id' => $product['id_product'],
                'title' => $product['name'],
                'description' => $product['description_short'],
                'quantity' => $product['quantity'],
                'unit_price' => round($product['price_wt'], $round_place),
                'picture_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$imagePath,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
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
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
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
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
            );
            $items[] = $item;
        }

        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );

        if (Configuration::get('MERCADOENVIOS_ACTIVATE') == 'true' &&
            isset($lista_shipping['MP_CARRIER'][$cart->id_carrier])
            ) {
            $dimensions = $this->getDimensions($products);

            $id_mercadoenvios_service_code = $lista_shipping['MP_CARRIER'][$cart->id_carrier];
            $address_delivery = new Address((integer) $cart->id_address_delivery);
            $shipments = array(
                'mode' => 'me2',
                'zip_code' => $address_invoice->postcode,
                'default_shipping_method' => $id_mercadoenvios_service_code,
                'dimensions' => $dimensions,
                'receiver_address' => array(
                    'floor' => '-',
                    'zip_code' => $address_delivery->postcode,
                    'street_name' => $address_delivery->address1.' - '.$address_delivery->address2.' - '.
                         $address_delivery->city.'/'.$address_delivery->country,
                        'apartment' => '-',
                        'street_number' => '-',
                ),
            );
        } else {
            $shipments = array();
            // include shipping cost
            $shipping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
            if ($shipping_cost > 0) {
                $item = array(
                    'title' => 'Shipping',
                    'description' => 'Shipping service used by store',
                    'quantity' => 1,
                    'unit_price' => $shipping_cost,
                    'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                );
                $items[] = $item;
            }
        }

        $data = array(
            'external_reference' => $cart->id,
            'customer' => $customer_data,
            'items' => $items,
            'shipments' => $shipments,
        );

        // PRESTASHOP
        // if (!$this->mercadopago->isTestUser()) {
        //     switch (Configuration::get('MERCADOPAGO_COUNTRY')) {
        //         case 'MLB':
        //             $payment_preference['sponsor_id'] = 236914421;
        //             break;
        //         case 'MLM':
        //             $payment_preference['sponsor_id'] = 237793014;
        //             break;
        //         case 'MLA':
        //             $payment_preference['sponsor_id'] = 237788409;
        //             break;
        //         case 'MCO':
        //             $payment_preference['sponsor_id'] = 237788769;
        //             break;
        //         case 'MLV':
        //             $payment_preference['sponsor_id'] = 237789083;
        //             break;
        //         case 'MLC':
        //             $payment_preference['sponsor_id'] = 237788173;
        //             break;
        //         case 'MPE':
        //             $payment_preference['sponsor_id'] = 237791025;
        //             break;
        //         case 'MLU':
        //             $payment_preference['sponsor_id'] = 241730009;
        //             break;
        //     }
        // }

        //GIT HUB
        if (!$this->mercadopago->isTestUser()) {
            switch (Configuration::get('MERCADOPAGO_COUNTRY')) {
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
        $data['auto_return'] = Configuration::get('MERCADOPAGO_AUTO_RETURN') == 'approved' ? 'approved' : '';
        $data['back_urls']['success'] = $this->link->getModuleLink(
            'mercadopago',
            'standardreturn',
            array(),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        $data['back_urls']['failure'] = $this->link->getPageLink(
            'order-opc',
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false,
            null
        );
        $data['back_urls']['pending'] = $this->link->getModuleLink(
            'mercadopago',
            'standardreturn',
            array(),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        $data['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentMethods();
        $data['payment_methods']['excluded_payment_types'] = array();
        $data['payment_methods']['installments'] = (integer) Configuration::get('MERCADOPAGO_INSTALLMENTS');
        $data['notification_url'] = $this->link->getModuleLink(
            'mercadopago',
            'notification',
            array(),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        ).'?checkout=standard&';
        // swap to payer index since customer is only for transparent
        $data['customer']['name'] = $data['customer']['first_name'];
        $data['customer']['surname'] = $data['customer']['last_name'];
        $data['payer'] = $data['customer'];
        unset($data['customer']);

        return $data;
    }

    private function getDimensions($products)
    {
        // pega medidas dos produtos
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
        foreach ($products as $product) {
            for ($qty = 0; $qty < $product['quantity']; ++$qty) {
                $width +=  $product['width'];
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
            $error = 'Invalid dimensions cart [height, length, width, weight]';
            PrestaShopLogger::addLog('MercadoPago :: getDimensions = '.$error, MPApi::INFO, 0);

            $this->context->smarty->assign(
                $this->setErrorMercadoEnvios(
                    $error
                )
            );

            throw new Exception($error);
        }

        return $height.'x'.$width.'x'.$length.','.$weight;
    }

    public function createStandardCheckoutPreference()
    {
        $preferences = $this->getPrestashopPreferencesStandard(null);
        if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
            PrestaShopLogger::addLog("=====preferences=====".Tools::jsonEncode($preferences), MPApi::INFO, 0);
        }
        return $this->mercadopago->createPreference($preferences);
    }

    private function getExcludedPaymentMethods()
    {
        $payment_methods = $this->mercadopago->getPaymentMethods();
        $excluded_payment_methods = array();

        foreach ($payment_methods as $payment_method) {
            $pm_variable_name = 'MERCADOPAGO_'.Tools::strtoupper($payment_method['id']);
            $value = Configuration::get($pm_variable_name);

            if ($value == 'on') {
                $excluded_payment_methods[] = array(
                    'id' => $payment_method['id'],
                );
            }
        }

        return $excluded_payment_methods;
    }

    /**
     * salve the token for to use in IPN.
     *
     * @param $post
     * @param $cart
     */
    private function saveCard($result)
    {
        $token = $result['response']['metadata']['card_token_id'];
        $customerID = $result['response']['metadata']['customer_id'];

        $tokenPagamentoJson = array(
            'token' => $token,
        );
        $result_response = $this->mercadopago->addCustomerCard($tokenPagamentoJson, $customerID);

        return $result_response;
    }

    public function listenIPN($checkout, $topic, $id)
    {
        $payment_method_ids = array();
        $payment_ids = array();
        $payment_statuses = array();
        $payment_types = array();
        $credit_cards = array();
        $transaction_amounts = 0;
        $cardholders = array();
        $external_reference = '';
        $cost_mercadoEnvios = 0;
        $isMercadoEnvios = 0;


        if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
            PrestaShopLogger::addLog('MercadoPago :: listenIPN - topic = '.$topic, MPApi::INFO, 0);
            PrestaShopLogger::addLog('MercadoPago :: listenIPN - id = '.$id, MPApi::INFO, 0);
            PrestaShopLogger::addLog('MercadoPago :: listenIPN - checkout = '.$checkout, MPApi::INFO, 0);
        }

        if ($checkout == 'standard' && $topic == 'merchant_order' && $id > 0) {
            $result = $this->mercadopago->getMerchantOrder($id);
            $merchant_order_info = $result['response'];

            // check value
            $cart = new Cart($merchant_order_info['external_reference']);
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

            if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO') {
                $products = $cart->getProducts();
                $total = 0;
                foreach ($products as $product) {
                    $total += round($product['price_wt'], 0) * $product['quantity'];
                }
            }

            // check the module
            $id_order = $this->getOrderByCartId($merchant_order_info['external_reference']);
            $order = new Order($id_order);
            $total_amount = $merchant_order_info['total_amount'];
            if ($total_amount != $total) {
                return;
            }
            $status_shipment = null;
            if (isset($merchant_order_info['shipments'][0]) &&
                $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2') {
                $isMercadoEnvios = true;
                $cost_mercadoEnvios = $merchant_order_info['shipments'][0]['shipping_option']['cost'];

                $status_shipment = $merchant_order_info['shipments'][0]['status'];

                $id_order = $this->getOrderByCartId($merchant_order_info['external_reference']);
                $order = new Order($id_order);
                $order_status = null;
                switch ($status_shipment) {
                    case 'ready_to_ship':
                        $order_status = 'MERCADOPAGO_STATUS_8';
                        break;
                    case 'shipped':
                        $order_status = 'MERCADOPAGO_STATUS_9';
                        break;
                    case 'delivered':
                        $order_status = 'MERCADOPAGO_STATUS_10';
                        break;
                }
                if ($order_status != null) {
                    $existStates = $this->checkStateExist($id_order, Configuration::get($order_status));
                    if ($existStates) {
                        return;
                    }
                    $this->updateOrderHistory($order->id, Configuration::get($order_status));
                }

                return;
            }

            $payments = $merchant_order_info['payments'];
            $external_reference = $merchant_order_info['external_reference'];
            foreach ($payments as $payment) {
                // get payment info
                $result = $this->mercadopago->getPaymentStandard($payment['id']);
                $payment_info = $result['response']['collection'];
                // colect payment details
                $payment_ids[] = $payment_info['id'];
                $payment_statuses[] = $payment_info['status'];
                $payment_types[] = $payment_info['payment_type'];
                $transaction_amounts += $payment_info['transaction_amount'];
                if ($payment_info['payment_type'] == 'credit_card') {
                    $payment_method_ids[] = isset($payment_info['payment_method_id']) ?
                                            $payment_info['payment_method_id'] : '';
                    $credit_cards[] = isset($payment_info['card']['last_four_digits']) ?
                                            '**** **** **** '.$payment_info['card']['last_four_digits'] : '';
                    $cardholders[] = isset($payment_info['card']['cardholder']['name']) ?
                                    $payment_info['card']['cardholder']['name'] : '';
                }
            }

            if ($merchant_order_info['total_amount'] == $transaction_amounts) {
                if ($isMercadoEnvios) {
                    $transaction_amounts += $cost_mercadoEnvios;
                }

                $this->updateOrder(
                    $payment_ids,
                    $payment_statuses,
                    $payment_method_ids,
                    $payment_types,
                    $credit_cards,
                    $cardholders,
                    $transaction_amounts,
                    $external_reference,
                    $result
                );
            }
        } elseif ($checkout == 'custom' && $topic == 'payment' && $id > 0) {
            $result = $this->mercadopago->getPayment($id);

            $payment_info = $result['response'];

            $external_reference = $payment_info['external_reference'];

            $id_order = $this->getOrderByCartId($external_reference);
            $order = new Order($id_order);

            // colect payment details
            $payment_ids[] = $payment_info['id'];
            $payment_statuses[] = $payment_info['status'];
            $payment_types[] = $payment_info['payment_type_id'];
            $transaction_amounts += $payment_info['transaction_amount'];
            if ($payment_info['payment_type_id'] == 'credit_card') {
                $payment_method_ids[] = $payment_info['payment_method_id'];
                $credit_cards[] = '**** **** **** '.$payment_info['card']['last_four_digits'];
                $cardholders[] = $payment_info['card']['cardholder']['name'];
            }

            $this->updateOrder(
                $payment_ids,
                $payment_statuses,
                $payment_method_ids,
                $payment_types,
                $credit_cards,
                $cardholders,
                $transaction_amounts,
                $external_reference,
                $result
            );
        }
    }

    /**
     * Verify if there is state approved for order.
     */
    public static function getOrderStateApproved($id_order)
    {
        return (bool) Db::getInstance()->getValue(
            '
        SELECT `id_order_state`
        FROM '._DB_PREFIX_.'order_history
        WHERE `id_order` = '.(int) $id_order.'
        AND `id_order_state` = '.
            (int) Configuration::get('MERCADOPAGO_STATUS_1')
        );
    }

    /**
     * Verify if there is state approved for order.
     */
    public static function checkStateExist($id_order, $id_order_state)
    {
        return (bool) Db::getInstance()->getValue(
            '
        SELECT `id_order_state`
        FROM '._DB_PREFIX_.'order_history
        WHERE `id_order` = '.(int) $id_order.'
        AND `id_order_state` = '.
            (int) $id_order_state
        );
    }

    private function updateOrder(
        $payment_ids,
        $payment_statuses,
        $payment_method_ids,
        $payment_types,
        $credit_cards,
        $cardholders,
        $transaction_amounts,
        $external_reference,
        $result
    ) {
        $order = null;

        // if has two creditcard validate whether payment has same status in order to continue validating order
        if (count($payment_statuses) == 1 ||
             (count($payment_statuses) == 2 && $payment_statuses[0] == $payment_statuses[1])) {
            $order = null;
            $order_status = null;
            $payment_status = $payment_statuses[0];
            $payment_type = $payment_types[0];

            switch ($payment_status) {
                case 'in_process':
                    $order_status = 'MERCADOPAGO_STATUS_0';
                    break;
                case 'approved':
                    $order_status = 'MERCADOPAGO_STATUS_1';
                    break;
                case 'cancelled':
                    $order_status = 'MERCADOPAGO_STATUS_2';
                    break;
                case 'refunded':
                    $order_status = 'MERCADOPAGO_STATUS_4';
                    break;
                case 'charged_back':
                    $order_status = 'MERCADOPAGO_STATUS_5';
                    break;
                case 'in_mediation':
                    $order_status = 'MERCADOPAGO_STATUS_6';
                    break;
                case 'pending':
                    $order_status = 'MERCADOPAGO_STATUS_7';
                    break;
                case 'rejected':
                    $order_status = 'MERCADOPAGO_STATUS_3';
                    break;
                case 'ready_to_ship':
                    $order_status = 'MERCADOPAGO_STATUS_8';
                    break;
                case 'shipped':
                    $order_status = 'MERCADOPAGO_STATUS_9';
                    break;
                case 'delivered':
                    $order_status = 'MERCADOPAGO_STATUS_10';
                    break;
            }
            if ($payment_type == 'credit_card' &&
                $payment_status == 'approved') {
                $this->saveCard($result);
            }
            // just change if there is an order status
            if ($order_status) {
                $id_cart = $external_reference;
                $id_order = $this->getOrderByCartId($id_cart);
                if ($id_order) {
                    $order = new Order($id_order);

                    $existStates = $this->checkStateExist(
                        $id_order,
                        Configuration::get($order_status)
                    );
                    if ($existStates) {
                        return;
                    }
                }
                // If order wasn't created yet and payment is approved or pending or in_process, create it.
                // This can happen when user closes checkout standard
                if (empty($id_order) && ($payment_status == 'in_process' || $payment_status == 'approved' ||
                    $payment_status == 'pending')
                    ) {
                    $cart = new Cart($id_cart);
                    $total = (double) number_format($transaction_amounts, 2, '.', '');
                    $extra_vars = array(
                        '{bankwire_owner}' => $this->textshowemail,
                        '{bankwire_details}' => '',
                        '{bankwire_address}' => '',
                    );
                    $id_order = !$id_order ? $this->getOrderByCartId($id_cart) : $id_order;
                    $order = new Order($id_order);
                    $existStates = $this->checkStateExist($id_order, Configuration::get($order_status));
                    if ($existStates) {
                        return;
                    }

                    $displayName = UtilMercadoPago::setNamePaymentType($payment_type);

                    $this->validateOrder(
                        $id_cart,
                        Configuration::get($order_status),
                        $total,
                        $displayName,
                        null,
                        $extra_vars,
                        $cart->id_currency,
                        false,
                        $cart->secure_key
                    );
                } elseif (!empty($order) && $order->current_state != null &&
                     $order->current_state != Configuration::get($order_status)) {
                    $id_order = !$id_order ? $this->getOrderByCartId($id_cart) : $id_order;
                    $order = new Order($id_order);
                    /*
                     * this is necessary to ignore the transactions with the same
                     * external reference and states diferents
                     * the transaction approved cant to change the status, except refunded.
                     */
                    if ($payment_status == 'cancelled' || $payment_status == 'rejected') {
                        // check if is mercadopago
                        if ($order->module == "mercadopago") {
                            $retorno = $this->getOrderStateApproved($id_order);
                            if ($retorno) {
                                return;
                            }
                        } else {
                            return;
                        }
                    }
                    $this->updateOrderHistory($order->id, Configuration::get($order_status));

                    // Cancel the order to force products to go to stock.
                    switch ($payment_status) {
                        case 'cancelled':
                        case 'refunded':
                        case 'rejected':
                            $this->updateOrderHistory($id_order, Configuration::get('PS_OS_CANCELED'), false);
                            break;
                    }
                }
                if ($order) {
                    // update order payment information
                    $order_payments = $order->getOrderPayments();
                    foreach ($order_payments as $order_payment) {
                        $order_payment->transaction_id = implode(' / ', $payment_ids);
                        if ($payment_type == 'credit_card') {
                            $order_payment->card_number = implode(' / ', $credit_cards);
                            $order_payment->card_brand = implode(' / ', $payment_method_ids);
                            $order_payment->card_holder = implode(' / ', $cardholders);
                        }
                        $order_payment->save();
                    }
                }
            }
        }
    }

    /**
     * Return the customerID.
     */
    private function getCustomerCards($customerID)
    {
        $responseCustomer = $this->mercadopago->getCustomerCards($customerID);

        return $responseCustomer;
    }

    /**
     * Return the customerID.
     */
    private function getCustomerID()
    {
        $customer_fields = Context::getContext()->customer->getFields();
        $emailCustomer = $customer_fields['email'];
        $customerData = array();
        $customerData['email'] = $emailCustomer;
        $responseCustomer = $this->mercadopago->getCustomer($customerData);

        $customerID = null;
        if ($responseCustomer['status'] == 200 && $responseCustomer['response']['paging']['total'] > 0) {
            $customerID = $responseCustomer['response']['results'][0]['id'];
        } else {
            if ($customerID == null || empty($customerID)) {
                $customerData = array();
                $customerData['email'] = $emailCustomer;
                $customer = $this->mercadopago->createCustomerCard($customerData);

                if ($customer['response']['status'] == 200) {
                    $customerID = $customer['response']['id'];
                }
            }
        }

        return $customerID;
    }

    public function updateOrderHistory($id_order, $status, $mail = true)
    {
        // Change order state and send email
        $history = new OrderHistory();
        $history->id_order = (integer) $id_order;
        $history->changeIdOrderState((integer) $status, (integer) $id_order, true);
        if ($mail) {
            $extra_vars = array();
            $history->addWithemail(true, $extra_vars);
        }
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

    public function hookdisplayBeforeCarrier($params)
    {
        if (!isset($this->context->smarty->tpl_vars['delivery_option_list'])) {
            return;
        }

        //global $appended_text;
        $mercado_envios_activate = Configuration::get('MERCADOENVIOS_ACTIVATE');
        if (empty($mercado_envios_activate) ||
            $mercado_envios_activate == "false") {
            return;
        }

        // Init var
        $address = new Address($params['cart']->id_address_delivery);
        $lista_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );

        $delivery_option_list = $this->context->smarty->tpl_vars['delivery_option_list'];
        $retornoCalculadora = $this->calculateListCache($address->postcode);

        $mpCarrier = $lista_shipping['MP_SHIPPING'];

        foreach ($delivery_option_list->value as $id_address) {
            foreach ($id_address as $key) {
                foreach ($key['carrier_list'] as $id_carrier) {
                    //$obj_carrier = $delivery_option_list_param[$id_address];
                    if (in_array($id_carrier['instance']->id, $mpCarrier)) {
                        if (isset($lista_shipping['MP_CARRIER'][(int)$id_carrier['instance']->id])) {
                            $id_mercadoenvios_service_code = $lista_shipping['MP_CARRIER'][$id_carrier['instance']->id];
                            $calculadora = $retornoCalculadora[(string) $id_mercadoenvios_service_code];
                            $msg = $calculadora['estimated_delivery'].' '.$this->l('working days.');

                            $id_carrier['instance']->delay[$this->context->cart->id_lang] =
                                $this->l('After the post, receive the product ').$msg;
                        }
                    }
                }
            }
        }
    }

    private function calculateListCache($postcode)
    {
        $cart = Context::getContext()->cart;
        $products = $cart->getProducts();
        $price_total = 0;
        foreach ($products as $product) {
            for ($qty = 0; $qty < $product['quantity']; ++$qty) {
                $price_total += $product['price_wt'];
            }
        }

        $external_reference = $cart->id;

        $chave = $external_reference.
        '|'.
        $postcode.''.
        $price_total;

        if (isset(self::$listCache[$chave])) {
            return self::$listCache[$chave];
        } else {
            $retorno = $this->calculateList($postcode);
            self::$listCache[$chave] = $retorno;
            return $retorno;
        }
    }

    private function calculateList($postcode)
    {
        $cart = Context::getContext()->cart;
        $products = $cart->getProducts();
        $price_total = 0;

        $mp = $this->mercadopago;

        // pega medidas dos produtos
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
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
            $retorno = $this->verifyCache($params, $this->id_carrier);
            $shipping_cost = (float) $retorno['cost'];
            if ($retorno != null) {
                return $shipping_cost;
            }
        }

        return false;
    }

    private function verifyCache($params, $id_carrier)
    {
        $cart = Context::getContext()->cart;
        $products = $cart->getProducts();
        $price_total = 0;
        foreach ($products as $product) {
            for ($qty = 0; $qty < $product['quantity']; ++$qty) {
                $price_total += $product['price_wt'];
            }
        }

        $address = new Address($params->id_address_delivery);
        $postcode = $address->postcode;

        $external_reference = $cart->id;

        $chave = $external_reference.
        '|'.
        $id_carrier.''.
        $postcode.''.
        $price_total;

        if (array_key_exists($chave, self::$listCache)) {
            return self::$listCache[$chave];
        } else {
            $retorno = $this->calculate($params, $id_carrier);
            self::$listCache[$chave] = $retorno;

            return $retorno;
        }
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
        $mp = $this->mercadopago;

        // pega medidas dos produtos
        $width = 0;
        $height = 0;
        $length = 0;
        $weight = 0;
        foreach ($products as $product) {
            for ($qty = 0; $qty < $product['quantity']; ++$qty) {
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
            $error = 'Invalid dimensions cart [height, length, width, weight]';
            PrestaShopLogger::addLog("=====calculate=====".$error, MPApi::ERROR, 0);
           // throw new Exception($error);
        }

        $dimensions = $height.'x'.$width.'x'.$length.','.$weight;

        $postcode = $address->postcode;
        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLB') {
            $postcode = str_replace('-', '', $postcode);
        } /*elseif (Configuration::get('MERCADOPAGO_COUNTRY') == 'MLA') {
            $postcode = Tools::substr($postcode, 1);
        }*/
        $return = null;
        $paramsMP = array(
        'dimensions' => $dimensions,

        'zip_code' => $postcode,
        //'zip_code' => '5700',

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
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'carrier_group',
                    array('id_carrier' => (int) ($carrier->id),
                    'id_group' => (int) ($group['id_group']),
                    ),
                    'INSERT'
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
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'carrier_zone',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_zone' => (int) ($zone['id_zone']),
                    ),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_.'delivery',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_range_price' => (int) ($rangePrice->id),
                        'id_range_weight' => null,
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                    ),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_.'delivery',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_range_price' => null,
                        'id_range_weight' => (int) ($rangeWeight->id),
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                    ),
                    'INSERT'
                );
            }

            // Copy Logo
            @copy(dirname(__FILE__).'/views/img/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int) $carrier->id.'.jpg');

            // Return ID Carrier
            return (int) ($carrier->id);
        }

        return false;
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

    public function applyDiscount($cart, $payment_mode, $installments = 1)
    {
        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        $credit_card = (int) Configuration::get('MERCADOPAGO_ACTIVE_CREDITCARD');
        $boleto = (int) Configuration::get('MERCADOPAGO_ACTIVE_BOLETO');

        $rules = $cart->getCartRules();
        $discount_name = 'Desconto Mercado Pago Cart-ID=' . $cart->id;

        foreach ($rules as $value) {
            if ($value['name'] == $discount_name) {
                return $value['id_cart_rule'];
            }
        }

        if (count($percent) > 0) {
            if (($credit_card && $payment_mode == 'cartao') || ($boleto && $payment_mode == 'boleto')) {
                if ($installments == 1) {
                    $cart_rule = new CartRule();
                    $cart_rule->reduction_percent = $percent;
                    $cart_rule->reduction_amount = 0;
                    $cart_rule->active = true;
                    $cart_rule->date_from = date('Y-m-d H:i:s');
                    $cart_rule->date_to = date(
                        'Y-m-d H:i:s',
                        mktime(0, 0, 0, date("m"), date("d"), date("Y") + 10)
                    );
                    $cart_rule->partial_use = false;
                    $cart_rule->quantity = 9;
                    $cart_rule->quantity_per_user = 9;
                    $cart_rule->code = $cart->id . '-DISCOUNT-MP';
                    foreach (Language::getLanguages(true) as $lang) {
                        $cart_rule->name[$lang['id_lang']] = $discount_name;
                    }

                    $cart_rule->save();

                    $cart->addCartRule($cart_rule->id);
                    return $cart_rule->id;
                }
            }
        }
        return null;
    }


    public function uninstallModule()
    {
        Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', false);
        Configuration::updateValue('MERCADOPAGO_CUSTOM_BOLETO', false);
        Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', false);

        Configuration::updateValue('MERCADOPAGO_TWO_CARDS', false);
        Configuration::updateValue('MERCADOPAGO_COUPON_ACTIVE', false);
        Configuration::updateValue('MERCADOENVIOS_ACTIVATE', false);
    }

    public function setSettings()
    {
        $mp = new MPApi(Tools::getValue('MERCADOPAGO_CLIENT_ID'), Tools::getValue('MERCADOPAGO_CLIENT_SECRET'));

        $userResponse = $mp->getAccessTokenResponse();

        $moduleVersion = $this->version;
        $siteId = Configuration::get('MERCADOPAGO_COUNTRY');
        $dataCreated = date('Y-m-d H:i:s');

        $collectorId = $userResponse["user_id"];

        $phpVersion = phpversion();
        $soServer = PHP_OS;
        $modulesId = "PRESTASHOP " . $this->getPrestashopVersion();

        $status = 0;
        $statusTwoCards = 0;
        $statusMe = 0;
        $statusTicket = 0;
        $statusCoupon = 0;
        $statusStandard = 0;
        $statusCustom = 0;

        if (Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE') == "true" ||
            Configuration::get('MERCADOPAGO_CUSTOM_BOLETO') == "true" ||
            Configuration::get('MERCADOPAGO_STANDARD_ACTIVE') == "true"
        ) {
            $status = 1;
        }
        if (Configuration::get('MERCADOPAGO_TWO_CARDS') == "active") {
            $statusTwoCards = 1;
        }
        if (Configuration::get('MERCADOENVIOS_ACTIVATE') == "true") {
            $statusMe = 1;
        }
        if (Configuration::get('MERCADOPAGO_CUSTOM_BOLETO') == "true") {
            $statusTicket = 1;
        }
        if (Configuration::get('MERCADOPAGO_COUPON_ACTIVE') == "true") {
            $statusCoupon = 1;
        }
        if (Configuration::get('MERCADOPAGO_STANDARD_ACTIVE') == "true") {
            $statusStandard = 1;
        }
        if (Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE') == "true") {
            $statusCustom = 1;
        }

        $request = array(
            "ModuleVersion" => $moduleVersion,
            "SiteId" =>  $siteId,
            "DataCreated" => $dataCreated,
            "CollectorId" => $collectorId,
            "Status" => $status,
            "StatusTwoCards" => $statusTwoCards,
            "StatusMe" => $statusMe,
            "StatusTicket" => $statusTicket,
            "StatusCoupon" => $statusCoupon,
            "StatusStandard" => $statusStandard,
            "StatusCustom" => $statusCustom,
            "PhpVersion" => $phpVersion,
            "SoServer" => $soServer,
            "ModulesId" => $modulesId
        );

        try {
            $userResponse = $mp->saveSettings($request);
        } catch (Exception $e) {
            if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
                PrestaShopLogger::addLog("=====settings=====".$e->getMessage(), MPApi::ERROR, 0);
            }
        }
    }

    public function getPrestashopVersion()
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
}
