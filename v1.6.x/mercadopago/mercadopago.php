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
 *  @author    MERCAupdateOrderDOPAGO.COM REPRESENTA&Ccedil;&Otilde;ES LTDA.
 *  @copyright Copyright(c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License(OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

if (!defined('_PS_VERSION_')) {
    exit();
}

function_exists('curl_init');
include dirname(__FILE__) . '/includes/MPApi.php';
include dirname(__FILE__) . '/includes/CheckoutCustom.php';

class MercadoPago extends PaymentModule
{
    public static $listShipping;
    public static $appended_text;
    public static $listCache = array();
    public static $weightUnit = '';
    public static $dimensionUnit = '';
    public $id_carrier;
    private $site_url = null;

    private $dimensionUnitList = array(
        'CM' => 'CM',
        'IN' => 'IN',
        'CMS' => 'CM',
        'INC' => 'IN',
    );

    private $weightUnitList = array(
        'KG' => 'KGS',
        'KGS' => 'KGS',
        'LBS' => 'LBS',
        'LB' => 'LBS',
    );

    private $postCodeTest = array(
        'MLM' => 20117,
        'MLB' => 6541005,
        'MLA' => 5700,
    );

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
            'MP_SHIPPING_MAX_WE' => 25000,
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
            'MP_SHIPPING_MAX_WE' => 15000,
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
            'MP_SHIPPING_MAX_WE' => 30000,
        ),
    );

    public function __construct()
    {
        $this->name = 'mercadopago';
        $this->tab = 'payments_gateways';
        $this->version = "3.6.7";
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
        $this->description = $this->l('Receive payments by credit cards, ticket and balance money of Mercado Pago.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall MercadoPago?');
        $this->textshowemail = $this->l('You must follow MercadoPago rules for purchase to be valid');
        $this->site_url = Tools::htmlentitiesutf8(
            ((bool) Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
            . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__
        );
        $this->author = $this->l('MERCADOPAGO.COM Representações LTDA.');
        $this->link = new Link();
        $this->currencies_mode = 'checkbox';
        $this->mercadopago = $this->getAPI();
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
        FROM `' . _DB_PREFIX_ . 'order_state`
        WHERE `id_order_state` = ' . (int) $id_order_state
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
            array(
                '#4169E1',
                $this->l('Waiting payment Mercado Pago POS'),
                'waiting_POS',
                '010010000',
            ),
            array(
                '#37bf3a',
                $this->l('Transaction started'),
                'started',
                '010010000',
            ),
            array(
                '#ec2e15',
                $this->l('Transaction Partial Refunded'),
                'payment_partial_refund',
                '010010000',
            ),
        );

        foreach ($order_states as $key => $value) {
            if (!is_null($this->orderStateAvailable(Configuration::get('MERCADOPAGO_STATUS_' . $key)))) {
                continue;
            }
            $order_state = new OrderState();
            $order_state->module_name = 'mercadopago';
            $order_state->color = $value[0];
            $order_state->deleted = true;
            $order_state->unremovable = true;
            $order_state->send_email = $value[3][1];
            $order_state->unremovable = $value[3][2];
            $order_state->hidden = $value[3][3];
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

            $file = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
            copy((dirname(__file__) . '/views/img/mp_icon.gif'), $file);

            Configuration::updateValue('MERCADOPAGO_STATUS_' . $key, $order_state->id);
        }

        if (!is_null($this->orderStateAvailable(Configuration::get('MERCADOPAGO_STATUS_12')))) {
            Db::getInstance()->update(
                'order_state',
                array(
                    'logable' => 1,
                    'send_email' => 0,
                ),
                'module_name = "mercadopago" and id_order_state = ' . Configuration::get('MERCADOPAGO_STATUS_12')
            );
        }

        if (!is_null($this->orderStateAvailable(Configuration::get('MERCADOPAGO_STATUS_1')))) {
            Db::getInstance()->update(
                'order_state',
                array(
                    'logable' => 1,
                    'paid' => 1,
                    'send_email' => 1,
                    'invoice' => 1,
                    'pdf_invoice' => 1,
                ),
                'module_name = "mercadopago" and id_order_state = ' . Configuration::get('MERCADOPAGO_STATUS_1')
            );
        }

        return true;
    }

    private function populateEmail($lang, $name, $extension)
    {
        if (!file_exists(_PS_MAIL_DIR_ . $lang)) {
            mkdir(_PS_MAIL_DIR_ . $lang, 0777, true);
        }
        $new_template = _PS_MAIL_DIR_ . $lang . '/' . $name . '.' . $extension;
        if (!file_exists($new_template)) {
            $template = dirname(__file__) . "/mails/$lang/" . $name . '.' . $extension;
            copy($template, $new_template);
        }
    }

    /**
     * install module.
     */
    public function install()
    {
        try {
            $errors = array();
            if (!function_exists('curl_version')) {
                $errors[] = $this->l('Curl not installed');
                return false;
            }
            $this->uninstallOverrideMercadoEnvios();
            $this->dropTables();
            if (!parent::install() ||
                !$this->createStates() ||
                !$this->registerHook('payment') ||
                !$this->registerHook('paymentReturn') ||
                !$this->registerHook('displayHeader') ||
                !$this->registerHook('displayOrderDetail') ||
                !$this->registerHook('displayAdminOrder') ||
                !$this->registerHook('displayAdminOrderTabOrder') ||
                !$this->registerHook('displayAdminOrderContentOrder') ||
                !$this->registerHook('backOfficeHeader') ||
                !$this->registerHook('displayBackOfficeHeader') ||
                !$this->registerHook('displayBeforeCarrier') ||
                !$this->registerHook('displayFooter') ||
                !$this->registerHook('displayRightColumnProduct') ||
                !$this->registerHook('displayShoppingCartFooter') ||
                !$this->createTables()
            ) {
                return false;
            }
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "Ocorreu um erro na instalação === ",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->install"
            );
        }
        return true;
    }

    public function hookDisplayRightColumnProduct($params)
    {
        if (!$this->active || Configuration::get('MERCADOPAGO_PRODUCT_CALCULATE') == "false") {
            return;
        }

        $id_product = (int) Tools::getValue('id_product');
        $product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
        $price = $product->price;
        $tax = $product->tax_rate;
        $totalProduct = $price * (1 + ($tax / 100));
        $settings = null;
        if (Configuration::get('MERCADOPAGO_PUBLIC_KEY')) {
            $settings['totalAmount'] = Tools::ps_round($totalProduct, 2);
            $settings['isCart'] = "false";
            $settings['public_key'] = htmlentities(Configuration::get('MERCADOPAGO_PUBLIC_KEY'), ENT_COMPAT, 'UTF-8');
            $settings['country'] = htmlentities(Configuration::get('MERCADOPAGO_COUNTRY'), ENT_COMPAT, 'UTF-8');
            $settings['this_path_ssl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__;

            $this->context->smarty->assign($settings);
            return $this->display(__FILE__, 'views/templates/hook/calculateInstallments.tpl');
        }
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        if (!$this->active || Configuration::get('MERCADOPAGO_CART_CALCULATE') == "false") {
            return;
        }
        $settings = null;
        if (Configuration::get('MERCADOPAGO_PUBLIC_KEY')) {
            $settings['totalAmount'] = $this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
            $settings['isCart'] = "true";
            $settings['public_key'] = htmlentities(Configuration::get('MERCADOPAGO_PUBLIC_KEY'), ENT_COMPAT, 'UTF-8');
            $settings['country'] = htmlentities(Configuration::get('MERCADOPAGO_COUNTRY'), ENT_COMPAT, 'UTF-8');
            $settings['this_path_ssl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__;

            $this->context->smarty->assign($settings);
            return $this->display(__FILE__, 'views/templates/hook/calculateInstallments.tpl');
        }
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        if (!$this->active) {
            return;
        }

        $order = $params['order'];
        $id_order_state = $this->getOrderStatePending($order->id);
        $ticket = $this->getURLTicket($order);
        if ($order->module == $this->name &&
            $ticket != null &&
            $id_order_state == Configuration::get('MERCADOPAGO_STATUS_7')) {
            return $this->display(__FILE__, 'views/templates/hook/admin-order-tab.tpl');
        }
    }

    public function hookDisplayAdminOrderContentOrder($params)
    {
        if (!$this->active) {
            return;
        }
        $order = $params['order'];
        $ticket = $this->getURLTicket($order);

        if ($order->module == $this->name && $ticket != null) {
            $this->smarty->assign($ticket);
            return $this->display(__FILE__, 'views/templates/hook/admin-order-content.tpl');
        }
    }

    private function isMercadoEnvios($id_carrier)
    {
        $list_shipping = (array) Tools::jsonDecode(
            Configuration::get('MERCADOPAGO_CARRIER'),
            true
        );
        $id_mercadoenvios_service_code = 0;
        if (isset($list_shipping['MP_CARRIER']) &&
            array_key_exists($id_carrier, $list_shipping['MP_CARRIER'])) {
            $id_mercadoenvios_service_code = $list_shipping['MP_CARRIER'][$id_carrier];
        }
        return $id_mercadoenvios_service_code;
    }

    /**
     * Verify if there is state approved for order.
     */
    public static function getOrderStatePending($id_order)
    {
        $select = 'SELECT id_order_state FROM ' . _DB_PREFIX_ . 'order_history WHERE id_order = ' .
        (int) $id_order .
            ' order by date_add desc limit 1;';

        $result = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($select));
        return $result ? $result[0]['id_order_state'] : 0;
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int) $params['id_order']);

        $payments = $order->getOrderPaymentCollection();
        if (count($payments) > 0 && empty($payments[0]->transaction_id)) {
            $payment_ids = $this->mercadopago->getPaymentsID($order->id_cart);
            $payments[0]->transaction_id = implode(' / ', $payment_ids);
            $payments[0]->update();
        }

        $this->context->smarty->assign('pos_active', Configuration::get('MERCADOPAGO_POINT'));
        $this->setupOrderData($params['id_order']);
        $this->setupCarrierData($params['id_order']);

        return $this->display(__file__, '/views/templates/hook/display_admin_order.tpl');
    }

    private function setupOrderData($orderId)
    {
        $statusOrder = '';
        $id_order_state = $this->getOrderStatePending($orderId);
        if ($id_order_state == Configuration::get('MERCADOPAGO_STATUS_7')) {
            $statusOrder = 'Pendente';
        }

        $token_form = Tools::getAdminToken('AdminOrder' . Tools::getValue('id_order'));
        $data = array(
            'id_order' => $orderId,
            'token_form' => $token_form,
            'statusOrder' => $statusOrder,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
            'cancel_action_url' => $this->link->getModuleLink(
                'mercadopago',
                'cancelorder',
                array(),
                Configuration::get('PS_SSL_ENABLED'),
                null,
                null,
                false
            ),
            'payment_pos_action_url' => $this->link->getModuleLink(
                'mercadopago',
                'paymentpos',
                array(),
                Configuration::get('PS_SSL_ENABLED'),
                null,
                null,
                false
            ),
        );
        if (Configuration::get('MERCADOPAGO_POINT') == "true" &&
            $id_order_state == Configuration::get('MERCADOPAGO_STATUS_11')) {
            $data['pos_options'] = $this->loadPoints();
            $data['showPoint'] = true;
        } else {
            $data['showPoint'] = false;
        }
        $this->context->smarty->assign($data);
    }

    private function setupCarrierData($orderId)
    {
        $order = new Order((int) $orderId);
        $id_order_carrier = $order->getIdOrderCarrier();

        $order_carrier = new OrderCarrier($id_order_carrier);
        $id_mercadoenvios_service_code = $this->isMercadoEnvios($order_carrier->id_carrier);

        if ($id_mercadoenvios_service_code > 0) {
            $order_payments = $order->getOrderPayments();
            foreach ($order_payments as $order_payment) {
                $result = $this->mercadopago->getPayment($order_payment->transaction_id, "custom");
                if ($result['status'] == '404') {
                    $result = $this->mercadopago->getPayment($order_payment->transaction_id, "standard");
                }
                if ($result['status'] == '200') {
                    $payment_info = $result['response'];
                    if (isset($payment_info)) {
                        $merchant_order_id = $payment_info['order']['id'];
                        $result_merchant = $this->mercadopago->getMerchantOrder($merchant_order_id);
                        $return_tracking = $this->setTracking(
                            $order,
                            $result_merchant['response']['shipments'],
                            false
                        );
                        $tag_shipment = $this->mercadopago->getTagShipment($return_tracking['shipment_id']);
                        $tag_shipment_zebra = $this->mercadopago->getTagShipmentZebra($return_tracking['shipment_id']);
                        $return_tracking['tag_shipment_zebra'] = $tag_shipment_zebra;
                        $return_tracking['tag_shipment'] = $tag_shipment;
                        $this->context->smarty->assign($return_tracking);
                    }
                }
                break;
            }
        }
    }

    private function loadPoints()
    {
        $pos_options = array();

        $str = Tools::file_get_contents(dirname(__FILE__) . "/pos.json");
        $json = Tools::jsonDecode($str, true);

        foreach ($json['points'] as $field) {
            $pos_options[$field['poi']] = $field['label'];
        }

        return $pos_options;
    }

    private function createTables()
    {
        try {
            $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mercadopago_point_order` (
        `id` int(11) unsigned NOT NULL auto_increment,
        `id_transaction` varchar(255) NOT NULL,
        `id_order` int(10) unsigned NOT NULL ,
        PRIMARY KEY  (`id`)
        ) ENGINE=' . _MYSQL_ENGINE_ .
                ' DEFAULT CHARSET=utf8  auto_increment=1;';
            if (!Db::getInstance()->Execute($sql)) {
                return false;
            }

            $sql = " CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "mercadopago_orders_initpoint (
          `mercadopago_orders_id` int(11) unsigned NOT NULL auto_increment,
          `cart_id` int(15),
          `init_point` varchar(200),
          PRIMARY KEY  (`mercadopago_orders_id`)
          ) ENGINE=" . _MYSQL_ENGINE_ .
                ' DEFAULT CHARSET=utf8  auto_increment=1;';
            if (!Db::getInstance()->Execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "There is a problem with create a tables. ",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "createTables"
            );
        }

        return true;
    }

    private function getInfomationsForTicket($id_address_invoice)
    {
        $customer_fields = Context::getContext()->customer->getFields();
        $address_invoice = new Address((integer) $id_address_invoice);

        $result = array(
            'email' => $customer_fields['email'],
            'firstname' => $customer_fields['firstname'],
            'cpf' => isset($customer_fields['document']) ? $customer_fields['document'] : '',
            'lastname' => $customer_fields['lastname'],
            'lastname' => $customer_fields['lastname'],
            'address' => $address_invoice->address1,
            'number' => '',
            'city' => $address_invoice->city,
            'postcode' => $address_invoice->postcode,
            'state' => UtilMercadoPago::getIsoCodeStateById($address_invoice->id_state),
        );

        return $result;
    }

    public function hookDisplayOrderDetail($params)
    {
        if ($params['order']->module == 'mercadopago') {
            $order = new Order(Tools::getValue('id_order'));

            $statusPS = (int) $order->getCurrentState();
            if (Configuration::get("MERCADOPAGO_STATUS_12") == $statusPS) {
                $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mercadopago_orders_initpoint
                WHERE cart_id = ' . $order->id_cart;
                if ($row = Db::getInstance()->getRow($sql)) {
                    if ($row['init_point']) {
                        $settings = array('init_point' => $row['init_point']);
                    }
                }
            } else {
                $order_payments = $order->getOrderPayments();
                foreach ($order_payments as $order_payment) {
                    $result = $this->mercadopago->getPayment($order_payment->transaction_id, "custom");
                    if ($result['status'] == '404') {
                        $result = $this->mercadopago->getPayment($order_payment->transaction_id, "standard");
                    }
                    if ($result['status'] == 200) {
                        $payment_info = $result['response'];
                        $id_mercadoenvios_service_code = $this->isMercadoEnvios($order->id_carrier);

                        if ($id_mercadoenvios_service_code > 0) {
                            $merchant_order_id = $payment_info['order']['id'];
                            $result_merchant = $this->mercadopago->getMerchantOrder($merchant_order_id);
                            $return_tracking = $this->setTracking(
                                $order,
                                $result_merchant['response']['shipments'],
                                true
                            );
                            $this->context->smarty->assign($return_tracking);
                        }

                        $payment_type_id = $payment_info['payment_type_id'];

                        if ($payment_type_id == 'ticket' && isset($payment_info['transaction_details'])) {
                            $settings = array(
                                'boleto_url' => isset($payment_info['transaction_details']) ?
                                urldecode(
                                    $payment_info['transaction_details']['external_resource_url']
                                ) : "",
                                'payment_type_id' => $payment_type_id,
                            );
                        }
                    }
                    break;
                }
            }
            $settings['this_path_ssl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__;
            $this->context->smarty->assign($settings);

            return $this->display(__file__, '/views/templates/hook/print_details_order.tpl');
        }
    }

    private function getURLTicket($order)
    {
        $settings = null;
        $order_payments = $order->getOrderPayments();
        foreach ($order_payments as $order_payment) {
            $result = $this->mercadopago->getPayment($order_payment->transaction_id, "custom");
            if ($result['status'] == '404') {
                $result = $this->mercadopago->getPayment($order_payment->transaction_id, "standard");
            }
            if ($result['status'] == 200) {
                $payment_info = $result['response'];
                $payment_type_id = $payment_info['payment_type_id'];

                if ($payment_type_id == 'ticket' && isset($payment_info['transaction_details'])) {
                    $settings = array(
                        'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
                        htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
                        'boleto_url' => isset($payment_info['transaction_details']) ?
                        urldecode($payment_info['transaction_details']['external_resource_url']) : "",
                        'payment_type_id' => $payment_type_id,
                    );
                }
            }
            break;
        }
        return $settings;
    }

    private function setTracking($order, $shipments, $update)
    {
        $shipment_id = null;
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
                $result = array(
                    'shipment_id' => $shipment_id,
                    'tracking_number' => $tracking_number,
                    'name' => $response_shipment['shipping_option']['name'],
                    'status' => $status,
                    'substatus' => $response_shipment['substatus'],
                    'substatus_description' => $substatus_description,
                    'estimated_delivery' => $estimated_delivery->format('d/m/Y'),
                    'estimated_handling_limit' => $estimated_handling_limit->format('d/m/Y'),
                    'estimated_delivery_final' => $estimated_delivery_final->format('d/m/Y'),
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
                );
                if ($update) {
                    $id_order_carrier = $order->getIdOrderCarrier();
                    $order_carrier = new OrderCarrier($id_order_carrier);
                    $order_carrier->tracking_number = $tracking_number;
                    $order_carrier->update();
                }
            } else {
                $result = array(
                    'shipment_id' => $shipment_id,
                    'tracking_number' => '',
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
                );
            }
        }

        return $result;
    }

    private function removeMercadoEnvios()
    {
        $list_shipping = (array) Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'));

        if (isset($list_shipping['MP_SHIPPING'])) {
            foreach ($list_shipping['MP_SHIPPING'] as $id_carrier) {
                $carrier = new Carrier($id_carrier);
                $carrier->deleted = false;
                $carrier->active = false;
                $carrier->save();
            }
        }
        Configuration::deleteByName('MERCADOPAGO_CARRIER');
        $this->uninstallOverrideMercadoEnvios();
    }

    private function dropTables()
    {
        // Exclui as tabelas
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "mercadopago_orders`;";
        Db::getInstance()->execute($sql);

        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "mercadopago_orders_initpoint`;";
        Db::getInstance()->execute($sql);
    }

    public function uninstall()
    {
        $this->uninstallOverrideMercadoEnvios();
        $this->removeMercadoEnvios();
        $this->uninstallModule();
        $this->dropTables();

        // continue the states
        return ($this->uninstallPaymentSettings() &&
            Configuration::deleteByName('MERCADOPAGO_CATEGORY') &&
            Configuration::deleteByName('MERCADOPAGO_CREDITCARD_BANNER') &&
            Configuration::deleteByName('MERCADOPAGO_CREDITCARD_ACTIVE') &&
            Configuration::deleteByName('MERCADOPAGO_STANDARD_ACTIVE') &&
            Configuration::deleteByName('MERCADOPAGO_STANDARD_BANNER') &&
            Configuration::deleteByName('MERCADOPAGO_WINDOW_TYPE') &&
            Configuration::deleteByName('MERCADOPAGO_IFRAME_WIDTH') &&
            Configuration::deleteByName('MERCADOPAGO_IFRAME_HEIGHT') &&
            Configuration::deleteByName('MERCADOPAGO_INSTALLMENTS') &&
            Configuration::deleteByName('MERCADOPAGO_AUTO_RETURN') &&
            Configuration::deleteByName('MERCADOPAGO_COUNTRY') &&
            Configuration::deleteByName('MERCADOPAGO_COUPON_ACTIVE') &&
            Configuration::deleteByName('MERCADOPAGO_POINT') &&
            Configuration::deleteByName('MERCADOPAGO_COUPON_TICKET_ACTIVE') &&
            Configuration::deleteByName('MERCADOENVIOS_ACTIVATE') &&
            Configuration::deleteByName('MERCADOPAGO_CARRIER') &&
            Configuration::deleteByName('MERCADOPAGO_CARRIER_ID_1') &&
            Configuration::deleteByName('MERCADOPAGO_CARRIER_ID_2') &&
            Configuration::deleteByName('MERCADOPAGO_DISCOUNT_PERCENT') &&
            Configuration::deleteByName('MERCADOPAGO_ACTIVE_CREDITCARD') &&
            Configuration::deleteByName('MERCADOPAGO_ACTIVE_BOLETO') &&
            Configuration::deleteByName('MERCADOPAGO_CLIENT_ID') &&
            Configuration::deleteByName('MERCADOPAGO_CLIENT_SECRET') &&
            Configuration::deleteByName('MERCADOPAGO_ACCESS_TOKEN') &&
            Configuration::deleteByName('MERCADOPAGO_PUBLIC_KEY') &&
            parent::uninstall());
    }

    public function uninstallPaymentSettings()
    {
        $client_id = Configuration::get('MERCADOPAGO_CLIENT_ID');
        $client_secret = Configuration::get('MERCADOPAGO_CLIENT_SECRET');

        $access_token = Tools::getValue('MERCADOPAGO_ACCESS_TOKEN');
        $public_key = Tools::getValue('MERCADOPAGO_PUBLIC_KEY');

        $mp = $this->mercadopago;

        if ($client_id != '' && $client_secret != '') {
            $payment_methods = $mp->getPaymentMethods();
            foreach ($payment_methods as $payment_method) {
                $pm_variable_name = 'MERCADOPAGO_' . Tools::strtoupper($payment_method['id']);
                if (!Configuration::deleteByName($pm_variable_name)) {
                    return false;
                }
            }
        }

        if (!empty($access_token) && !empty($public_key)) {
            $offline_methods_payments = $mp->getOfflinePaymentMethods();
            foreach ($offline_methods_payments as $offline_payment) {
                $op_banner_variable = 'MERCADOPAGO_' . Tools::strtoupper($offline_payment['id'] . '_BANNER');
                $op_active_variable = 'MERCADOPAGO_' . Tools::strtoupper($offline_payment['id'] . '_ACTIVE');
                if (!Configuration::deleteByName($op_banner_variable) ||
                    !Configuration::deleteByName($op_active_variable)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function getLoginStandardContent()
    {
        $client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
        $client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');
        $success = true;
        $errors = array();

        if (empty($client_id) || empty($client_secret)) {
            $errors[] = $this->l('Please, complete the fields Client Id and Client Secret.');
            $success = false;
        }
        if ($this->validateCredential($client_id, $client_secret)) {
            Configuration::updateValue('MERCADOPAGO_CLIENT_ID', $client_id);
            Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', $client_secret);

            $country = $this->mercadopago->getCountry();
            $this->setDefaultValues($client_id, $client_secret, $country);
        } else {
            $errors[] = $this->l('Client Id or Client Secret invalid.');
            $success = false;
        }
        return $this->renderSettings($errors, $success, 'Basic');
    }

    private function getLoginCustomContent()
    {
        $access_token = Tools::getValue('MERCADOPAGO_ACCESS_TOKEN');
        $public_key = Tools::getValue('MERCADOPAGO_PUBLIC_KEY');
        $success = true;
        $errors = array();

        if (empty($access_token) || empty($public_key)) {
            $errors[] = $this->l('Please, complete the fields Access Token and Public Key.');
            $success = false;
        }

        if ($success) {
            $validatedCredentials = $this->validCkoCustomCredentials($access_token, $public_key);
            $success = $validatedCredentials['success'];
            $errors = $validatedCredentials['errors'];
            $country = $this->mercadopago->getCountry();
            Configuration::updateValue('MERCADOPAGO_COUNTRY', $country);
            Configuration::updateValue('MERCADOPAGO_CUSTOM_ACTIVE', 'false');
        }

        $this->setSponsorId($country);

        return $this->renderSettings($errors, $success, 'Custom');
    }

    private function getCheckoutStandardContent()
    {
        $client_id = Tools::getValue('MERCADOPAGO_CLIENT_ID');
        $client_secret = Tools::getValue('MERCADOPAGO_CLIENT_SECRET');

        $errors = array();
        $success = true;

        if (empty($client_id) || empty($client_secret)) {
            $errors[] = $this->l('Please, complete the fields Client Id and Client Secret.');
            $success = false;
        }

        try {
            if (!$this->validateCredential($client_id, $client_secret)) {
                $errors[] = $this->l('Client Id or Client Secret invalid.');
                $success = false;
            } else {
                $mp = $this->mercadopago;
                $country = $mp->getCountry();

                $this->setDefaultValues($client_id, $client_secret, $country);
                $mercadoenvios_activate = Tools::getValue('MERCADOENVIOS_ACTIVATE');
                Configuration::updateValue('MERCADOENVIOS_ACTIVATE', $mercadoenvios_activate);

                if ($mercadoenvios_activate == 'true') {
                    $paramsMP = array(
                        'dimensions' => '30x30x30,500',
                        'zip_code' => $this->postCodeTest[$country],
                        'item_price' => (double) 1,
                        'free_method' => '', // optional
                    );
                    $response = $mp->calculateEnvios($paramsMP);
                    if (empty($response['response'])) {
                        $errors[] = $this->l(
                            'Please, enable your Mercado Envios in your settings of Mercado Pago.'
                        );
                        $mercadoenvios_activate = 'false';
                        Configuration::updateValue('MERCADOPAGO_CARRIER', 0);
                        $success = false;
                    }
                    if (count(Tools::jsonDecode(Configuration::get('MERCADOPAGO_CARRIER'))) == 0) {
                        $this->setCarriers();
                        $this->installOverrideMercadoEnvios();
                    }
                } else {
                    $this->removeMercadoEnvios();
                }

                $configCard = $mp->setEnableDisableTwoCard(Tools::getValue('MERCADOPAGO_TWO_CARDS'));

                if (isset($configCard['response']['two_cards'])) {
                    $two_cards = $configCard['response']['two_cards'];
                    Configuration::updateValue('MERCADOPAGO_TWO_CARDS', $two_cards);
                }

                $this->setSponsorId($country);

                // Update CheckoutStandard configurations from fields
                $this->updateCheckoutStandardConfigurations();
            }
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "An installation error occurred",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->getContent"
            );

            $this->context->smarty->assign(
                array('message_error' => $e->getMessage(),
                    'version' => $this->getPrestashopVersion())
            );
            return $this->display(__file__, '/views/templates/front/error_admin.tpl');
        }
        return $this->renderSettings($errors, $success, 'Basic');
    }

    private function getCheckoutCustomContent()
    {
        $access_token = Tools::getValue('MERCADOPAGO_ACCESS_TOKEN');
        $public_key = Tools::getValue('MERCADOPAGO_PUBLIC_KEY');

        $success = false;
        try {
            $validatedCredentials = $this->validCkoCustomCredentials($access_token, $public_key);
            $success = $validatedCredentials['success'];
            $errors = $validatedCredentials['errors'];
            $country = $this->mercadopago->getCountry();
            Configuration::updateValue('MERCADOPAGO_COUNTRY', $country);
            if ($success) {
                $this->updateCheckoutCustomConfigurations();
            }
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "An installation error occurred",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->getContent"
            );

            $this->context->smarty->assign(
                array('message_error' => $e->getMessage(),
                    'version' => $this->getPrestashopVersion())
            );

            $this->setSponsorId($country);

            return $this->display(__file__, '/views/templates/front/error_admin.tpl');
        }

        return $this->renderSettings($errors, $success, 'Custom');
    }

    private function updateCheckoutStandardConfigurations()
    {
        $configs = array('MERCADOPAGO_STANDARD_ACTIVE', 'MERCADOPAGO_CUSTOM_TEXT',
            'MERCADOPAGO_PERCENT_EXTRA', 'MERCADOPAGO_STANDARD_BANNER',
            'MERCADOPAGO_WINDOW_TYPE', 'MERCADOPAGO_IFRAME_WIDTH',
            'MERCADOPAGO_IFRAME_HEIGHT', 'MERCADOPAGO_INSTALLMENTS',
            'MERCADOPAGO_AUTO_RETURN');

        foreach ($configs as $config) {
            Configuration::updateValue($config, Tools::getValue($config));
        }
    }

    private function updateCheckoutCustomConfigurations()
    {
        $configs = array(
            'MERCADOPAGO_COUPON_ACTIVE',
            'MERCADOPAGO_CREDITCARD_ACTIVE',
            'MERCADOPAGO_PRODUCT_CALCULATE', 'MERCADOPAGO_CART_CALCULATE',
            'MERCADOPAGO_CUSTOM_ACTIVE',
        );

        Configuration::updateValue(
            'MERCADOPAGO_DISCOUNT_PERCENT',
            (float) Tools::getValue('MERCADOPAGO_DISCOUNT_PERCENT')
        );

        Configuration::updateValue(
            'MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO',
            (int) Tools::getValue('MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO')
        );

        Configuration::updateValue(
            'MERCADOPAGO_COUPON_TICKET_ACTIVE',
            Tools::getValue('MERCADOPAGO_COUPON_TICKET_ACTIVE')
        );

        foreach ($configs as $config) {
            Configuration::updateValue($config, Tools::getValue($config));
        }
    }

    private function validCkoCustomCredentials($access_token, $public_key)
    {
        $success = true;
        $errors = null;
        $mp = $this->mercadopago;

        Configuration::updateValue('MERCADOPAGO_PUBLIC_KEY', $public_key);
        Configuration::updateValue('MERCADOPAGO_ACCESS_TOKEN', $access_token);

        if ($public_key != "") {
            $returnValidPublicKey = $mp->isValidPublicKey($public_key);
            if (!$returnValidPublicKey) {
                $errors[] = $this->l('Please, check your public key because it is invalid.');
                $success = false;
                Configuration::updateValue('MERCADOPAGO_PUBLIC_KEY', "");
            }
        }

        if ($access_token != "") {
            $returnValidAccessToken = $mp->isValidAccessToken($access_token);
            if (!$returnValidAccessToken) {
                $errors[] = $this->l('Please, check your access token because it is invalid.');
                $success = false;
                Configuration::updateValue('MERCADOPAGO_ACCESS_TOKEN', "");
            }
        }

        return array('success' => $success, "errors" => $errors);
    }

    private function setSponsorId($current_country)
    {
        $sponsorID = UtilMercadoPago::getString(Configuration::get("SPONSOR_ID"));
        $test_user = $this->mercadopago->isTestUser();
        Configuration::updateValue('MERCADOPAGO_USER_TEST', $test_user);
        if (!$test_user) {
            if ($sponsorID == "") {
                Configuration::updateValue(
                    'MERCADOPAGO_SPONSOR_ID',
                    UtilMercadoPago::$DEFAULT_SPONSOR_ID[$current_country]
                );
            } else {
                Configuration::updateValue('MERCADOPAGO_SPONSOR_ID', $sponsorID);
            }
        }
    }

    public function getAPI()
    {
        $mp = new MPApi();
        $client_id = Configuration::get('MERCADOPAGO_CLIENT_ID');
        $client_secret = Configuration::get('MERCADOPAGO_CLIENT_SECRET');

        if (!empty($client_id) && !empty($client_secret)) {
            $mp = new MPApi();
            $mp->setCredentialsStandard($client_id, $client_secret);
        }
        return $mp;
    }

    private function getCategories()
    {
        return array(
            "art" => 'Collectibles & Art',
            "baby" => 'Toys for Baby, Stroller, Stroller Accessories, Car Safety Seats',
            "coupons" => 'Coupons',
            "donations" => 'Donations',
            "computing" => 'Computers & Tablets',
            "cameras" => 'Cameras & Photography',
            "video_games" => 'Video Games & Consoles',
            "television" => 'LCD, LED, Smart TV, Plasmas, TVs',
            "car_electronics" => 'Car Audio, Car Alarm Systems & Security, Car DVRs, Car Video Players, Car PC',
            "electronics" => 'Audio & Surveillance, Video & GPS, Others',
            "automotive" => 'Parts & Accessories',
            "entertainment" => 'Music, Movies & Series, Books, Magazines & Comics, Board Games & Toys',
            "fashion" => 'Men\'s, Women\'s, Kids & baby,'.
            'Handbags & Accessories, Health & Beauty, Shoes, Jewelry & Watches',
            "games" => 'Online Games & Credits',
            "home" => 'Home appliances. Home & Garden',
            "musical" => 'Instruments & Gear',
            "phones" => 'Cell Phones & Accessories',
            "services" => 'General services',
            "learnings" => 'Trainings, Conferences, Workshops',
            "tickets" => 'Tickets for Concerts, Sports, Arts, Theater, Family, Excursions tickets, Events & more',
            "travels" => 'Plane tickets, Hotel vouchers, Travel vouchers',
            "others" => 'Other categories',
        );
    }

    private function renderSettings($errors = array(), $success = true, $active_tab = 'Global')
    {
        $client_id = Configuration::get('MERCADOPAGO_CLIENT_ID');
        $client_secret = Configuration::get('MERCADOPAGO_CLIENT_SECRET');
        $access_token = Configuration::get('MERCADOPAGO_ACCESS_TOKEN');
        $public_key = Configuration::get('MERCADOPAGO_PUBLIC_KEY');
        $payment_methods = null;
        $payment_methods_settings = null;
        $offline_methods_payments = null;
        $offline_payment_settings = null;
        if (!empty($client_id) && !empty($client_secret) && $success) {
            $mp = $this->mercadopago;
            $payment_methods = $mp->getPaymentMethods();
            $exclude_all = true;
            foreach ($payment_methods as $payment_method) {
                $pm_variable_name = 'MERCADOPAGO_' . Tools::strtoupper($payment_method['id']);
                $value = Tools::getValue($pm_variable_name);

                if ($value != 'on') {
                    $exclude_all = false;
                }
                // current settings
                $payment_methods_settings[$payment_method['id']] = Configuration::get($pm_variable_name);
            }
            if (!$exclude_all) {
                foreach ($payment_methods as $payment_method) {
                    $pm_variable_name = 'MERCADOPAGO_' . Tools::strtoupper($payment_method['id']);
                    if (Tools::getValue('submit_checkout_standard')) {
                        $value = Tools::getValue($pm_variable_name);
                        // save setting per payment_method
                        Configuration::updateValue($pm_variable_name, $value);
                    }
                    $payment_methods_settings[$payment_method['id']] = Configuration::get($pm_variable_name);
                }
            } else {
                $errors[] = $this->l('Cannnot exclude all payment methods.');
                $success = false;
            }
        }
        if (!empty($access_token) && !empty($public_key) && $success) {
            $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();
            $offline_payment_settings = array();

            foreach ($offline_methods_payments as $offline_payment) {
                $op_active_variable = 'MERCADOPAGO_' . Tools::strtoupper($offline_payment['id'] . '_ACTIVE');

                if (Tools::getValue('submit_checkout_custom')) {
                    if (!Tools::getValue("MERCADOPAGO_CUSTOM_ACTIVE")) {
                        Configuration::updateValue($op_active_variable, "false");
                    } else {
                        $op_active = Tools::getValue($op_active_variable);
                        Configuration::updateValue($op_active_variable, $op_active);
                    }
                }
                $offline_payment_settings[$offline_payment['id']] = array(
                    'name' => $offline_payment['name'],
                    'disabled' => Configuration::get($op_active_variable),
                );

                if ($offline_payment['payment_type_id'] == "ticket") {
                    if (!Tools::getValue("MERCADOPAGO_CUSTOM_ACTIVE")) {
                        Configuration::updateValue("MERCADOPAGO_CUSTOM_BOLETO", "true");
                    } else {
                        Configuration::updateValue(
                            'MERCADOPAGO_CUSTOM_BOLETO',
                            Configuration::get($op_active_variable) == "" ? "false" : "true"
                        );
                    }
                }
            }
            if (Tools::getValue('MERCADOPAGO_CUSTOM_ACTIVE') == "false") {
                Configuration::updateValue("MERCADOPAGO_CREDITCARD_ACTIVE", "true");
                Configuration::updateValue("MERCADOPAGO_CUSTOM_BOLETO", "true");
            }
        }

        $test_user = '';
        $requirements = UtilMercadoPago::checkRequirements();

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
            'custom_text' => htmlentities(
                UtilMercadoPago::getString(Configuration::get('MERCADOPAGO_CUSTOM_TEXT')),
                ENT_COMPAT,
                'UTF-8'
            ),
            'categories' => $this->getCategories(),
            'two_cards' => htmlentities(Configuration::get('MERCADOPAGO_TWO_CARDS'), ENT_COMPAT, 'UTF-8'),
            'MERCADOPAGO_PRODUCT_CALCULATE' => htmlentities(
                Configuration::get('MERCADOPAGO_PRODUCT_CALCULATE'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'MERCADOPAGO_CART_CALCULATE' => htmlentities(
                Configuration::get('MERCADOPAGO_CART_CALCULATE'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'sponsor_id' => htmlentities(Configuration::get('SPONSOR_ID'), ENT_COMPAT, 'UTF-8'),
            'public_key' => htmlentities(Configuration::get('MERCADOPAGO_PUBLIC_KEY'), ENT_COMPAT, 'UTF-8'),
            'access_token' => htmlentities(Configuration::get('MERCADOPAGO_ACCESS_TOKEN'), ENT_COMPAT, 'UTF-8'),
            'custom_active' => htmlentities(Configuration::get('MERCADOPAGO_CUSTOM_ACTIVE'), ENT_COMPAT, 'UTF-8'),
            'client_id' => htmlentities(Configuration::get('MERCADOPAGO_CLIENT_ID'), ENT_COMPAT, 'UTF-8'),
            'client_secret' => htmlentities(Configuration::get('MERCADOPAGO_CLIENT_SECRET'), ENT_COMPAT, 'UTF-8'),
            'country' => htmlentities(Configuration::get('MERCADOPAGO_COUNTRY'), ENT_COMPAT, 'UTF-8'),
            'category' => htmlentities(Configuration::get('MERCADOPAGO_CATEGORY'), ENT_COMPAT, 'UTF-8'),
            'percent_extra' => htmlentities(Configuration::get('MERCADOPAGO_PERCENT_EXTRA'), ENT_COMPAT, 'UTF-8'),
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
            'point_active' => htmlentities(Configuration::get('MERCADOPAGO_POINT'), ENT_COMPAT, 'UTF-8'),
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
            'percent' => htmlentities(
                Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'active_credicard_discount' => htmlentities(
                Configuration::get('MERCADOPAGO_ACTIVE_DISCOUNT_CREDITCARD'),
                ENT_COMPAT,
                'UTF-8'
            ),
            'active_boleto_discount' => htmlentities(
                Configuration::get('MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO'),
                ENT_COMPAT,
                'UTF-8'
            ),

            'standard_banner' => htmlentities(Configuration::get('MERCADOPAGO_STANDARD_BANNER'), ENT_COMPAT, 'UTF-8'),
            'window_type' => htmlentities(Configuration::get('MERCADOPAGO_WINDOW_TYPE'), ENT_COMPAT, 'UTF-8'),
            'iframe_width' => htmlentities(Configuration::get('MERCADOPAGO_IFRAME_WIDTH'), ENT_COMPAT, 'UTF-8'),
            'iframe_height' => htmlentities(Configuration::get('MERCADOPAGO_IFRAME_HEIGHT'), ENT_COMPAT, 'UTF-8'),
            'installments' => htmlentities(Configuration::get('MERCADOPAGO_INSTALLMENTS'), ENT_COMPAT, 'UTF-8'),
            'auto_return' => htmlentities(Configuration::get('MERCADOPAGO_AUTO_RETURN'), ENT_COMPAT, 'UTF-8'),
            'uri' => $_SERVER['REQUEST_URI'],
            'payment_methods' => $payment_methods,
            'payment_methods_settings' => $payment_methods_settings,
            'offline_methods_payments' => $offline_methods_payments,
            'offline_payment_settings' => $offline_payment_settings,
            'errors' => $errors,
            'success' => $success,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
            'version' => $this->getPrestashopVersion(),
            'active_tab' => $active_tab,
        );

        if (!Tools::getValue('save_general')) {
            $this->setSettings();
        }
        return $settings;
    }

    public function getContent()
    {
        $this->context->controller->addCss($this->_path . 'views/css/settings.css', 'all');
        $this->context->controller->addCss($this->_path . 'views/css/style.css', 'all');

        Configuration::updateValue(
            'MERCADOPAGO_EMAIL_ADMIN',
            Configuration::get('PS_SHOP_EMAIL')
        );

        $errors = null;
        $success = false;
        if (Tools::getValue('save_general')) {
            Configuration::updateValue(
                'MERCADOPAGO_CATEGORY',
                Tools::getValue('MERCADOPAGO_CATEGORY')
            );

            $country = UtilMercadoPago::getString(Configuration::get('MERCADOPAGO_COUNTRY'));

            if ($country != "") {
                $sponsorID = UtilMercadoPago::getString(Tools::getValue('SPONSOR_ID'));

                if ($sponsorID != "") {
                    $mp = $this->mercadopago;
                    $success = true;
                    $errors = array();
                    $userInfo = $mp->getUserInfo($sponsorID);
                    if (isset($userInfo['site_id']) &&
                        $userInfo['site_id'] == $country &&
                        $userInfo['status']['site_status'] == "active"
                    ) {
                        Configuration::updateValue('SPONSOR_ID', $sponsorID);
                        $this->setSponsorId($country);
                    } else {
                        $errors[] = $this->l('The Sponsor ID is invalid.');
                        $success = false;
                    }
                }
            }
        }

        if (Tools::getValue('login_standard')) {
            $settings = $this->getLoginStandardContent();
        } elseif (Tools::getValue('login_custom')) {
            $settings = $this->getLoginCustomContent();
        } elseif (Tools::getValue('submit_checkout_standard')) {
            $settings = $this->getCheckoutStandardContent();
        } elseif (Tools::getValue('submit_checkout_custom')) {
            $settings = $this->getCheckoutCustomContent();
        } else {
            $settings = $this->renderSettings();
        }
        if (!$success) {
            $this->context->smarty->assign($errors);
        }
        $settings["log"] = $this->_path . "/logs/mercadopago.log";

        $this->context->smarty->assign($settings);
        return $this->display(__file__, '/views/templates/admin/settings.tpl');
    }

    private function setDefaultValues($client_id, $client_secret, $country)
    {
        Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', 'false');
        Configuration::updateValue('MERCADOPAGO_CLIENT_ID', $client_id);
        Configuration::updateValue('MERCADOPAGO_CLIENT_SECRET', $client_secret);
        Configuration::updateValue('MERCADOPAGO_COUNTRY', $country);
        Configuration::updateValue('MERCADOPAGO_WINDOW_TYPE', 'redirect');
        Configuration::updateValue('MERCADOPAGO_IFRAME_WIDTH', '725');
        Configuration::updateValue('MERCADOPAGO_IFRAME_HEIGHT', '570');
        Configuration::updateValue('MERCADOPAGO_INSTALLMENTS', '12');
        Configuration::updateValue('MERCADOPAGO_AUTO_RETURN', 'approved');
        Configuration::updateValue('MERCADOPAGO_CUSTOM_TEXT', 'Pay via MercadoPago and split into up to 24 times');
        Configuration::updateValue(
            'MERCADOPAGO_STANDARD_BANNER',
            (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars(
                $_SERVER['HTTP_HOST'],
                ENT_COMPAT,
                'UTF-8'
            ) . __PS_BASE_URI__ . 'modules/mercadopago/views/img/' . $country . '/banner_all_methods.png'
        );
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

            $javascript = '<script>var carrier_id = [' . $lista_shipping . '];';

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

    private function validateCredential($client_id, $client_secret)
    {
        $mp = $this->mercadopago;
        $mp->setCredentialsStandard($client_id, $client_secret);
        $access_token = $mp->getAccessToken();
        $returnValidAccessToken = $mp->isValidAccessToken($access_token);

        return $returnValidAccessToken;
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

        $this->context->controller->addCss($this->_path . 'views/css/mercadopago_core.css', 'all');
        $this->context->controller->addCss($this->_path . 'views/css/chico.min.css', 'all');
        $this->context->controller->addCss($this->_path . 'views/css/dd.css', 'all');
        $this->context->controller->addCss($this->_path . 'views/css/mercadopago_v6.css', 'all');

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

    public function hookPaymentStandard($params)
    {
        return $this->display(__file__, '/views/templates/hook/checkoutStandard.tpl');
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        //calculo desconto parcela a vista
        $cart = $params['cart'];

        $credit_card_discount = (int) Configuration::get('MERCADOPAGO_ACTIVE_DISCOUNT_CREDITCARD');
        $shipping_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        $product_cost = (double) $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        $discount = 0;
        if (Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT') > 0) {
            $discount = ($percent / 100) * $product_cost;
        }

        $orderTotal = number_format(($product_cost - $discount) + $shipping_cost, 2, ',', '.');

        $this->context->smarty->assign(
            array('orderTotal' => $orderTotal, 'credit_card_discount' => $credit_card_discount)
        );
        
        $creditcard_disable = (Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE') == "") ? "false" : "true";
        $mercadoenvios_activate = Configuration::get('MERCADOENVIOS_ACTIVATE');
        $boleto_disable = Configuration::get('MERCADOPAGO_CUSTOM_BOLETO');
        $boleto_discount = (int) Configuration::get('MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO');

        if ($mercadoenvios_activate == 'true') {
            $creditcard_disable = 'true';
            $boleto_disable = 'true';
        } else {
            $mercadoenvios_activate = 'false';
        }

        if ($this->hasCredential()) {
            UtilMercadoPago::log("hasCredential", "hasCredential");
            $this_path_ssl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__;
            $data = array(
                'credit_card_discount' => $credit_card_discount,
                'boleto_discount' => $boleto_discount,

                'percent' => $percent,
                'this_path_ssl' => $this_path_ssl,
                'mercadoenvios_activate' => $mercadoenvios_activate,
                'boleto_disable' => $boleto_disable,
                'creditcard_disable' => $creditcard_disable,
                'coupon_active' => Configuration::get('MERCADOPAGO_COUPON_ACTIVE'),
                'coupon_ticket_active' => Configuration::get('MERCADOPAGO_COUPON_TICKET_ACTIVE'),

                'standard_active' => Configuration::get('MERCADOPAGO_STANDARD_ACTIVE'),
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
            if (Configuration::get('MERCADOPAGO_CUSTOM_ACTIVE') &&
                Configuration::get('MERCADOPAGO_PUBLIC_KEY') != "" &&
                Configuration::get('MERCADOPAGO_ACCESS_TOKEN') != "") {
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
            } else {
                $data['customerCards'] = null;
                $data['customerID'] = null;
            }

            // send standard configurations only activated
            if (Configuration::get('MERCADOPAGO_STANDARD_ACTIVE') == 'true') {
                $data['custom_text'] = Configuration::get('MERCADOPAGO_CUSTOM_TEXT');
                $data['standard_banner'] = Configuration::get('MERCADOPAGO_STANDARD_BANNER');
                if (Configuration::get('MERCADOPAGO_WINDOW_TYPE') == 'iframe') {
                    $result = $this->createStandardCheckoutPreference();
                    if (array_key_exists('init_point', $result['response'])) {
                        $data['preferences_url'] = $result['response']['init_point'];
                    } else {
                        UtilMercadoPago::logMensagem(
                            'Occured a error during the process the create standard preferences, id cart' . $cart->id,
                            MPApi::ERROR,
                            $result['message'],
                            true,
                            null,
                            'MercadoPago->hookPayment'
                        );
                    }
                } else {
                    $data['standard_action_url'] = $this->link->getModuleLink(
                        'mercadopago',
                        'standardpayment',
                        array(),
                        Configuration::get('PS_SSL_ENABLED'),
                        null,
                        null,
                        false
                    );
                }
            }
            // send offline settings
            $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods();
            $offline_payment_settings = array();
            foreach ($offline_methods_payments as $offline_payment) {
                $op_banner_variable = 'MERCADOPAGO_' . Tools::strtoupper($offline_payment['id'] . '_BANNER');
                $op_active_variable = 'MERCADOPAGO_' . Tools::strtoupper($offline_payment['id'] . '_ACTIVE');

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
            if ($boleto_disable == "false" && Configuration::get('MERCADOPAGO_COUNTRY') == 'MLB') {
                $data['ticket'] = $this->getInfomationsForTicket($cart->id_address_invoice);
            }

            if ((Configuration::get('MERCADOPAGO_COUNTRY') == 'MLM' ||
                Configuration::get('MERCADOPAGO_COUNTRY') == 'MPE') &&
                $mercadoenvios_activate == 'false'
            ) {
                $payment_methods_credit = $this->mercadopago->getPaymentCreditsMLM();
                $data['payment_methods_credit'] = $payment_methods_credit;
            } else {
                $data['payment_methods_credit'] = array();
            }

            $pageReturn = '/views/templates/hook/checkout.tpl';
            $this->context->smarty->assign($data);
            return $this->display(__file__, $pageReturn);
        } else {
            UtilMercadoPago::logMensagem(
                'OCORREU UM ERRO DURANTE A INSTALAção, credenciais não foram carregadas' . $cart->id,
                MPApi::ERROR,
                $result['message'],
                true,
                null,
                'MercadoPago->hookPayment'
            );
        }
    }

    private function setPreModuleAnalytics()
    {
        $customer_fields = Context::getContext()->customer->getFields();

        $select = 'SELECT name FROM ' . _DB_PREFIX_ . 'module where active = 1 AND id_module IN (
            SELECT h.id_module
            FROM ' . _DB_PREFIX_ . 'hook_module h INNER JOIN ' . _DB_PREFIX_ . 'hook ph on ph.id_hook = h.id_hook
            WHERE ph.name = "displayPayment"
            )';
        $query = Db::getInstance()->executeS($select);

        $resultModules = array();

        foreach ($query as $result) {
            array_push($resultModules, $result['name']);
        }

        $return = array(
            'publicKey' => Configuration::get('MERCADOPAGO_PUBLIC_KEY') ?
            Configuration::get('MERCADOPAGO_PUBLIC_KEY') : "",
            'token' => Configuration::get('MERCADOPAGO_ACCESS_TOKEN'),
            'platform' => "PRESTASHOP",
            'platformVersion' => $this->getPrestashopVersion(),
            'moduleVersion' => $this->version,
            'payerEmail' => $customer_fields['email'],
            'userLogged' => $this->context->customer->isLogged() ? 1 : 0,
            'installedModules' => implode(', ', $resultModules),
            'additionalInfo' => "",
        );
        return $return;
    }

    /**
     * @param
     *            $params
     */
    public function hookPaymentReturn($params)
    {
        UtilMercadoPago::log("hookPaymentReturn", "1");
        if (!$this->active) {
            return;
        }
        if (Tools::getValue('payment_method_id') == 'bolbradesco' ||
            Tools::getValue('payment_type') == 'bank_transfer' ||
            Tools::getValue('payment_type') == 'atm' || Tools::getValue('payment_type') == 'ticket') {
            $boleto_url = Tools::getValue('boleto_url');
            if (Configuration::get('PS_SSL_ENABLED')) {
                $boleto_url = str_replace("http", "https", $boleto_url);
            }
            $this->context->smarty->assign(
                array(
                    'payment_id' => Tools::getValue('payment_id'),
                    'boleto_url' => Tools::getValue('boleto_url'),
                    'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') .
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
                )
            );

            return $this->display(__file__, '/views/templates/hook/boleto_payment_return.tpl');
        } else {
            $this->context->controller->addCss($this->_path . 'views/css/mercadopago_core.css', 'all');
            $this->context->smarty->assign(
                array(
                    'payment_status' => Tools::getValue('payment_status'),
                    'status_detail' => Tools::getValue('status_detail'),
                    'card_holder_name' => Tools::getValue('card_holder_name'),
                    'four_digits' => Tools::getValue('four_digits'),
                    'payment_method_id' => $this->setNamePaymentType(Tools::getValue('payment_type')),
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
                    ) . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__,
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
        return (Configuration::get('MERCADOPAGO_CLIENT_ID') != '' &&
            Configuration::get('MERCADOPAGO_CLIENT_SECRET') != '') ||
            (Configuration::get('MERCADOPAGO_ACCESS_TOKEN') != '' &&
            Configuration::get('MERCADOPAGO_PUBLIC_KEY') != '');
    }

    /**
     * @param
     *            $post
     */
    public function execPayment($post)
    {
        $preferences = $this->getPreferencesCustom($post);
        try {
            UtilMercadoPago::logMensagem(
                "Json preferences custom ",
                MPApi::INFO,
                Tools::jsonEncode($preferences),
                false,
                null,
                "mercadopago->execPayment"
            );
            $result = $this->mercadopago->createCustomPayment($preferences);
            UtilMercadoPago::logMensagem(
                "Json result custom ",
                MPApi::INFO,
                Tools::jsonEncode($result),
                false,
                null,
                "mercadopago->execPayment"
            );
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                'Occured a error during the process custom payment.',
                MPApi::WARNING,
                $e->getMessage(),
                true,
                null,
                'MercadoPago->execPayment'
            );
        }
        return $result['response'];
    }

    public function getViewInfomationsTicket($post)
    {
        $email = $post['email'];
        $firstname = $post['firstname'];
        $cpf = $post['cpfcnpj'];
        $lastname = $post['lastname'];
        $address = $post['address'];
        $number = $post['number'];
        $city = $post['city'];
        $postcode = UtilMercadoPago::getCodigoPostal($post['postcode']);
        $state = $post['state'];
        $retorno = array();

        $retorno['first_name'] = $firstname;
        $retorno['last_name'] = $lastname;
        $retorno['email'] = $email;

        $retorno['identification']['type'] = $post['typeDocument'];

        $retorno['identification']['number'] = $cpf;

        $retorno['address']['zip_code'] = $postcode;
        $retorno['address']['street_name'] = $address;
        $retorno['address']['street_number'] = (int) $number;
        $retorno['address']['neighborhood'] = $state;
        $retorno['address']['city'] = $city;
        $retorno['address']['federal_unit'] = $state;

        return $retorno;
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
        $mp = $this->mercadopago;

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
        $customer_fields = null;
        try {
            $customer_fields = Context::getContext()->customer->getFields();
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "FATAL ERROR, getFields",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->getPreferencesCustom"
            );
        }

        $cart = Context::getContext()->cart;

        // items
        $products = $cart->getProducts();
        $items = array();
        $summary = '';
        $round = false;
        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO' || Configuration::get('MERCADOPAGO_COUNTRY') == 'MLC') {
            $round = true;
        }

        foreach ($products as $key => $product) {
            $image = Image::getCover($product['id_product']);
            $product_image = new Product($product['id_product'], false, Context::getContext()->language->id);
            $link = new Link(); //because getImageLInk is not static function
            $imagePath = $link->getImageLink(
                $product_image->link_rewrite,
                $image['id_image'],
                ImageType::getFormatedName('home')
            );

            $item = array(
                'id' => $product['id_product'],
                'title' => $product['name'],
                'description' => $product['description_short'],
                'picture_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $imagePath,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'quantity' => $product['quantity'],
                'unit_price' => $round ? (int) Tools::ps_round($product['price_wt'], 0) : (float) $product['price_wt'],
            );

            if ($key == 0) {
                $summary .= $product['name'];
            } else {
                $summary .= ', ' . $product['name'];
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
                'unit_price' => $round ? (int) Tools::ps_round($shipping_cost, 0) : (float) $shipping_cost,
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
                'unit_price' => $round ? (int) Tools::ps_round($wrapping_cost, 0) : (float) $wrapping_cost,
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
                'unit_price' => $round ? (int) Tools::ps_round(-$discounts, 0) : (float)  - $discounts,
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
                'zip_code' => UtilMercadoPago::getCodigoPostal($address_invoice->postcode),
                'street_name' => $address_invoice->address1 . ' - ' . $address_invoice->address2 . ' - ' .
                $address_invoice->city . '/' . $address_invoice->country,
                'street_number' => '-',
            ),
        );
        // Get shipment address for additional_info
        $address_delivery = new Address((integer) $cart->id_address_delivery);
        $shipments = array(
            'receiver_address' => array(
                'zip_code' => UtilMercadoPago::getCodigoPostal($address_delivery->postcode),
                'street_name' => $address_delivery->address1 . ' - ' . $address_delivery->address2 . ' - ' .
                $address_delivery->city . '/' . $address_delivery->country,
                'street_number' => '-',
                'floor' => '-',
                'apartment' => '-',
            ),
        );

        $notification_url = $this->context->link->getModuleLink(
            'mercadopago',
            'notification',
            array('checkout' => 'custom', 'cart_id' => $cart->id, 'notification' => "ipn"),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );

        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        if (count($percent) > 0) {
            //aplicar desconto parcela a vista
            $installments = 1;
            $payment_mode = 'boleto';
            if (isset($post['opcaoPagamentoCreditCard']) && 'Customer' == $post['opcaoPagamentoCreditCard']) {
                $installments = (integer) $post['installmentsCust'];
            }

            if (isset($post['card_token_id'])) {
                $payment_mode = 'cartao';
            }
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
            $callback_url = $this->link->getModuleLink(
                'mercadopago',
                'standardreturn',
                array(),
                Configuration::get('PS_SSL_ENABLED'),
                null,
                null,
                false
            );

            $payment_preference['callback_url'] = $callback_url;

            $payment_preference['transaction_details']['financial_institution'] = "1234";
            $payment_preference['additional_info']['ip_address'] = "127.0.0.1";

            $payment_preference['payer']['identification']['type'] = "RUT";
            $payment_preference['payer']['identification']['number'] = "0";
            $payment_preference['payer']['entity_type'] = "individual";
        }

        if ($post['payment_method_id'] == 'bolbradesco') {
            $payment_preference['payer'] = $this->getViewInfomationsTicket($post);
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
            $payment_preference['notification_url'] = $notification_url;
            UtilMercadoPago::log("===notification_url custom==", $notification_url);
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
                $this->context->smarty->assign(
                    array(
                        'message_error' => $coupon['response']['error'],
                        'version' => $this->getPrestashopVersion(),
                    )
                );

                return $this->display(__file__, '/views/templates/front/error_admin.tpl');
            }
        }

        if (Configuration::get('MERCADOPAGO_USER_TEST') == "false") {
            $payment_preference['sponsor_id'] = (int) Configuration::get('MERCADOPAGO_SPONSOR_ID');
        }
        if (Configuration::get('MERCADOPAGO_BINARY_MODE') == "true") {
            $payment_preference['binary_mode'] = 'true';
        }

        $payment_preference['statement_descriptor'] = 'MERCADOPAGO - ' . Configuration::get('PS_SHOP_NAME');

        return $payment_preference;
    }

    private function getPrestashopPreferencesStandard()
    {
        try {
            $customer_fields = Context::getContext()->customer->getFields();
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "FATAL ERROR, getFields",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->getPrestashopPreferencesStandard"
            );
            return;
        }

        $cart = Context::getContext()->cart;

        $currency = new Currency((int) $cart->id_currency);

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
                'zip_code' => UtilMercadoPago::getCodigoPostal($address_invoice->postcode),
                'street_name' => $address_invoice->address1 . ' - ' . $address_invoice->address2 . ' - ' .
                $address_invoice->city . '/' . $address_invoice->country,
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
        $round = false;

        if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO' || Configuration::get('MERCADOPAGO_COUNTRY') == 'MLC') {
            $round = true;
        }

        foreach ($products as $key => $product) {
            $image = Image::getCover($product['id_product']);
            $product_image = new Product($product['id_product'], false, Context::getContext()->language->id);
            $link = new Link(); //because getImageLInk is not static function
            $imagePath = $link->getImageLink(
                $product_image->link_rewrite,
                $image['id_image'],
                ImageType::getFormatedName('home')
            );

            $unit_price = 0;
            $feeMP = (float) str_replace(",", ".", Configuration::get('MERCADOPAGO_PERCENT_EXTRA'));

            if ($feeMP > 0) {
                $feeMP = $feeMP / 100.0;
                $unit_price = Tools::ps_round($product['price_wt'] + ($feeMP * $product['price_wt']), 2);
            } else {
                $unit_price = $product['price_wt'];
            }

            $item = array(
                'id' => $product['id_product'],
                'title' => $product['name'],
                'description' => $product['description_short'],
                'quantity' => $product['quantity'],
                'unit_price' => $round ? (int) Tools::ps_round($unit_price, 0) : (float) $unit_price,
                'picture_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $imagePath,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'currency_id' => $currency->iso_code,
            );
            if ($key == 0) {
                $summary .= $product['name'];
            } else {
                $summary .= ', ' . $product['name'];
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
                'unit_price' => $round ? round($wrapping_cost) : $wrapping_cost,
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'currency_id' => $currency->iso_code,
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
                'unit_price' => -($round ? floor($discounts) : $discounts),
                'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                'currency_id' => $currency->iso_code,
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
                'zip_code' => UtilMercadoPago::getCodigoPostal($address_invoice->postcode),
                'default_shipping_method' => $id_mercadoenvios_service_code,
                'dimensions' =>
                "{$dimensions['width']}x{$dimensions['height']}x" .
                "{$dimensions['depth']},{$dimensions['weight']}",
                'receiver_address' => array(
                    'floor' => '-',
                    'zip_code' => UtilMercadoPago::getCodigoPostal($address_delivery->postcode),
                    'street_name' => $address_delivery->address1 . ' - ' . $address_delivery->address2 . ' - ' .
                    $address_delivery->city . '/' . $address_delivery->country,
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
                    'unit_price' => $round ? round($shipping_cost) : $shipping_cost,
                    'category_id' => Configuration::get('MERCADOPAGO_CATEGORY'),
                    'currency_id' => $currency->iso_code,
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

        if (Configuration::get('MERCADOPAGO_USER_TEST') == "false") {
            $data['sponsor_id'] = (int) Configuration::get('MERCADOPAGO_SPONSOR_ID');
        }

        $data['auto_return'] = Configuration::get('MERCADOPAGO_AUTO_RETURN') == 'approved' ? 'approved' : '';
        $data['back_urls']['success'] = $this->link->getModuleLink(
            'mercadopago',
            'standardreturn',
            array('typeReturn' => 'success', 'cart_id' => $cart->id),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        $data['back_urls']['failure'] = $this->link->getModuleLink(
            'mercadopago',
            'standardreturn',
            array('typeReturn' => 'failure', 'cart_id' => $cart->id),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        $data['back_urls']['pending'] = $this->link->getModuleLink(
            'mercadopago',
            'standardreturn',
            array('typeReturn' => 'pending', 'cart_id' => $cart->id),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        $data['payment_methods']['excluded_payment_methods'] = $this->getExcludedPaymentMethods();
        $data['payment_methods']['excluded_payment_types'] = array();
        $data['payment_methods']['installments'] = (integer) Configuration::get('MERCADOPAGO_INSTALLMENTS');

        $notification_url = $this->context->link->getModuleLink(
            'mercadopago',
            'notification',
            array('checkout' => 'standard', 'cart_id' => $cart->id, 'notification' => "ipn"),
            Configuration::get('PS_SSL_ENABLED'),
            null,
            null,
            false
        );
        if (!strrpos($notification_url, 'localhost')) {
            $data['notification_url'] = $notification_url;
            UtilMercadoPago::log("===notification_url==", $data['notification_url']);
        }

        // swap to payer index since customer is only for transparent
        $data['customer']['name'] = $data['customer']['first_name'];
        $data['customer']['surname'] = $data['customer']['last_name'];
        $data['payer'] = $data['customer'];
        unset($data['customer']);

        return $data;
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
                $sort_dim = array($width, $height, $depth);
                sort($sort_dim);
                $depth = $sort_dim[0];
                $height = $sort_dim[1];
                $width = $sort_dim[2];
            }
            $weight += ($p['weight2'] > 0.1 ? $p['weight2'] : Configuration::get('default_weight')) * $p['quantity'];
        }

        $config_shipment = MercadoPago::$countryOptions[Configuration::get('MERCADOPAGO_COUNTRY')];

        $width = max($width, $config_shipment['MP_SHIPPING_MIN_W']);
        $height = max($height, $config_shipment['MP_SHIPPING_MIN_H']);
        $depth = max($depth, $config_shipment['MP_SHIPPING_MIN_D']);
        $weight = max($weight, $config_shipment['MP_SHIPPING_MIN_WE']);
        return array(
            'width' => (int) Tools::ps_round($width, 0), // > 0.01 ? $width : $this->config['default_width'], 0),
            'height' => (int) Tools::ps_round($height, 0), // > 0.01 ? $height : $this->config['default_height'], 0),
            'depth' => (int) Tools::ps_round($depth, 0), // > 0.01 ? $depth : $this->config['default_depth'], 0),
            'weight' => (int) Tools::ps_round($weight, 0), // > 0.1 ? $weight : $this->config['default_weight'], 0),
        );
    }

    public function createStandardCheckoutPreference()
    {
        $preferences = $this->getPrestashopPreferencesStandard(null);

        return $this->mercadopago->createPreference($preferences);
    }

    private function getExcludedPaymentMethods()
    {
        $payment_methods = $this->mercadopago->getPaymentMethods();
        $excluded_payment_methods = array();

        if ($payment_methods != null) {
            foreach ($payment_methods as $payment_method) {
                $pm_variable_name = 'MERCADOPAGO_' . Tools::strtoupper($payment_method['id']);
                $value = Configuration::get($pm_variable_name);

                if ($value == 'on') {
                    $excluded_payment_methods[] = array(
                        'id' => $payment_method['id'],
                    );
                }
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
        if (isset($result['response']['metadata']) &&
            !empty($result['response']['metadata'])) {
            $token = $result['response']['metadata']['card_token_id'];
            $customerID = $result['response']['metadata']['customer_id'];

            $tokenPagamentoJson = array(
                'token' => $token,
            );
            $result_response = $this->mercadopago->addCustomerCard($tokenPagamentoJson, $customerID);
            return $result_response;
        }
        return null;
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
        $isMercadoEnvios = 0;
        if ($checkout == 'standard' && $topic == 'merchant_order' && $id > 0) {
            $result = $this->mercadopago->getMerchantOrder($id);
            UtilMercadoPago::log("====id merchant order====", $id);
            UtilMercadoPago::log("====result merchantOrder====", Tools::jsonEncode($result));
            $merchant_order_info = $result['response'];
            // check value
            $cart = new Cart($merchant_order_info['external_reference']);

            $payments = $merchant_order_info['payments'];
            $external_reference = $merchant_order_info['external_reference'];
            foreach ($payments as $payment) {
                // get payment info
                $result = $this->mercadopago->getPayment($payment['id'], "standard");
                UtilMercadoPago::log("====result payment====", Tools::jsonEncode($result));
                $payment_info = $result['response'];

                // colect payment details
                $payment_ids[] = $payment_info['id'];
                $payment_statuses[] = $payment_info['status'];
                $payment_types[] = $payment_info['payment_type_id'];
                $transaction_amounts += $payment_info['transaction_amount'];
                if ($payment_info['payment_type_id'] == 'credit_card') {
                    $payment_method_ids[] = isset($payment_info['payment_method_id']) ?
                    $payment_info['payment_method_id'] : '';
                    $credit_cards[] = isset($payment_info['card']['last_four_digits']) ?
                    '**** **** **** ' . $payment_info['card']['last_four_digits'] : '';
                    $cardholders[] = isset($payment_info['card']['cardholder']['name']) ?
                    $payment_info['card']['cardholder']['name'] : '';
                }
            }
            if (round($transaction_amounts, 2) >= round($merchant_order_info['total_amount'], 2)) {
                if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO' ||
                    Configuration::get('MERCADOPAGO_COUNTRY') == 'MLC') {
                    $transaction_amounts = $cart->getOrderTotal(true, Cart::BOTH);
                }
                if ($isMercadoEnvios ||
                    (isset($merchant_order_info['shipments']) &&
                        isset($merchant_order_info['shipments'][0]) &&
                        $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2')
                ) {
                    $transaction_amounts += $merchant_order_info['shipments'][0]['shipping_option']['cost'];
                }
                $this->updateOrder(
                    $payment_ids,
                    $payment_statuses,
                    $payment_types,
                    $external_reference,
                    $result,
                    $checkout
                );
            }
            // check the module
            $id_order = $this->getOrderByCartId($merchant_order_info['external_reference']);
            $order = new Order($id_order);
            $status_shipment = null;
            if (isset($merchant_order_info['shipments'][0]) &&
                $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2' &&
                ($merchant_order_info['shipments'][0]['status'] == "ready_to_ship" ||
                    $merchant_order_info['shipments'][0]['status'] == "shipped" ||
                    $merchant_order_info['shipments'][0]['status'] == "delivered")
            ) {
                $isMercadoEnvios = true;
                $status_shipment = $merchant_order_info['shipments'][0]['status'];

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
                $payment_status_check = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta[$status_shipment]);
                if ($order_status != null) {
                    $existStates = $this->checkStateExist($id_order, $payment_status_check);
                    if ($existStates) {
                        return;
                    }
                    $this->updateOrderHistory($order->id, Configuration::get($order_status));
                }
            }
        } elseif (($checkout == 'custom' || $checkout == 'pos') && $topic == 'payment' && $id > 0) {
            $result = $this->mercadopago->getPayment($id, "custom");
            $payment_info = $result['response'];
            if (!isset($result['error'])) {
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
                    $credit_cards[] = '**** **** **** ' . $payment_info['card']['last_four_digits'];
                    $cardholders[] = $payment_info['card']['cardholder']['name'];
                }
                $this->updateOrder(
                    $payment_ids,
                    $payment_statuses,
                    $payment_types,
                    $external_reference,
                    $result,
                    $checkout
                );
            }
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
        FROM ' . _DB_PREFIX_ . 'order_history
        WHERE `id_order` = ' . (int) $id_order . '
        AND `id_order_state` = ' .
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
        FROM ' . _DB_PREFIX_ . 'order_history
        WHERE `id_order` = ' . (int) $id_order . '
        AND `id_order_state` = ' .
            (int) $id_order_state
        );
    }

    private function updateOrder(
        $payment_ids,
        $payment_statuses,
        $payment_types,
        $external_reference,
        $result,
        $checkout
    ) {
        $order = null;
        // if has two creditcard validate whether payment has same status in order to continue validating order
        if (count($payment_statuses) == 1 ||
            (count($payment_statuses) == 2 &&
                $payment_statuses[0] == $payment_statuses[1])
        ) {
            $order = null;
            $payment_status = $payment_statuses[0];
            $payment_type = $payment_types[0];

            if ($payment_type == 'credit_card' &&
                $payment_status == 'approved') {
                $this->saveCard($result);
            }
            // just change if there is an order status
            $id_cart = $external_reference;
            $id_order = $this->getOrderByCartId($id_cart);
            $order = new Order($id_order);
            if ($id_order) {
                $payment_status_check = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta[$payment_status]);
                if ($this->checkStateExist($id_order, $payment_status_check)) {
                    return;
                }
            }
            if ($payment_status == 'cancelled' || $payment_status == 'rejected') {
                if ($order->module == "mercadopago" || $checkout == 'pos') {
                    $retorno = $this->getOrderStateApproved($id_order);
                    if ($retorno) {
                        return;
                    }
                } else {
                    return;
                }
            }
            $statusPS = (int) $order->getCurrentState();
            $payment_status = Configuration::get(UtilMercadoPago::$statusMercadoPagoPresta[$payment_status]);
            if ($payment_status != $statusPS) {
                $order->setCurrentState($payment_status);
            }
            try {
                $payments = $order->getOrderPaymentCollection();
                $payments[0]->transaction_id = implode(' / ', $payment_ids);
                $payments[0]->update();
            } catch (Exception $e) {
                UtilMercadoPago::logMensagem(
                    'Occured a error during the process the update order, payments is null = ' . $id_cart,
                    MPApi::ERROR,
                    $e->getMessage(),
                    true,
                    null,
                    'MercadoPago->updateOrder'
                );
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

                if (isset($customer['response']['status']) && $customer['response']['status'] == 200) {
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
        $country = $this->mercadopago->getCountry();
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

    public function hookDisplayBeforeCarrier($params)
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
        $retornoCalculadora = $this->calculateListCache(UtilMercadoPago::getCodigoPostal($address->postcode));

        $mpCarrier = $lista_shipping['MP_SHIPPING'];

        foreach ($delivery_option_list->value as $id_address) {
            foreach ($id_address as $key) {
                foreach ($key['carrier_list'] as $id_carrier) {
                    if (in_array($id_carrier['instance']->id, $mpCarrier)) {
                        if (isset($lista_shipping['MP_CARRIER'][(int) $id_carrier['instance']->id])) {
                            $id_mercadoenvios_service_code = $lista_shipping['MP_CARRIER'][$id_carrier['instance']->id];
                            $calculadora = $retornoCalculadora[(string) $id_mercadoenvios_service_code];
                            $msg = $calculadora['estimated_delivery'] . ' ' . $this->l('working days.');
                            $id_carrier['instance']->delay[$this->context->cart->id_lang] =
                            $this->l('After the post, receive the product ') . $msg;
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

        $chave = $external_reference .
            '|' .
            $postcode . '' .
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
        try {
            $products = $cart->getProducts();
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "There is a problem with calculate list. " . $cart->id,
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "calculateList"
            );
        }

        $price_total = 0;

        $mp = $this->mercadopago;

        $dimensions = $this->getDimensions($products);

        $return = array();
        $paramsMP = array(
            'dimensions' => "{$dimensions['width']}x{$dimensions['height']}x" .
            "{$dimensions['depth']},{$dimensions['weight']}",
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
    //API_LOGS
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
        $postcode = UtilMercadoPago::getCodigoPostal($address->postcode);

        $external_reference = $cart->id;

        $chave = $external_reference .
            '|' .
            $id_carrier . '' .
            $postcode . '' .
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
                $width += $product['width'];
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
            PrestaShopLogger::addLog("=====calculate=====" . $error, MPApi::ERROR, 0);
            // throw new Exception($error);
        }

        $dimensions = $height . 'x' . $width . 'x' . $length . ',' . $weight;

        $postcode = UtilMercadoPago::getCodigoPostal($address->postcode);

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
                    _DB_PREFIX_ . 'carrier_group',
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
                    _DB_PREFIX_ . 'carrier_zone',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_zone' => (int) ($zone['id_zone']),
                    ),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_ . 'delivery',
                    array('id_carrier' => (int) ($carrier->id),
                        'id_range_price' => (int) ($rangePrice->id),
                        'id_range_weight' => null,
                        'id_zone' => (int) ($zone['id_zone']),
                        'price' => '0',
                    ),
                    'INSERT'
                );
                Db::getInstance()->autoExecuteWithNullValues(
                    _DB_PREFIX_ . 'delivery',
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
            @copy(dirname(__FILE__) . '/views/img/carrier.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');

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
            FROM `' . _DB_PREFIX_ . 'orders`
            WHERE `id_cart` = ' . (int) $id_cart
        . Shop::addSqlRestriction() . ' order by id_order desc';
        $result = Db::getInstance()->getRow($sql);

        return isset($result['id_order']) ? $result['id_order'] : false;
    }

    public function applyDiscount($cart, $payment_mode, $installments = 1)
    {
        $percent = (float) Configuration::get('MERCADOPAGO_DISCOUNT_PERCENT');

        $credit_card = (int) Configuration::get('MERCADOPAGO_ACTIVE_DISCOUNT_CREDITCARD');
        $boleto = (int) Configuration::get('MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO');

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
        Configuration::updateValue('MERCADOPAGO_ACTIVE_DISCOUNT_CREDITCARD', false);
        Configuration::updateValue('MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO', false);

        Configuration::updateValue('MERCADOPAGO_CREDITCARD_ACTIVE', false);
        Configuration::updateValue('MERCADOPAGO_CUSTOM_BOLETO', false);
        Configuration::updateValue('MERCADOPAGO_PEC_ACTIVE', false);
        Configuration::updateValue('MERCADOPAGO_CART_CALCULATE', false);
        Configuration::updateValue('MERCADOPAGO_PRODUCT_CALCULATE', false);
        Configuration::updateValue('MERCADOPAGO_STANDARD_ACTIVE', false);
        Configuration::updateValue('MERCADOPAGO_CUSTOM_ACTIVE', false);

        Configuration::updateValue('MERCADOPAGO_TWO_CARDS', false);
        Configuration::updateValue('MERCADOPAGO_COUPON_ACTIVE', false);
        Configuration::updateValue('MERCADOPAGO_POINT', false);
        Configuration::updateValue('MERCADOENVIOS_ACTIVATE', false);
        Configuration::updateValue('MERCADOPAGO_USER_TEST', false);
        Configuration::updateValue('MERCADOPAGO_SPONSOR_ID', "");
        Configuration::updateValue('MERCADOPAGO_CUSTOM_TEXT', "");
    }

    public function setSettings()
    {
        if ($this->hasCredential()) {
            $mp = $this->mercadopago;

            $request = array(
                "platform_version" => _PS_VERSION_,
                "two_cards" => UtilMercadoPago::checkValueNull(
                    Configuration::get('MERCADOPAGO_TWO_CARDS')
                ),
                "mercado_envios" => UtilMercadoPago::checkValueNull(
                    Configuration::get('MERCADOENVIOS_ACTIVATE')
                ),
                "checkout_custom_ticket" => UtilMercadoPago::checkValueNull(
                    Configuration::get('MERCADOPAGO_CUSTOM_BOLETO')
                ),
                "checkout_custom_credit_card_coupon" => UtilMercadoPago::checkValueNull(
                    Configuration::get('MERCADOPAGO_COUPON_ACTIVE')
                ),
                "checkout_basic" => UtilMercadoPago::checkValueNull(
                    Configuration::get('MERCADOPAGO_STANDARD_ACTIVE')
                ),
                "checkout_custom_credit_card" => UtilMercadoPago::checkValueNull(
                    Configuration::get('MERCADOPAGO_CREDITCARD_ACTIVE')
                ),
                "code_version" => phpversion(),
                "module_version" => $this->version,
                "platform" => "PrestaShop",
            );

            try {
                $mp->saveSettings($request);
            } catch (Exception $e) {
                UtilMercadoPago::logMensagem(
                    "FATAL ERROR, save settings",
                    MPApi::ERROR,
                    $e->getMessage(),
                    true,
                    null,
                    "standard->initContent"
                );
            }
        }
    }

    public function selectMercadoPagoOrder($cart_id)
    {
        $sql = 'SELECT MAX(mercadopago_orders_id) as mercadopago_orders_id FROM `' .
        _DB_PREFIX_ . 'mercadopago_orders` WHERE `cart_id` = ' . (int) $cart_id;

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                $sql
            );
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "FATAL ERROR, error on selectMercadoPagoOrder. ",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->selectMercadoPagoOrder"
            );
        }
        return isset($result['mercadopago_orders_id']) ? $result['mercadopago_orders_id'] : false;
    }

    public function insertMercadoPagoOrder($cart_id, $order_id, $valid, $ipn_status)
    {
        $insertOrder = 'INSERT INTO ' .
        _DB_PREFIX_ . 'mercadopago_orders (cart_id, order_id, added, valid, ipn_status) VALUES(' .
        $cart_id . ',' . $order_id . ',\'' . pSql(date('Y-m-d h:i:s')) . '\',' . $valid . ',\'' . $ipn_status . '\')';
        try {
            $returnInsert = Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($insertOrder);
        } catch (Exception $e) {
            UtilMercadoPago::logMensagem(
                "FATAL ERROR, error on insert mercadopago_orders. ",
                MPApi::ERROR,
                $e->getMessage(),
                true,
                null,
                "mercadopago->insertMercadoPagoOrder"
            );
        }
        return $returnInsert;
    }

    public function setNamePaymentType($payment_type_id)
    {
        if ($payment_type_id == "ticket") {
            $displayName = $this->l('Mercado Pago - Ticket');
        } elseif ($payment_type_id == "atm") {
            $displayName = $this->l('Mercado Pago - ATM');
        } elseif ($payment_type_id == "credit_card") {
            $displayName = $this->l('Mercado Pago - Credit Card');
        } elseif ($payment_type_id == "debit_card") {
            $displayName = $this->l('Mercado Pago - Debit Card');
        } elseif ($payment_type_id == "prepaid_card") {
            $displayName = $this->l('Mercado Pago - Prepaid Card');
        } elseif ($payment_type_id == "account_money") {
            $displayName = $this->l('Mercado Pago - Account Money');
        } else {
            $displayName = $this->l('Mercado Pago');
        }
        return $displayName;
    }

    public function installOverrideMercadoEnvios()
    {
        @copy(
            dirname(__FILE__) . '/override/classes/Hook_Envios.php',
            dirname(__FILE__) . '/override/classes/Hook.php'
        );

        @copy(
            dirname(__FILE__) . '/override/classes/Hook.php',
            dirname(__FILE__) . '/../../override/classes/Hook.php'
        );
        try {
            return $this->installOverrides();
        } catch (Exception $e) {
            $this->uninstallOverrides();
            return false;
        }
    }

    public function uninstallOverrideMercadoEnvios()
    {
        //$mp->checkOverride();
        if (Configuration::get('MERCADOENVIOS_ACTIVATE') == 'true') {
            if (file_exists(dirname(__FILE__) . '/override/classes/Hook.php')) {
                unlink(dirname(__FILE__) . '/override/classes/Hook.php');
            }
            if (file_exists(dirname(__FILE__) . '/../../override/classes/Hook.php')) {
                unlink(dirname(__FILE__) . '/../../override/classes/Hook.php');
            }
        }
        try {
            $this->uninstallOverrides();
        } catch (Exception $e) {
            //Se ignora...
        }
    }

    public function getSourceModule()
    {
        return dirname(__FILE__);
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
