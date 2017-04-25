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

    public function __construct()
    {
        $this->name = "mercadopago";
        $this->tab = "payments_gateways";
        $this->version = "1.0.3";
        $this->ps_versions_compliancy = array("min" => "1.7", "max" => _PS_VERSION_);
        $this->author = "Mercado Pago";
        $this->controllers = array("validationstandard", "standardreturn");
        $this->has_curl = function_exists('curl_version');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = "checkbox";
        $this->confirmUninstall = $this->l("Are you sure you want to uninstall?");
        if (!isset($this->access_key) || !isset($this->secret_key)) {
            $this->warning = $this->l("Your Mercado Pago details must be configured before using this module.");
        }
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l("Mercado Pago");
        $this->description = $this->l("Receive your payments using Mercado Pago, you can using the Checkout Standard.");

// this->getTranslator()->trans('Receive your payments using Mercado Pago, you can using the Checkout Standard.', array(), 'Admin.Global');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l("No currency has been set for this module.");
        }
    }

    public function install()
    {
        $returnStatus = $this->createStates();
        return parent::install() &&
            $this->registerHook('paymentOptions') &&
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
    /**
     * Create the states, we need to check if doens`t exists.
     */
    private function createStates()
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
            || !Configuration::deleteByName("MERCADOPAGO_CLIENT_SECRET")
            || !Configuration::deleteByName("MERCADOPAGO_CLIENT_ID")
            || !Configuration::deleteByName("MERCADOPAGO_INSTALLMENTS")
            || !Configuration::deleteByName("MERCADOPAGO_CATEGORY")

            || !$this->unregisterHook("paymentOptions")
            || !$this->unregisterHook("paymentReturn")
            || !$this->unregisterHook("payment")
            || !$this->unregisterHook("displayPayment")
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

        $tabs[] = array(
            "id" => "presentation",
            "title" => $tabsLocale["presentation"],
            "content" => $this->getPresentationTemplate()
        );

        $tabs[] = array(
            "id" => "requirements",
            "title" => $tabsLocale["requirements"],
            "content" => $this->getPageRequirements()
        );

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
                "show" => true,
                "mercadoPagoActive" => Configuration::get("MERCADOPAGO_STARDAND_ACTIVE"),
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
            $this->validateGeneralSetting();
            $this->selectedTab = "general_setting";
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
            array(
               "id" => "IFRAME",
               "name"   => $display["iframe"]
            ),
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

    public function getExternalPaymentOption()
    {
        $country = strtoupper(MPApi::getInstanceMP()->getCountry());
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l("Mercado Pago Redirect"))
                       ->setAction($this->context->link->getModuleLink($this->name, "standard", array(), true))
                       ->setInputs([
                            "token" => [
                                "name" =>"token",
                                "type" =>"hidden",
                                "value" =>"12345689",
                            ],
                        ])
                       ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name."/views/img/".$country."/mercadopago_468X60.jpg"));

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
                $message = $this->l("Unfortunately, the confirmation of your payment failed.
                    Please contact your merchant for clarification.");
            break;
            default:
                $message = "";
            break;
        }
        return $message;
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
