<?php
/**
 * 2007-2015 PrestaShop
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
 *  @author    ricardobrito
 *  @copyright Copyright(c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License(OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */
if (! defined ( '_PS_VERSION_' ))
    exit ();

function_exists ( 'curl_init' );
include (dirname ( __FILE__ ) . '/includes/mercadopago.php');
class MercadoPago extends PaymentModule {
    public function __construct() {
        $this->name = 'mercadopago';
        $this->tab = 'payments_gateways';
        $this->version = '3.1.1';
        $this->currencies = true;
        $this->currencies_mode = 'radio';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array (
                'min' => '1.5',
                'max' => '1.6' 
        );
        
        parent::__construct ();
        
        $this->page = basename ( __file__, '.php' );
        $this->displayName = $this->l ( 'MercadoPago' );
        $this->description = $this->l ( 'Receive payments via MercadoPago of credit cards and tickets using our custom checkout or standard checkout' );
        $this->confirmUninstall = $this->l ( 'Are you sure you want to uninstall MercadoPago?' );
        $this->textshowemail = $this->l ( 'You must follow MercadoPago rules for purchase to be valid' );
        $this->author = $this->l ( 'MERCADOPAGO.COM REPRESENTAÇÕES LTDA.' );
        $this->link = new Link ();
        $this->mercadopago = new MP_SDK ( Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ), Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ) );
    }
    
    /**
     * Check if the state exist before create another one.
     *
     * @param integer $id_order_state
     *            State ID
     * @return boolean availability
     */
    public static function orderStateAvailable($id_order_state) {
        $result = Db::getInstance ( _PS_USE_SQL_SLAVE_ )->getRow ( '
			SELECT `id_order_state` AS ok
			FROM `' . _DB_PREFIX_ . 'order_state`
			WHERE `id_order_state` = ' . ( int ) $id_order_state );
        return $result ['ok'];
    }
    
    /**
     * Create the states, we need to check if doens`t exists.
     */
    public function createStates() {
        $order_states = array (
                array (
                        '#ccfbff',
                        $this->l ( 'Transaction in Process' ),
                        'in_process',
                        '010010000' 
                ),
                array (
                        '#c9fecd',
                        $this->l ( 'Transaction Finished' ),
                        'payment',
                        '110010010' 
                ),
                array (
                        '#fec9c9',
                        $this->l ( 'Transaction Cancelled' ),
                        'order_canceled',
                        '010010000' 
                ),
                array (
                        '#fec9c9',
                        $this->l ( 'Transaction Rejected' ),
                        'payment_error',
                        '010010000' 
                ),
                array (
                        '#ffeddb',
                        $this->l ( 'Transaction Refunded' ),
                        'refund',
                        '110010000' 
                ),
                array (
                        '#c28566',
                        $this->l ( 'Transaction Chargedback' ),
                        'charged_back',
                        '010010000' 
                ),
                array (
                        '#b280b2',
                        $this->l ( 'Transaction in Mediation' ),
                        'in_mediation',
                        '010010000' 
                ),
                array (
                        '#fffb96',
                        $this->l ( 'Transaction Pending' ),
                        'pending',
                        '010010000' 
                ) 
        );
        
        $languages = Language::getLanguages ();
        
        foreach ( $order_states as $key => $value ) {
            if (! is_null ( $this->orderStateAvailable ( Configuration::get ( 'MERCADOPAGO_STATUS_' . $key ) ) )) {
                continue;
            } else {
                $order_state = new OrderState ();
                $order_state->invoice = $value [3] [0];
                $order_state->send_email = $value [3] [1];
                $order_state->module_name = 'mercadopago';
                $order_state->color = $value [0];
                $order_state->unremovable = $value [3] [2];
                $order_state->hidden = $value [3] [3];
                $order_state->logable = $value [3] [4];
                $order_state->delivery = $value [3] [5];
                $order_state->shipped = $value [3] [6];
                $order_state->paid = $value [3] [7];
                $order_state->deleted = $value [3] [8];
                $order_state->name = array ();
                $order_state->template = array ();
                
                foreach ( Language::getLanguages ( false ) as $language ) {
                    $order_state->name [( int ) $language ['id_lang']] = $value [1];
                    $order_state->template [$language ['id_lang']] = $value [2];
                    
                    if ($value [2] == 'in_process' || $value [2] == 'pending' || $value [2] == 'charged_back' || $value [2] == 'in_mediation') {
                        $this->populateEmail ( $language ['iso_code'], $value [2], 'html' );
                        $this->populateEmail ( $language ['iso_code'], $value [2], 'txt' );
                    }
                }
                
                if (! $order_state->add ())
                    return false;
                
                $file = _PS_ROOT_DIR_ . '/img/os/' . ( int ) $order_state->id . '.gif';
                copy ( (dirname ( __file__ ) . '/views/img/mp_icon.gif'), $file );
                
                Configuration::updateValue ( 'MERCADOPAGO_STATUS_' . $key, $order_state->id );
            }
        }
        return true;
    }
    private function populateEmail($lang, $name, $extension) {
        if (! file_exists ( _PS_MAIL_DIR_ . $lang ))
            mkdir ( _PS_MAIL_DIR_ . $lang, 0777, true );
        
        $new_template = _PS_MAIL_DIR_ . $lang . '/' . $name . '.' . $extension;
        
        if (! file_exists ( $new_template )) {
            $template = dirname ( __file__ ) . '/mails/' . $name . '.' . $extension;
            copy ( $template, $new_template );
        }
    }
    private function deleteStates() {
        for($index = 0; $index <= 7; $index ++) {
            $order_state = new OrderState ( Configuration::get ( 'MERCADOPAGO_STATUS_' . $index ) );
            if (! $order_state->delete ())
                return false;
        }
        return true;
    }
    public function install() {
        if (! parent::install () || ! $this->createStates () || ! $this->registerHook ( 'payment' ) || ! $this->registerHook ( 'paymentReturn' ) || ! $this->registerHook ( 'displayHeader' ))
            
            return false;
        
        return true;
    }
    public function uninstall() {
        
        // continue the states
        if (! $this->uninstallPaymentSettings () || ! Configuration::deleteByName ( 'MERCADOPAGO_PUBLIC_KEY' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_CLIENT_ID' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_CLIENT_SECRET' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_CATEGORY' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_CREDITCARD_BANNER' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_CREDITCARD_ACTIVE' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_STANDARD_ACTIVE' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_LOG' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_STANDARD_BANNER' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_WINDOW_TYPE' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_IFRAME_WIDTH' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_IFRAME_HEIGHT' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_INSTALLMENTS' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_AUTO_RETURN' ) || ! Configuration::deleteByName ( 'MERCADOPAGO_COUNTRY' ) || ! parent::uninstall ())
            return false;
        return true;
    }
    public function uninstallPaymentSettings() {
        $client_id = Configuration::get ( 'MERCADOPAGO_CLIENT_ID' );
        $client_secret = Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' );
        
        if ($client_id != '' && $client_secret != '') {
            $payment_methods = $this->mercadopago->getPaymentMethods ();
            foreach ( $payment_methods as $payment_method ) {
                $pm_variable_name = 'MERCADOPAGO_' . strtoupper ( $payment_method ['id'] );
                if (! Configuration::deleteByName ( $pm_variable_name ))
                    return false;
            }
            
            $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods ();
            foreach ( $offline_methods_payments as $offline_payment ) {
                $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                if (! Configuration::deleteByName ( $op_banner_variable ) || ! Configuration::deleteByName ( $op_active_variable ))
                    return false;
            }
        }
        
        return true;
    }
    public function getContent() {
        $errors = array ();
        $success = false;
        $payment_methods = null;
        $payment_methods_settings = null;
        $offline_payment_settings = null;
        $offline_methods_payments = null;
        
        $this->context->controller->addCss ( $this->_path . 'views/css/settings.css', 'all' );
        $this->context->controller->addCss ( $this->_path . 'views/css/bootstrap.css', 'all' );
        $this->context->controller->addCss ( $this->_path . 'views/css/style.css', 'all' );
        
        if (Tools::getValue ( 'login' )) {
            $client_id = Tools::getValue ( 'MERCADOPAGO_CLIENT_ID' );
            $client_secret = Tools::getValue ( 'MERCADOPAGO_CLIENT_SECRET' );
            
            if (! $this->validateCredential ( $client_id, $client_secret )) {
                $errors [] = $this->l ( 'Client Id or Client Secret invalid.' );
                $success = false;
            } else {
                $this->setDefaultValues ( $client_id, $client_secret );
                
                // populate all payments accoring to country
                $mp = new MP_SDK ( $client_id, $client_secret );
                $payment_methods = $mp->getPaymentMethods ();
                
                // load all offline payment method settings
                $offline_methods_payments = $mp->getOfflinePaymentMethods ();
                
                $offline_payment_settings = array ();
                foreach ( $offline_methods_payments as $offline_payment ) {
                    $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                    $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                    
                    $offline_payment_settings [$offline_payment ['id']] = array (
                            'name' => $offline_payment ['name'],
                            'banner' => Configuration::get ( $op_banner_variable ),
                            'active' => Configuration::get ( $op_active_variable ) 
                    );
                }
            }
        } else if (Tools::getValue ( 'submitmercadopago' )) {
            $client_id = Tools::getValue ( 'MERCADOPAGO_CLIENT_ID' );
            $client_secret = Tools::getValue ( 'MERCADOPAGO_CLIENT_SECRET' );
            $public_key = Tools::getValue ( 'MERCADOPAGO_PUBLIC_KEY' );
            
            $creditcard_active = Tools::getValue ( 'MERCADOPAGO_CREDITCARD_ACTIVE' );
            $boleto_active = Tools::getValue ( 'MERCADOPAGO_BOLETO_ACTIVE' );
            $standard_active = Tools::getValue ( 'MERCADOPAGO_STANDARD_ACTIVE' );
            $mercadopago_log = Tools::getValue ( 'MERCADOPAGO_LOG' );
            $new_country = false;
            
            try {
                if (! $this->validateCredential ( $client_id, $client_secret )) {
                    $errors [] = $this->l ( 'Client Id or Client Secret invalid.' );
                    $success = false;
                } else {
                    $previous_country = $this->getCountry ( Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ), Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ) );
                    $current_country = $this->getCountry ( $client_id, $client_secret );
                    $new_country = $previous_country == $current_country ? false : true;
                    
                    Configuration::updateValue ( 'MERCADOPAGO_CLIENT_ID', $client_id );
                    Configuration::updateValue ( 'MERCADOPAGO_CLIENT_SECRET', $client_secret );
                    Configuration::updateValue ( 'MERCADOPAGO_COUNTRY', $this->getCountry ( $client_id, $client_secret ) );
                    
                    $success = true;
                    
                    if ($creditcard_active == 'true' && ! empty ( $public_key ))
                        Configuration::updateValue ( 'MERCADOPAGO_PUBLIC_KEY', $public_key );
                        
                        // populate all payments accoring to country
                    $this->mercadopago = new MP_SDK ( Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ), Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ) );
                    $payment_methods = $this->mercadopago->getPaymentMethods ();
                }
            } catch ( Exception $e ) {
                PrestaShopLogger::addLog ( 'MercadoPago::getContent - Fatal Error: ' . $e->getMessage (), MP_SDK::FATAL_ERROR, 0 );
                $this->context->smarty->assign ( array (
                        'message_error' => $e->getMessage (),
                        'version' => $this->getPrestashopVersion () 
                ) );
                return $this->display ( __file__, '/views/templates/front/error_admin.tpl' );
            }
            $category = Tools::getValue ( 'MERCADOPAGO_CATEGORY' );
            Configuration::updateValue ( 'MERCADOPAGO_CATEGORY', $category );
            
            $creditcard_banner = Tools::getValue ( 'MERCADOPAGO_CREDITCARD_BANNER' );
            Configuration::updateValue ( 'MERCADOPAGO_CREDITCARD_BANNER', $creditcard_banner );
            
            Configuration::updateValue ( 'MERCADOPAGO_STANDARD_ACTIVE', $standard_active );
            Configuration::updateValue ( 'MERCADOPAGO_LOG', $mercadopago_log );
            
            Configuration::updateValue ( 'MERCADOPAGO_BOLETO_ACTIVE', $boleto_active );
            Configuration::updateValue ( 'MERCADOPAGO_CREDITCARD_ACTIVE', $creditcard_active );
            
            $standard_banner = Tools::getValue ( 'MERCADOPAGO_STANDARD_BANNER' );
            Configuration::updateValue ( 'MERCADOPAGO_STANDARD_BANNER', $standard_banner );
            
            $window_type = Tools::getValue ( 'MERCADOPAGO_WINDOW_TYPE' );
            Configuration::updateValue ( 'MERCADOPAGO_WINDOW_TYPE', $window_type );
            
            $iframe_width = Tools::getValue ( 'MERCADOPAGO_IFRAME_WIDTH' );
            Configuration::updateValue ( 'MERCADOPAGO_IFRAME_WIDTH', $iframe_width );
            
            $iframe_height = Tools::getValue ( 'MERCADOPAGO_IFRAME_HEIGHT' );
            Configuration::updateValue ( 'MERCADOPAGO_IFRAME_HEIGHT', $iframe_height );
            
            $installments = Tools::getValue ( 'MERCADOPAGO_INSTALLMENTS' );
            Configuration::updateValue ( 'MERCADOPAGO_INSTALLMENTS', $installments );
            
            $auto_return = Tools::getValue ( 'MERCADOPAGO_AUTO_RETURN' );
            Configuration::updateValue ( 'MERCADOPAGO_AUTO_RETURN', $auto_return );
            
            $exclude_all = true;
            foreach ( $payment_methods as $payment_method ) {
                $pm_variable_name = 'MERCADOPAGO_' . strtoupper ( $payment_method ['id'] );
                $value = Tools::getValue ( $pm_variable_name );
                
                if ($value != 'on') {
                    $exclude_all = false;
                }
                
                // current settings
                $payment_methods_settings [$payment_method ['id']] = Configuration::get ( $pm_variable_name );
            }
            
            if (! $exclude_all) {
                $payment_methods_settings = array ();
                foreach ( $payment_methods as $payment_method ) {
                    $pm_variable_name = 'MERCADOPAGO_' . strtoupper ( $payment_method ['id'] );
                    $value = Tools::getValue ( $pm_variable_name );
                    // save setting per payment_method
                    Configuration::updateValue ( $pm_variable_name, $value );
                    
                    $payment_methods_settings [$payment_method ['id']] = Configuration::get ( $pm_variable_name );
                }
            } else {
                $errors [] = $this->l ( 'Cannnot exclude all payment methods.' );
                $success = false;
            }
            
            // if it is new country, reset values
            if ($new_country) {
                $this->setCustomSettings ( $client_id, $client_secret, $this->getCountry ( $client_id, $client_secret ) );
                
                $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods ();
                $offline_payment_settings = array ();
                foreach ( $offline_methods_payments as $offline_payment ) {
                    $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                   
                    $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                    
                    $op_banner = Configuration::get ( $op_banner_variable );
                    $op_active = Configuration::get ( $op_banner_variable );
                    
                    $offline_payment_settings [$offline_payment ['id']] = array (
                            'name' => $offline_payment ['name'],
                            'banner' => Configuration::get ( $op_banner_variable ),
                            'active' => Configuration::get ( $op_active_variable ) 
                    );
                }
            } else {
                // save offline payment settings
                $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods ();
                $offline_payment_settings = array ();
                foreach ( $offline_methods_payments as $offline_payment ) {
                    $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                    $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                    
                    $op_banner = Tools::getValue ( $op_banner_variable );
                    
                    // save setting per payment_method
                    Configuration::updateValue ( $op_banner_variable, $op_banner );
                    
                    $op_active = Tools::getValue ( $op_active_variable );
                    // save setting per payment_method
                    Configuration::updateValue ( $op_active_variable, $op_active );
                    
                    $offline_payment_settings [$offline_payment ['id']] = array (
                            'name' => $offline_payment ['name'],
                            'banner' => Configuration::get ( $op_banner_variable ),
                            'active' => Configuration::get ( $op_active_variable ) 
                    );
                }
            }
        } else // it's not a post
{
            
            // populate all payments according to country
            if (Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ) != '' && Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ) != '') {
                $this->mercadopago = new MP_SDK ( Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ), Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ) );
                
                // load payment method settings for standard
                $payment_methods = $this->mercadopago->getPaymentMethods ();
                $payment_methods_settings = array ();
                foreach ( $payment_methods as $payment_method ) {
                    $pm_variable_name = 'MERCADOPAGO_' . strtoupper ( $payment_method ['id'] );
                    $value = Configuration::get ( $pm_variable_name );
                    
                    $payment_methods_settings [$payment_method ['id']] = Configuration::get ( $pm_variable_name );
                }
                // load all offline payment method settings
                $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods ();
                $offline_payment_settings = array ();
                foreach ( $offline_methods_payments as $offline_payment ) {
                    $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                    $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                    
                    $offline_payment_settings [$offline_payment ['id']] = array (
                            'name' => $offline_payment ['name'],
                            'banner' => Configuration::get ( $op_banner_variable ),
                            'active' => Configuration::get ( $op_active_variable ) 
                    );
                }
            }
        }
        
        $notification_url = $this->link->getModuleLink ( 'mercadopago', 'notification', array (), Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false );
        

        
        $settings = array (
                'public_key' => htmlentities ( Configuration::get ( 'MERCADOPAGO_PUBLIC_KEY' ), ENT_COMPAT, 'UTF-8' ),
                'client_id' => htmlentities ( Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ), ENT_COMPAT, 'UTF-8' ),
                'client_secret' => htmlentities ( Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ), ENT_COMPAT, 'UTF-8' ),
                'country' => htmlentities ( Configuration::get ( 'MERCADOPAGO_COUNTRY' ), ENT_COMPAT, 'UTF-8' ),
                'category' => htmlentities ( Configuration::get ( 'MERCADOPAGO_CATEGORY' ), ENT_COMPAT, 'UTF-8' ),
                'notification_url' => htmlentities ( $notification_url, ENT_COMPAT, 'UTF-8' ),
                'creditcard_banner' => htmlentities ( Configuration::get ( 'MERCADOPAGO_CREDITCARD_BANNER' ), ENT_COMPAT, 'UTF-8' ),
                'creditcard_active' => htmlentities ( Configuration::get ( 'MERCADOPAGO_CREDITCARD_ACTIVE' ), ENT_COMPAT, 'UTF-8' ),
                'boleto_active' => htmlentities ( Configuration::get ( 'MERCADOPAGO_BOLETO_ACTIVE' ), ENT_COMPAT, 'UTF-8' ),
                'standard_active' => htmlentities ( Configuration::get ( 'MERCADOPAGO_STANDARD_ACTIVE' ), ENT_COMPAT, 'UTF-8' ),
                'log_active' => htmlentities ( Configuration::get ( 'MERCADOPAGO_LOG' ), ENT_COMPAT, 'UTF-8' ),
                'standard_banner' => htmlentities ( Configuration::get ( 'MERCADOPAGO_STANDARD_BANNER' ), ENT_COMPAT, 'UTF-8' ),
                'window_type' => htmlentities ( Configuration::get ( 'MERCADOPAGO_WINDOW_TYPE' ), ENT_COMPAT, 'UTF-8' ),
                'iframe_width' => htmlentities ( Configuration::get ( 'MERCADOPAGO_IFRAME_WIDTH' ), ENT_COMPAT, 'UTF-8' ),
                'iframe_height' => htmlentities ( Configuration::get ( 'MERCADOPAGO_IFRAME_HEIGHT' ), ENT_COMPAT, 'UTF-8' ),
                'installments' => htmlentities ( Configuration::get ( 'MERCADOPAGO_INSTALLMENTS' ), ENT_COMPAT, 'UTF-8' ),
                'auto_return' => htmlentities ( Configuration::get ( 'MERCADOPAGO_AUTO_RETURN' ), ENT_COMPAT, 'UTF-8' ),
                'uri' => $_SERVER ['REQUEST_URI'],
                'payment_methods' => $payment_methods ? $payment_methods : null,
                'payment_methods_settings' => $payment_methods_settings ? $payment_methods_settings : null,
                'offline_methods_payments' => $offline_methods_payments ? $offline_methods_payments : null,
                'offline_payment_settings' => $offline_payment_settings ? $offline_payment_settings : null,
                'errors' => $errors,
                'success' => $success,
                'this_path_ssl' => (Configuration::get ( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://') . htmlspecialchars ( $_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8' ) . __PS_BASE_URI__,
                'version' => $this->getPrestashopVersion () 
        );
        
        $this->context->smarty->assign ( $settings );
        
        return $this->display ( __file__, '/views/templates/admin/settings.tpl' );
    }
    private function setDefaultValues($client_id, $client_secret) {
        $country = $this->getCountry ( $client_id, $client_secret );
        
        Configuration::updateValue ( 'MERCADOPAGO_CLIENT_ID', $client_id );
        Configuration::updateValue ( 'MERCADOPAGO_CLIENT_SECRET', $client_secret );
        Configuration::updateValue ( 'MERCADOPAGO_COUNTRY', $country );
        Configuration::updateValue ( 'MERCADOPAGO_WINDOW_TYPE', 'redirect' );
        Configuration::updateValue ( 'MERCADOPAGO_IFRAME_WIDTH', '725' );
        Configuration::updateValue ( 'MERCADOPAGO_IFRAME_HEIGHT', '570' );
        Configuration::updateValue ( 'MERCADOPAGO_INSTALLMENTS', '12' );
        Configuration::updateValue ( 'MERCADOPAGO_AUTO_RETURN', 'approved' );
        
        $this->setCustomSettings ( $client_id, $client_secret, $country );
    }
    private function setCustomSettings($client_id, $client_secret, $country) {
        if ($country == "MLB" || $country == "MLM" || $country == "MLA" || $country == "MLC" || $country == "MCO") {
            Configuration::updateValue ( 'MERCADOPAGO_CREDITCARD_BANNER', (Configuration::get ( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://') . htmlspecialchars ( $_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8' ) . __PS_BASE_URI__ . 'modules/mercadopago/views/img/' . $country . '/credit_card.png' );
            Configuration::updateValue ( 'MERCADOPAGO_CREDITCARD_ACTIVE', 'true' );
            Configuration::updateValue ( 'MERCADOPAGO_STANDARD_ACTIVE', 'false' );
            
            // set all offline payment settings
            $mp = new MP_SDK ( $client_id, $client_secret );
            
            $offline_methods_payments = $mp->getOfflinePaymentMethods ();
            foreach ( $offline_methods_payments as $offline_payment ) {
                $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                Configuration::updateValue ( $op_banner_variable, $offline_payment ['secure_thumbnail'] );
                $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                Configuration::updateValue ( $op_active_variable, 'true' );
            }
        } else {
            Configuration::updateValue ( 'MERCADOPAGO_STANDARD_ACTIVE', 'true' );
        }
        
        Configuration::updateValue ( 'MERCADOPAGO_STANDARD_BANNER', (Configuration::get ( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://') . htmlspecialchars ( $_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8' ) . __PS_BASE_URI__ . 'modules/mercadopago/views/img/' . $country . '/banner_all_methods.png' );
    }
    private function getCountry($client_id, $client_secret) {
        $mp = new MP_SDK ( $client_id, $client_secret );
        return $mp->getCountry ();
    }
    private function validateCredential($client_id, $client_secret) {
        $mp = new MP_SDK ( $client_id, $client_secret );
        return $mp->getAccessToken () ? true : false;
    }
    public function hookDisplayHeader() {
        if (! $this->active) {
            return;
        }
        
        $data = array (
                'creditcard_active' => Configuration::get ( 'MERCADOPAGO_CREDITCARD_ACTIVE' ),
                'public_key' => Configuration::get ( 'MERCADOPAGO_PUBLIC_KEY' ) 
        );
        
        $this->context->controller->addCss ( $this->_path . 'views/css/mercadopago_core.css', 'all' );
        $this->context->controller->addCss ( $this->_path . 'views/css/chico.min.css', 'all' );
        $this->context->controller->addCss ( $this->_path . 'views/css/mercadopago_v' . $this->getPrestashopVersion () . '.css', 'all' );
        $this->context->smarty->assign ( $data );
        
        return $this->display ( __file__, '/views/templates/hook/header.tpl' );
    }
    public function hookPayment($params) {
        if (! $this->active) {
            return;
        }
        
        if ($this->hasCredential ()) {
            $this_path_ssl = (Configuration::get ( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://') . htmlspecialchars ( $_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8' ) . __PS_BASE_URI__;
            $data = array (
                    'this_path_ssl' => $this_path_ssl,
                    'boleto_active' => Configuration::get ( 'MERCADOPAGO_BOLETO_ACTIVE' ),
                    'creditcard_active' => Configuration::get ( 'MERCADOPAGO_CREDITCARD_ACTIVE' ),
                    'standard_active' => Configuration::get ( 'MERCADOPAGO_STANDARD_ACTIVE' ),
                    'log_active' => Configuration::get ( 'MERCADOPAGO_LOG' ),
                    'version' => $this->getPrestashopVersion (),
                    'custom_action_url' => $this->link->getModuleLink ( 'mercadopago', 'custompayment', array (), Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false ),
                    'payment_status' => Tools::getValue ( 'payment_status' ),
                    'status_detail' => Tools::getValue ( 'status_detail' ),
                    'payment_method_id' => Tools::getValue ( 'payment_method_id' ),
                    'installments' => Tools::getValue ( 'installments' ),
                    'statement_descriptor' => Tools::getValue ( 'statement_descriptor' ),
                    'window_type' => Configuration::get ( 'MERCADOPAGO_WINDOW_TYPE' ),
                    'iframe_width' => Configuration::get ( 'MERCADOPAGO_IFRAME_WIDTH' ),
                    'iframe_height' => Configuration::get ( 'MERCADOPAGO_IFRAME_HEIGHT' ),
                    'country' => Configuration::get ( 'MERCADOPAGO_COUNTRY' ) 
            );
            
            // send credit card configurations only activated
            if (Configuration::get ( 'MERCADOPAGO_CREDITCARD_ACTIVE' ) == 'true') {
                $data ['public_key'] = Configuration::get ( 'MERCADOPAGO_PUBLIC_KEY' );
                $data ['creditcard_banner'] = Configuration::get ( 'MERCADOPAGO_CREDITCARD_BANNER' );
                $data ['amount'] = ( double ) number_format ( $params ['cart']->getOrderTotal ( true, Cart::BOTH ), 2, '.', '' );
            }
            
            // send standard configurations only activated
            if (Configuration::get ( 'MERCADOPAGO_STANDARD_ACTIVE' ) == 'true') {
                $result = $this->createStandardCheckoutPreference ();
                if (array_key_exists ( 'init_point', $result ['response'] )) {
                    $data ['standard_banner'] = Configuration::get ( 'MERCADOPAGO_STANDARD_BANNER' );
                    $data ['preferences_url'] = $result ['response'] ['init_point'];
                } else {
                    $data ['preferences_url'] = null;
                    PrestaShopLogger::addLog ( 'MercadoPago::hookPayment - An error occurred during preferences creation. Please check your credentials and try again.: ', MP_SDK::ERROR, 0 );
                }
            }
            
            // send offline settings
            $offline_methods_payments = $this->mercadopago->getOfflinePaymentMethods ();
            
            $offline_payment_settings = array ();
            foreach ( $offline_methods_payments as $offline_payment ) {
                $op_banner_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_BANNER' );
                $op_active_variable = 'MERCADOPAGO_' . strtoupper ( $offline_payment ['id'] . '_ACTIVE' );
                
                $offline_payment_settings [$offline_payment ['id']] = array (
                        'name' => $offline_payment ['name'],
                        'banner' => Configuration::get ( $op_banner_variable ),
                        'active' => Configuration::get ( $op_active_variable ) 
                );
            }
            $data ['offline_payment_settings'] = $offline_payment_settings;
            
            $this->context->smarty->assign ( $data );
            return $this->display ( __file__, '/views/templates/hook/checkout.tpl' );
        }
    }
    /**
     *
     * @param
     *            $params
     */
    public function hookPaymentReturn($params) {
        if (! $this->active) {
            return;
        }
        if (Tools::getValue ( 'payment_method_id' ) == 'bolbradesco' || Tools::getValue ( 'payment_type' ) == 'bank_transfer' || Tools::getValue ( 'payment_type' ) == 'atm' || Tools::getValue ( 'payment_type' ) == 'ticket') {
            $this->context->controller->addCss ( $this->_path . 'views/css/mercadopago_core.css', 'all' );
            
            $this->context->smarty->assign ( array (
                    'payment_id' => Tools::getValue ( 'payment_id' ),
                    'boleto_url' => Tools::getValue ( 'boleto_url' ),
                    'this_path_ssl' => (Configuration::get ( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://') . htmlspecialchars ( $_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8' ) . __PS_BASE_URI__ 
            ) );
            return $this->display ( __file__, '/views/templates/hook/boleto_payment_return.tpl' );
        } else {
            $this->context->controller->addCss ( $this->_path . 'views/css/mercadopago_core.css', 'all' );
            $this->context->smarty->assign ( array (
                    'payment_status' => Tools::getValue ( 'payment_status' ),
                    'status_detail' => Tools::getValue ( 'status_detail' ),
                    'card_holder_name' => Tools::getValue ( 'card_holder_name' ),
                    'four_digits' => Tools::getValue ( 'four_digits' ),
                    'payment_method_id' => Tools::getValue ( 'payment_method_id' ),
                    'installments' => Tools::getValue ( 'installments' ),
                    'transaction_amount' => Tools::displayPrice ( Tools::getValue ( 'transaction_amount' ), $params ['currencyObj'], false ),
                    'statement_descriptor' => Tools::getValue ( 'statement_descriptor' ),
                    'payment_id' => Tools::getValue ( 'payment_id' ),
                    'amount' => Tools::displayPrice ( Tools::getValue ( 'amount' ), $params ['currencyObj'], false ),
                    'this_path_ssl' => (Configuration::get ( 'PS_SSL_ENABLED' ) ? 'https://' : 'http://') . htmlspecialchars ( $_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8' ) . __PS_BASE_URI__ 
            ) );
            return $this->display ( __file__, '/views/templates/hook/creditcard_payment_return.tpl' );
        }
    }
    /**
     * Verify the credentials
     * 
     * @return boolean
     */
    private function hasCredential() {
        return Configuration::get ( 'MERCADOPAGO_CLIENT_ID' ) != '' && Configuration::get ( 'MERCADOPAGO_CLIENT_SECRET' ) != '';
    }
    /**
     *
     * @param
     *            $post
     */
    public function execPayment($post) {
        $preferences = $this->getPreferencesCustom ( $post );
        $result = $this->mercadopago->createCustomPayment ( $preferences );
        return $result ['response'];
    }
    /**
     *
     * @param
     *            $post
     */
    private function getPreferencesCustom($post) {
        $customer_fields = Context::getContext ()->customer->getFields ();
        $cart = Context::getContext ()->cart;
        
        // items
        $image_url = '';
        $products = $cart->getProducts ();
        $items = array ();
        $summary = '';
        
        foreach ( $products as $key => $product ) {
            $image_url = '';
            // get image URL
            if (! empty ( $product ['id_image'] )) {
                $image = new Image ( $product ['id_image'] );
                $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath () . '.' . $image->image_format;
            }
            
            $item = array (
                    'id' => $product ['id_product'],
                    'title' => $product ['name'],
                    'description' => $product ['description_short'],
                    'picture_url' => $image_url,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ),
                    'quantity' => $product ['quantity'],
                    'unit_price' => $product ['price_wt'] 
            );
            if ($key == 0) {
                $summary .= $product ['name'];
            } else {
                $summary .= ', ' . $product ['name'];
            }
            
            $items [] = $item;
        }
        
        // include shipping cost
        $shipping_cost = ( double ) $cart->getOrderTotal ( true, Cart::ONLY_SHIPPING );
        if ($shipping_cost > 0) {
            $item = array (
                    'title' => 'Shipping',
                    'description' => 'Shipping service used by store',
                    'quantity' => 1,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ),
                    'unit_price' => $shipping_cost 
            );
            
            $items [] = $item;
        }
        
        // include wrapping cost
        $wrapping_cost = ( double ) $cart->getOrderTotal ( true, Cart::ONLY_WRAPPING );
        if ($wrapping_cost > 0) {
            $item = array (
                    'title' => 'Wrapping',
                    'description' => 'Wrapping service used by store',
                    'quantity' => 1,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ),
                    'unit_price' => $wrapping_cost 
            );
            
            $items [] = $item;
        }
        
        // include discounts
        $discounts = ( double ) $cart->getOrderTotal ( true, Cart::ONLY_DISCOUNTS );
        if ($discounts > 0) {
            $item = array (
                    'title' => 'Discount',
                    'description' => 'Discount provided by store',
                    'quantity' => 1,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ),
                    'unit_price' => - $discounts 
            );
            
            $items [] = $item;
        }
        
        // Get payer address for additional_info
        $address_invoice = new Address ( ( integer ) $cart->id_address_invoice );
        $phone = $address_invoice->phone;
        $phone .= $phone == '' ? '' : '|';
        $phone .= $address_invoice->phone_mobile;
        $payer_additional_info = array (
                'first_name' => $customer_fields ['firstname'],
                'last_name' => $customer_fields ['lastname'],
                'registration_date' => $customer_fields ['date_add'],
                'phone' => array (
                        'area_code' => '-',
                        'number' => $phone 
                ),
                'address' => array (
                        'zip_code' => $address_invoice->postcode,
                        'street_name' => $address_invoice->address1 . ' - ' . $address_invoice->address2 . ' - ' . $address_invoice->city . '/' . $address_invoice->country,
                        'street_number' => '-' 
                ) 
        );
        // Get shipment address for additional_info
        $address_delivery = new Address ( ( integer ) $cart->id_address_delivery );
        $shipments = array (
                'receiver_address' => array (
                        'zip_code' => $address_delivery->postcode,
                        'street_name' => $address_delivery->address1 . ' - ' . $address_delivery->address2 . ' - ' . $address_delivery->city . '/' . $address_delivery->country,
                        'street_number' => '-',
                        'floor' => '-',
                        'apartment' => '-' 
                ) 
        );
        $payment_preference = array (
                'transaction_amount' => ( double ) number_format ( $cart->getOrderTotal ( true, Cart::BOTH ), 2, '.', '' ),
                'external_reference' => $cart->id,
                'statement_descriptor' => "",
                'payment_method_id' => $post ['payment_method_id'],
                'payer' => array (
                        'email' => $customer_fields ['email'] 
                ),
                'notification_url' => $this->link->getModuleLink ( 'mercadopago', 'notification', array (), Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false ) . '?checkout=custom&',
                'additional_info' => array (
                        'items' => $items,
                        'payer' => $payer_additional_info,
                        'shipments' => $shipments 
                ) 
        );
        $payment_preference ['description'] = $summary;
        // add only for creditcard
        if (array_key_exists ( 'card_token_id', $post )) {
            // add only it has issuer id
            if (array_key_exists ( 'issuersOptions', $post )) {
                $payment_preference ['issuer_id'] = ( integer ) $post ['issuersOptions'];
            }
            
            $payment_preference ['token'] = $post ['card_token_id'];
            $payment_preference ['installments'] = ( integer ) $post ['installments'];
        }
        
        if (! $this->mercadopago->isTestUser ()) {
            switch (Configuration::get ( 'MERCADOPAGO_COUNTRY' )) {
                case 'MLB' :
                    $data ['sponsor_id'] = 178326379;
                    break;
                case 'MLM' :
                    $data ['sponsor_id'] = 187899553;
                    break;
                case 'MLA' :
                    $data ['sponsor_id'] = 187899872;
                    break;
                case 'MCO' :
                    $data ['sponsor_id'] = 187900060;
                    break;
                case 'MLV' :
                    $data ['sponsor_id'] = 187900246;
                    break;
                case 'MLC' :
                    $data ['sponsor_id'] = 187900485;
                    break;
            }
        }
        
        return $payment_preference;
    }
    private function getPrestashopPreferencesStandard() {
        $customer_fields = Context::getContext ()->customer->getFields ();
        $cart = Context::getContext ()->cart;
        // Get shipment data
        $address_delivery = new Address ( ( integer ) $cart->id_address_delivery );
        $shipments = array (
                'receiver_address' => array (
                        'floor' => '-',
                        'zip_code' => $address_delivery->postcode,
                        'street_name' => $address_delivery->address1 . ' - ' . $address_delivery->address2 . ' - ' . $address_delivery->city . '/' . $address_delivery->country,
                        'apartment' => '-',
                        'street_number' => '-' 
                ) 
        );
        // Get costumer data
        $address_invoice = new Address ( ( integer ) $cart->id_address_invoice );
        $phone = $address_invoice->phone;
        $phone .= $phone == '' ? '' : '|';
        $phone .= $address_invoice->phone_mobile;
        $customer_data = array (
                'first_name' => $customer_fields ['firstname'],
                'last_name' => $customer_fields ['lastname'],
                'email' => $customer_fields ['email'],
                'phone' => array (
                        'area_code' => '-',
                        'number' => $phone 
                ),
                'address' => array (
                        'zip_code' => $address_invoice->postcode,
                        'street_name' => $address_invoice->address1 . ' - ' . $address_invoice->address2 . ' - ' . $address_invoice->city . '/' . $address_invoice->country,
                        'street_number' => '-' 
                ),
                // just have this data when using credit card
                'identification' => array (
                        'number' => '',
                        'type' => '' 
                ) 
        );
        // items
        $image_url = '';
        $products = $cart->getProducts ();
        $items = array ();
        $summary = '';
        foreach ( $products as $key => $product ) {
            $image_url = '';
            // get image URL
            if (! empty ( $product ['id_image'] )) {
                $image = new Image ( $product ['id_image'] );
                $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath () . '.' . $image->image_format;
            }
            $item = array (
                    'id' => $product ['id_product'],
                    'title' => $product ['name'],
                    'description' => $product ['description_short'],
                    'quantity' => $product ['quantity'],
                    'unit_price' => $product ['price_wt'],
                    'picture_url' => $image_url,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ) 
            );
            if ($key == 0) {
                $summary .= $product ['name'];
            } else {
                $summary .= ', ' . $product ['name'];
            }
            $items [] = $item;
        }
        // include shipping cost
        $shipping_cost = ( double ) $cart->getOrderTotal ( true, Cart::ONLY_SHIPPING );
        if ($shipping_cost > 0) {
            $item = array (
                    'title' => 'Shipping',
                    'description' => 'Shipping service used by store',
                    'quantity' => 1,
                    'unit_price' => $shipping_cost,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ) 
            );
            $items [] = $item;
        }
        // include wrapping cost
        $wrapping_cost = ( double ) $cart->getOrderTotal ( true, Cart::ONLY_WRAPPING );
        if ($wrapping_cost > 0) {
            $item = array (
                    'title' => 'Wrapping',
                    'description' => 'Wrapping service used by store',
                    'quantity' => 1,
                    'unit_price' => $wrapping_cost,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ),
                    'currency_id' => $cart->id_currency
            );
            $items [] = $item;
        }
        // include discounts
        $discounts = ( double ) $cart->getOrderTotal ( true, Cart::ONLY_DISCOUNTS );
        if ($discounts > 0) {
            $item = array (
                    'title' => 'Discount',
                    'description' => 'Discount provided by store',
                    'quantity' => 1,
                    'unit_price' => - $discounts,
                    'category_id' => Configuration::get ( 'MERCADOPAGO_CATEGORY' ) 
            );
            $items [] = $item;
        }
        $data = array (
                'external_reference' => $cart->id,
                'customer' => $customer_data,
                'items' => $items,
                'shipments' => $shipments 
        );
        if (! $this->mercadopago->isTestUser ()) {
            switch (Configuration::get ( 'MERCADOPAGO_COUNTRY' )) {
                case 'MLB' :
                    $data ['sponsor_id'] = 178326379;
                    break;
                case 'MLM' :
                    $data ['sponsor_id'] = 187899553;
                    break;
                case 'MLA' :
                    $data ['sponsor_id'] = 187899872;
                    break;
                case 'MCO' :
                    $data ['sponsor_id'] = 187900060;
                    break;
                case 'MLV' :
                    $data ['sponsor_id'] = 187900246;
                    break;
                case 'MLC' :
                    $data ['sponsor_id'] = 187900485;
                    break;
            }
        }
        
        $data ['auto_return'] = Configuration::get ( 'MERCADOPAGO_AUTO_RETURN' ) == 'approved' ? 'approved' : '';
        $data ['back_urls'] ['success'] = $this->link->getModuleLink ( 'mercadopago', 'standardreturn', array (), Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false );
        $data ['back_urls'] ['failure'] = $this->link->getPageLink ( 'order-opc', Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false, null );
        $data ['back_urls'] ['pending'] = $this->link->getModuleLink ( 'mercadopago', 'standardreturn', array (), Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false );
        $data ['payment_methods'] ['excluded_payment_methods'] = $this->getExcludedPaymentMethods ();
        $data ['payment_methods'] ['excluded_payment_types'] = array ();
        $data ['payment_methods'] ['installments'] = ( integer ) Configuration::get ( 'MERCADOPAGO_INSTALLMENTS' );
        
        $data ['notification_url'] = $this->link->getModuleLink ( 'mercadopago', 'notification', array (), Configuration::get ( 'PS_SSL_ENABLED' ), null, null, false ) . '?checkout=standard&';
        // swap to payer index since customer is only for transparent
        $data ['customer'] ['name'] = $data ['customer'] ['first_name'];
        $data ['customer'] ['surname'] = $data ['customer'] ['last_name'];
        $data ['payer'] = $data ['customer'];
        unset ( $data ['customer'] );
        
        return $data;
    }
    public function createStandardCheckoutPreference() {
        return $this->mercadopago->createPreference ( $this->getPrestashopPreferencesStandard ( null ) );
    }
    private function getExcludedPaymentMethods() {
        $payment_methods = $this->mercadopago->getPaymentMethods ();
        $excluded_payment_methods = array ();
        
        foreach ( $payment_methods as $payment_method ) {
            $pm_variable_name = 'MERCADOPAGO_' . strtoupper ( $payment_method ['id'] );
            $value = Configuration::get ( $pm_variable_name );
            
            if ($value == 'on') {
                $excluded_payment_methods [] = array (
                        'id' => $payment_method ['id'] 
                );
            }
        }
        
        return $excluded_payment_methods;
    }
    public function listenIPN($checkout, $topic, $id) {
        $payment_method_ids = array ();
        $payment_ids = array ();
        $payment_statuses = array ();
        $payment_types = array ();
        $credit_cards = array ();
        $transaction_amounts = 0;
        $cardholders = array ();
        $external_reference = '';
        if (Configuration::get ( 'MERCADOPAGO_LOG' ) == 'true') {
            PrestaShopLogger::addLog ( 'MercadoPago :: listenIPN - topic = ' . $topic, MP_SDK::INFO, 0 );
            PrestaShopLogger::addLog ( 'MercadoPago :: listenIPN - id = ' . $id, MP_SDK::INFO, 0 );
            PrestaShopLogger::addLog ( 'MercadoPago :: listenIPN - checkout = ' . $checkout, MP_SDK::INFO, 0 );
        }
        
        if ($checkout == "standard" && $topic == 'merchant_order' && $id > 0) {
            // get merchant order info
            $result = $this->mercadopago->getMerchantOrder ( $id );
            $merchant_order_info = $result ['response'];
            
            $payments = $merchant_order_info ['payments'];
            $external_reference = $merchant_order_info ['external_reference'];
            foreach ( $payments as $payment ) {
                // get payment info
                $result = $this->mercadopago->getPayment ( $payment ['id'] );
                $payment_info = $result ['response'];
                // colect payment details
                $payment_ids [] = $payment_info ['id'];
                $payment_statuses [] = $payment_info ['status'];
                $payment_types [] = $payment_info ['payment_type_id'];
                $transaction_amounts += $payment_info ['transaction_amount'];
                if ($payment_info ['payment_type_id'] == 'credit_card') {
                    
                    $payment_method_ids [] = isset ( $payment_info ['payment_method_id'] ) ? $payment_info ['payment_method_id'] : "";
                    $credit_cards [] = isset ( $payment_info ['card']['last_four_digits'] ) ? '**** **** **** ' . $payment_info ['card']['last_four_digits'] : "";
                    
                    $cardholders [] = isset($payment_info ['card']['cardholder'] ['name']) ? $payment_info ['card']['cardholder'] ['name'] : "";
                }
            }
            
            if ($merchant_order_info ['total_amount'] == $transaction_amounts) {
                $this->updateOrder ( $payment_ids, $payment_statuses, $payment_method_ids, $payment_types, $credit_cards, $cardholders, $transaction_amounts, $external_reference );
            }
        } else if ($checkout == "custom" && $topic == 'payment' && $id > 0) {
            
            $result = $this->mercadopago->getPayment ( $id );
            
            $payment_info = $result ['response'];
            
            $external_reference = $payment_info ['external_reference'];
            // colect payment details
            $payment_ids [] = $payment_info ['id'];
            $payment_statuses [] = $payment_info ['status'];
            $payment_types [] = $payment_info ['payment_type_id'];
            $transaction_amounts += $payment_info ['transaction_amount'];
            if ($payment_info ['payment_type_id'] == 'credit_card') {
                $payment_method_ids [] = $payment_info ['payment_method_id'];
                $credit_cards [] = '**** **** **** ' . $payment_info ['card']['last_four_digits'];
                $cardholders [] = $payment_info ['card']['cardholder'] ['name'];
            }
            
            $this->updateOrder ( $payment_ids, $payment_statuses, $payment_method_ids, $payment_types, $credit_cards, $cardholders, $transaction_amounts, $external_reference );
        }
    }
    
    /**
     * Verify if there is state approved for order
     */
    public static function getOrderStateApproved($id_order) {
        return ( bool ) Db::getInstance ()->getValue ( '
        SELECT `id_order_state`
        FROM ' . _DB_PREFIX_ . 'order_history
        WHERE `id_order` = ' . ( int ) $id_order . ' 
        AND `id_order_state` = ' . ( int ) Configuration::get ( 'MERCADOPAGO_STATUS_1' ) );
    }
    private function updateOrder($payment_ids, $payment_statuses, $payment_method_ids, $payment_types, $credit_cards, $cardholders, $transaction_amounts, $external_reference) {
        // if has two creditcard validate whether payment has same status in order to continue validating order
        if (count ( $payment_statuses ) == 1 || (count ( $payment_statuses ) == 2 && $payment_statuses [0] == $payment_statuses [1])) {
            $order;
            $order_status = null;
            $payment_status = $payment_statuses [0];
            $payment_type = $payment_types [0];
            switch ($payment_status) {
                case 'in_process' :
                    $order_status = 'MERCADOPAGO_STATUS_0';
                    break;
                case 'approved' :
                    $order_status = 'MERCADOPAGO_STATUS_1';
                    break;
                case 'cancelled' :
                    $order_status = 'MERCADOPAGO_STATUS_2';
                    break;
                case 'refunded' :
                    $order_status = 'MERCADOPAGO_STATUS_4';
                    break;
                case 'charged_back' :
                    $order_status = 'MERCADOPAGO_STATUS_5';
                    break;
                case 'in_mediation' :
                    $order_status = 'MERCADOPAGO_STATUS_6';
                    break;
                case 'pending' :
                    $order_status = 'MERCADOPAGO_STATUS_7';
                    break;
                case 'rejected' :
                    $order_status = 'MERCADOPAGO_STATUS_3';
                    break;
            }
            
            // just change if there is an order status
            if ($order_status) {
                $id_cart = $external_reference;
                $id_order = Order::getOrderByCartId ( $id_cart );
                
                if ($id_order) {
                    $order = new Order ( $id_order );
                }
                
                // If order wasn't created yet and payment is approved or pending or in_process, create it.
                // This can happen when user closes checkout standard
                if (empty ( $id_order ) && ($payment_status == 'in_process' || $payment_status == 'approved' || $payment_status == 'pending')) {
                    $cart = new Cart ( $id_cart );
                    $total = ( double ) number_format ( $transaction_amounts, 2, '.', '' );
                    $extra_vars = array (
                            '{bankwire_owner}' => $this->textshowemail,
                            '{bankwire_details}' => '',
                            '{bankwire_address}' => '' 
                    );
                    $id_order = ! $id_order ? Order::getOrderByCartId ( $id_cart ) : $id_order;
                    $order = new Order ( $id_order );
                    $cart_total_paid = ( float ) Tools::ps_round ( ( float ) $cart->getOrderTotal ( false, Cart::ONLY_PRODUCTS ), 2 );
                    
                    $this->validateOrder ( $id_cart, Configuration::get ( $order_status ), $total, $this->displayName, null, $extra_vars, $cart->id_currency, false, $cart->secure_key );
                } else if (! empty ( $order ) && $order->current_state != null && $order->current_state != Configuration::get ( $order_status )) {
                    /*
                     * this is necessary to ignore the transactions with the same
                     * external reference and states diferents
                     */
                    if ($payment_status == 'cancelled') {
                        $retorno = $this->getOrderStateApproved ( $id_order );
                        if ($retorno) {
                            return;
                        }
                    }
                    
                    $id_order = ! $id_order ? Order::getOrderByCartId ( $id_cart ) : $id_order;
                    $order = new Order ( $id_order );
                    $this->_updateOrderHistory ( $order->id, Configuration::get ( $order_status ) );
                    
                    // Cancel the order to force products to go to stock.
                    switch ($payment_status) {
                        case 'cancelled' :
                        case 'refunded' :
                        case 'rejected' :
                            $this->_updateOrderHistory ( $id_order, Configuration::get ( 'PS_OS_CANCELED' ), false );
                            break;
                    }
                }
                if ($order) {
                    // update order payment information
                    $order_payments = $order->getOrderPayments ();
                    foreach ( $order_payments as $order_payment ) {
                        $order_payment->transaction_id = join ( " / ", $payment_ids );
                        
                        if ($payment_type == "credit_card") {
                            $order_payment->card_number = join ( " / ", $credit_cards );
                            
                            $order_payment->card_brand = join ( " / ", $payment_method_ids );
                            $order_payment->card_holder = join ( " / ", $cardholders );
                            
                            // card_expiration just custom checkout has it. Can't fecht it thru collections
                        }
                        $order_payment->save ();
                    }
                }
            }
        }
    }
    private function _updateOrderHistory($id_order, $status, $mail = true) {
        // Change order state and send email
        $history = new OrderHistory ();
        $history->id_order = ( integer ) $id_order;
        $history->changeIdOrderState ( ( integer ) $status, ( integer ) $id_order, true );
        if ($mail) {
            $extra_vars = array ();
            $history->addWithemail ( true, $extra_vars );
        }
    }
    public function getPrestashopVersion() {
        if (version_compare ( _PS_VERSION_, '1.6.0.1', '>=' )) {
            $version = 6;
        } else if (version_compare ( _PS_VERSION_, '1.5.0.1', '>=' )) {
            $version = 5;
        } else {
            $version = 4;
        }
        return $version;
    }
}
?>
