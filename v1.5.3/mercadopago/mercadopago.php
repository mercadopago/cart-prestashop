<?php

include(dirname(__FILE__) . '/includes/mercadopago.php');
include(dirname(__FILE__) . '/includes/MPApi.php');

if (!defined('_PS_VERSION_'))
    exit;

class MercadoPago extends PaymentModule {

    private $_html = '';
    private $_postErrors = array();
    public $currencies;
    public $_botoes = array('buy_now_mlb.gif');

    public function __construct() {
        $this->name = 'mercadopago';
        $this->tab = 'payments_gateways';
        $this->version = '2.0';
        $this->currencies = true;
        $this->currencies_mode = 'radio';
        $this->need_instance = 1;

        parent::__construct();

        $this->page = basename(__file__, '.php');
        $this->displayName = $this->l('MercadoPago');
        $this->description = $this->l('Receive Payments throw MercadoPago');
        $this->confirmUninstall = $this->l('Are you sure that want to delete your datas?');
        $this->textshowemail = $this->l('You must follow MercadoPago rules to your shop be valid');
    }

    public function install() {

        if (!Configuration::get('mercadopago_STATUS_1'))
            $this->create_states();
        if
        (
                !parent::install()
                OR !Configuration::updateValue('mercadopago_CLIENT_ID', '')
                OR !Configuration::updateValue('mercadopago_CLIENT_SECRET', '')
		OR !Configuration::updateValue('mercadopago_CATEGORY', 'others')
		OR !Configuration::updateValue('mercadopago_TYPECHECKOUT', 'Lightbox')
		OR !Configuration::updateValue('mercadopago_SANDBOX', "Deactivate")
                OR !Configuration::updateValue('mercadopago_COUNTRY', '')
                OR !Configuration::updateValue('mercadopago_METHODS', '')
		OR !Configuration::updateValue('mercadopago_INSTALLMENTS', "inst-24")
                OR !Configuration::updateValue('mercadopago_URLPROCESS', _PS_BASE_URL_ . '/history.php')
                OR !Configuration::updateValue('mercadopago_URLSUCCESFULL', _PS_BASE_URL_ . '/history.php')
                OR !Configuration::updateValue('mercadopago_BTN', 0)
                OR !$this->registerHook('payment')
                OR !$this->registerHook('paymentReturn')
        )
            return false;

        return true;
    }

    public function create_states() {

        $this->order_state = array(
            array('ccfbff', '00100', 'MercadoPago - Transação em Andamento', ''),
            array('c9fecd', '11110', 'MercadoPago - Transação Concluída', 'payment'),
            array('fec9c9', '11110', 'MercadoPago - Transação Cancelada', 'order_canceled'),
            array('fec9c9', '11110', 'MercadoPago - Transação Rejeitada', 'payment_error')
        );


        $languages = Db::getInstance()->ExecuteS('
		SELECT `id_lang`, `iso_code`
		FROM `' . _DB_PREFIX_ . 'lang`
		');

        foreach ($this->order_state as $key => $value) {

            Db::getInstance()->Execute
                    ('
			INSERT INTO `' . _DB_PREFIX_ . 'order_state` 
			( `invoice`, `send_email`, `color`, `unremovable`, `logable`, `delivery`) 
			VALUES
			(' . $value[1][0] . ', ' . $value[1][1] . ', \'#' . $value[0] . '\', ' . $value[1][2] . ', ' . $value[1][3] . ', ' . $value[1][4] . ');
		    ');


            $sql = 'SELECT MAX(id_order_state) FROM ' . _DB_PREFIX_ . 'order_state';
            $this->figura = Db::getInstance()->getValue($sql);

            foreach ($languages as $language_atual) {

                Db::getInstance()->Execute
                        ('
			    INSERT INTO `' . _DB_PREFIX_ . 'order_state_lang` 
			    (`id_order_state`, `id_lang`, `name`, `template`)
			    VALUES
			    (' . $this->figura . ', ' . $language_atual['id_lang'] . ', \'' . $value[2] . '\', \'' . $value[3] . '\');
		        ');
            }



            $file = (dirname(__file__) . "/icons/$key.gif");
            $newfile = (dirname(dirname(dirname(__file__))) . "/img/os/$this->figura.gif");
            if (!copy($file, $newfile)) {
                return false;
            }

            Configuration::updateValue("mercadopago_STATUS_$key", $this->figura);
        }

        return true;
    }

    public function uninstall() {
        if(
                !Configuration::deleteByName('mercadopago_CLIENT_ID')
                OR !Configuration::deleteByName('mercadopago_CLIENT_SECRET')
		OR !Configuration::deleteByName('mercadopago_CATEGORY')
		OR !Configuration::deleteByName('mercadopago_TYPECHECKOUT')
                OR !Configuration::deleteByName('mercadopago_SANDBOX')
                OR !Configuration::deleteByName('mercadopago_URLPROCESS')
                OR !Configuration::deleteByName('mercadopago_URLSUCCESFULL')
                OR !Configuration::deleteByName('mercadopago_BTN')
		
                OR !Configuration::deleteByName('mercadopago_METHODS')
		OR !Configuration::deleteByName('mercadopago_INSTALLMENTS')
                OR !Configuration::deleteByName('mercadopago_COUNTRY')
                OR !parent::uninstall()
        ){
            return false;
        }

        return true;
    }

    public function getContent() {
        $this->_html = '<h2>MercadoPago</h2>';
	
        if (isset($_POST['submitmercadopago'])) {
            if (!sizeof($this->_postErrors)) {
		if (!empty($_POST['mercadopago_CLIENT_ID'])) {
                    Configuration::updateValue('mercadopago_CLIENT_ID', $_POST['mercadopago_CLIENT_ID']);
                }
		
                if (!empty($_POST['mercadopago_CLIENT_SECRET'])) {
                    Configuration::updateValue('mercadopago_CLIENT_SECRET', $_POST['mercadopago_CLIENT_SECRET']);
                }
		
		if (!empty($_POST['mercadopago_CATEGORY'])) {
                    Configuration::updateValue('mercadopago_CATEGORY', $_POST['mercadopago_CATEGORY']);
                }
		
		if (!empty($_POST['mercadopago_TYPECHECKOUT'])) {
                    Configuration::updateValue('mercadopago_TYPECHECKOUT', $_POST['mercadopago_TYPECHECKOUT']);
                }
		if (!empty($_POST['mercadopago_SANDBOX'])) {
                    Configuration::updateValue('mercadopago_SANDBOX', $_POST['mercadopago_SANDBOX']);
                }
		
                if (!empty($_POST['mercadopago_COUNTRY'])) {
                    Configuration::updateValue('mercadopago_COUNTRY', $_POST['mercadopago_COUNTRY']);
                }
		
		if (!empty($_POST['mercadopago_INSTALLMENTS'])) {
                    Configuration::updateValue('mercadopago_INSTALLMENTS', $_POST['mercadopago_INSTALLMENTS']);
                }
		
                if (!empty($_POST['mercadopago_METHODS'])) {
                    $methods = '';
                    foreach ($_POST['mercadopago_METHODS'] as $name) {
                        $methods .= $name . ',';
		    }
                    Configuration::updateValue('mercadopago_METHODS', $methods);
                }else{
		    //is empty
		    //force update checket
		    Configuration::updateValue('mercadopago_METHODS', "");
		}
		
                if (!empty($_POST['pg_url_retorno'])) {
                    Configuration::updateValue('mercadopago_URLPROCESS', $_POST['pg_url_retorno']);
                }
                if (!empty($_POST['pg_url_succesfull'])) {
                    Configuration::updateValue('mercadopago_URLSUCCESFULL', $_POST['pg_url_succesfull']);
                }
                $this->displayConf();
            }
            else
                $this->displayErrors();
        }
        elseif (isset($_POST['submitmercadopago_Btn'])) {
            Configuration::updateValue('mercadopago_BTN', $_POST['btn_pg']);
            $this->displayConf();
        } elseif (isset($_POST['submitmercadopago_Bnr'])) {
            Configuration::updateValue('mercadopago_BANNER', $_POST['banner_pg']);
            $this->displayConf();
        }

        $this->displaymercadopago();
        $this->displayFormSettingsmercadopago();
        return $this->_html;
    }

    public function displayConf() {
        $this->_html .= '
		<div class="conf confirm">
			' . $this->l('Configurações atualizadas') . '
		</div>';
    }

    public function displayErrors() {
        $nbErrors = sizeof($this->_postErrors);
        $this->_html .= '
		<div class="alert error">
			<h3>' . ($nbErrors > 1 ? $this->l('There are') : $this->l('There is')) . ' ' . $nbErrors . ' ' . ($nbErrors > 1 ? $this->l('errors') : $this->l('error')) . '</h3>
			<ol>';
        foreach ($this->_postErrors AS $error)
            $this->_html .= '<li>' . $error . '</li>';
        $this->_html .= '
			</ol>
		</div>';
    }

    public function displaymercadopago() {
        $this->_html .= '<div style="float:left;width:100%;margin: 0 0 20px 0;">';
	$this->_html .= '<img src="https://a248.e.akamai.net/secure.mlstatic.com/components/resources/mp/css/assets/desktop-logo-mercadopago.png" style="float:left; margin-right:15px;" />';
	$this->_html .= '</div>';
	$this->_html .= '<b>' . $this->l('Setup your account in MercadoPago.') . '</b><br /><br />';
	$this->_html .= $this->l('<b>Step 1:</b> Enter your Client_id, Client_Secret and Country, and Save') . '<br />';
	$this->_html .= $this->l('<b>Step 2:</b> If you wish, select the payment methods that you dont want to accept.') . '<br />';
	$this->_html .= $this->l('<b>Important:</b> If change the country, update the page before to chose the exclude payment methods. <br /><br />'); 
	$this->_html .= $this->l('<b>IPN - Instant Payment Notification</b></br> Setup in MercadoPago the url to receive, should be like <b>'._PS_BASE_URL_.'/modules/mercadopago/includes/retorno.php</b>') . '<br >';
	$this->_html .= '- <a href="https://www.mercadopago.com/mlb/ferramentas/notificacoes" target="_blank">Brasil</a><br />';
	$this->_html .= '- <a href="https://www.mercadopago.com/mla/herramientas/notificaciones" target="_blank">Argentina</a><br />';
	$this->_html .= '- <a href="https://www.mercadopago.com/mlm/herramientas/notificaciones" target="_blank">Mexico</a><br />';
	$this->_html .= '- <a href="https://www.mercadopago.com/mlv/herramientas/notificaciones" target="_blank">Venezuela</a><br />';
	$this->_html .= '<br /><br /><br />';
	
    }

    
    public function displayFormSettingsmercadopago() {
	
	//set MP Apis for request in api mercado pago
	$mp = new MPApi();
	
        $conf = Configuration::getMultiple(
	    array(
		'mercadopago_CLIENT_ID',
		'mercadopago_CLIENT_SECRET',
		'mercadopago_CATEGORY',
		'mercadopago_TYPECHECKOUT',
		'mercadopago_SANDBOX',
		'mercadopago_COUNTRY',
		'mercadopago_METHODS',
		'mercadopago_INSTALLMENTS',
		'mercadopago_URLPROCESS',
		'mercadopago_URLSUCCESFULL',
		'mercadopago_BTN',
		'mercadopago_BANNER'
            )
        );

        $client_id = array_key_exists('mercadopago_CLIENT_ID', $_POST) ? $_POST['mercadopago_CLIENT_ID'] : (array_key_exists('mercadopago_CLIENT_ID', $conf) ? $conf['mercadopago_CLIENT_ID'] : '');
        $client_secret = array_key_exists('mercadopago_CLIENT_SECRET', $_POST) ? $_POST['mercadopago_CLIENT_SECRET'] : (array_key_exists('mercadopago_CLIENT_SECRET', $conf) ? $conf['mercadopago_CLIENT_SECRET'] : '');
	$category =  array_key_exists('mercadopago_CATEGORY', $_POST) ? $_POST['mercadopago_CATEGORY'] : (array_key_exists('mercadopago_CATEGORY', $conf) ? $conf['mercadopago_CATEGORY'] : '');
	$type_checkout = array_key_exists('mercadopago_TYPECHECKOUT', $_POST) ? $_POST['mercadopago_TYPECHECKOUT'] : (array_key_exists('mercadopago_TYPECHECKOUT', $conf) ? $conf['mercadopago_TYPECHECKOUT'] : '');
	$sandbox = array_key_exists('mercadopago_SANDBOX', $_POST) ? $_POST['mercadopago_SANDBOX'] : (array_key_exists('mercadopago_SANDBOX', $conf) ? $conf['mercadopago_SANDBOX'] : '');
        $mercado_pago_country = array_key_exists('mercadopago_COUNTRY', $_POST) ? $_POST['mercadopago_COUNTRY'] : (array_key_exists('mercadopago_COUNTRY', $conf) ? $conf['mercadopago_COUNTRY'] : '');
        $mercadopago_method = array_key_exists('mercadopago_METHODS', $_POST) ? $_POST['mercadopago_METHODS'] : (array_key_exists('mercadopago_METHODS', $conf) ? preg_split("/[\s,]+/", $conf['mercadopago_METHODS']) : '');
	$installments = array_key_exists('mercadopago_INSTALLMENTS', $_POST) ? $_POST['mercadopago_INSTALLMENTS'] : (array_key_exists('mercadopago_INSTALLMENTS', $conf) ? $conf['mercadopago_INSTALLMENTS'] : '');
        $url_retorno = array_key_exists('pg_url_retorno', $_POST) ? $_POST['pg_url_retorno'] : (array_key_exists('mercadopago_URLPROCESS', $conf) ? $conf['mercadopago_URLPROCESS'] : '');
        $url_succesfull = array_key_exists('pg_url_succesfull', $_POST) ? $_POST['pg_url_succesfull'] : (array_key_exists('mercadopago_URLSUCCESFULL', $conf) ? $conf['mercadopago_URLSUCCESFULL'] : '');
        $btn = array_key_exists('btn_pg', $_POST) ? $_POST['btn_pg'] : (array_key_exists('mercadopago_BTN', $conf) ? $conf['mercadopago_BTN'] : '');
        $bnr = array_key_exists('banner_pg', $_POST) ? $_POST['banner_pg'] : (array_key_exists('mercadopago_BANNER', $conf) ? $conf['mercadopago_BANNER'] : '');




	//category marketplace
	$list_category = $mp->getCategories();
	$select_category = '<select name="mercadopago_CATEGORY" id="category">';
	foreach($list_category as $category_arr):
	
	    $selected = "";
	    if($category_arr['id'] == $category):
		$selected = 'selected="selected"';
	    endif;
	    
	    $select_category .= '<option value="' . $category_arr['id'] . '" id="type-checkout-' . $category_arr['description'] . '" ' . $selected . ' >' . $category_arr['description'] . '</option>';
	endforeach;
	$select_category .= "</select>";
	
	//Type Checkout
	$type_checkout_options = array(
		"Iframe",
		"Lightbox",
		"Redirect"
	);
	
	$select_type_checkout = '<select name="mercadopago_TYPECHECKOUT" id="type_checkout">';
	foreach($type_checkout_options as $select_type):
	
	    $selected = "";
	    if($select_type == $type_checkout):
		$selected = 'selected="selected"';
	    endif;
	    
	    $select_type_checkout .= '<option value="' . $select_type . '" id="type-checkout-' . $select_type . '" ' . $selected . ' >' . $select_type . '</option>';
	endforeach;
	$select_type_checkout .= "</select>";

	
	//sandbox
	$sandbox_options = array(
		array("value" => "active", "text" => "Active"),
		array("value" => "deactivate", "text" => "Deactivate")
	);
	
	$select_sandbox = '<select name="mercadopago_SANDBOX" id="sandbox">';
	foreach($sandbox_options as $op_sandbox):
	
	    $selected = "";
	    if($op_sandbox['value'] == $sandbox):
		$selected = 'selected="selected"';
	    endif;
	    
	    $select_sandbox .= '<option value="' . $op_sandbox['value'] . '" id="sandbox-' . $op_sandbox['value'] . '" ' . $selected . '>' . $op_sandbox['text'] . '</option>';
	endforeach;
	$select_sandbox .= "</select>";
	
	
	//Get countries
        $countries = $mp->getCountries();
        $showcountries = '<select name="mercadopago_COUNTRY" id="country">';
        foreach ($countries as $country) {
            if ($country['id'] == $mercado_pago_country) {
                $showcountries .= '<option value="' . $country["id"] . '" selected="selected" id="' . $country["id"] . '">' . $country["name"] . '</option>';
            } else {
                $showcountries .= '<option value="' . $country['id'] . '" id="' . $country["id"] . '">' . $country["name"] . '</option>';
            }
        }
        $showcountries .= '</select>';
	
	//installments limit
	$qty = array(2,3,4,5,6,9,10,12,15,18,24);
	
	$select_installments = '<select name="mercadopago_INSTALLMENTS" id="sandbox">';
	foreach($qty as $inst):
	
	    $selected = "";
	    if("inst-".$inst == $installments):
		$selected = 'selected="selected"';
	    endif;
	    
	    $select_installments .= '<option value="inst-' . $inst . '" id="sandbox-' . $inst . '" ' . $selected . '>' . $inst . '</option>';
	endforeach;
	$select_installments .= "</select>";
	
	//Get methods exclude according the country
        $mercadopago_methods = array();
        if (is_array($mercadopago_method)) {
            foreach ($mercadopago_method as $exclude) {
                $mercadopago_methods[] = $exclude;
            };
        }
	
	//echo "<pre>";
	//print_r($_REQUEST);
	//echo Configuration::get('mercadopago_METHODS') . "<br />";
	
        if ($mercado_pago_country != '' || $mercado_pago_country != null) {
            $methods = $mp->getPaymentMethods($mercado_pago_country);
            $showmethods = '';
            foreach ($methods as $method):
                if ($method['id'] != 'account_money') {
                    if ($mercadopago_methods != null && in_array($method['id'], $mercadopago_methods)) {
                        $showmethods .= ' <input name="mercadopago_METHODS[]" type="checkbox" checked="yes" value="' . $method['id'] . '">' . $method['name'] . '<br />';
                    } else {
                        $showmethods .= '<input name="mercadopago_METHODS[]" type="checkbox" value="' . $method['id'] . '"> ' . $method['name'] . '<br />';
                    }
                }
            endforeach;
        } else {
            $showmethods = 'Select first one country, save and reload the page to show the methods';
        }


        $this->_html .= '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />' . $this->l('Configurações') . '</legend>
			<label>' . $this->l('Client ID') . ':</label>
			<div class="margin-form"><input type="text" size="33" name="mercadopago_CLIENT_ID" value="' . htmlentities($client_id, ENT_COMPAT, 'UTF-8') . '" /><a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank" > <b>Bra</b></a>|<a href="http://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank" ><b>Arg</b></a>|<a href="http://www.mercadopago.com/mlm/herramientas/aplicaciones" target="_blank" ><b>Mex</b></a>|<a href="http://www.mercadopago.com/mlv/herramientas/aplicaciones" target="_blank" ><b>Ven</b></a></div>
                        <br />
			
			<label>' . $this->l('Client Secret') . ':</label>
			<div class="margin-form"><input type="text" size="33" name="mercadopago_CLIENT_SECRET" value="' . $client_secret . '" /><a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank" > <b>Bra</b></a>|<a href="http://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank" ><b>Arg</b></a>|<a href="http://www.mercadopago.com/mlm/herramientas/aplicaciones" target="_blank" ><b>Mex</b></a>|<a href="http://www.mercadopago.com/mlv/herramientas/aplicaciones" target="_blank" ><b>Ven</b></a></div>
			<br />
                        
			<label>' . $this->l('Category') . ':</label>
			<div class="margin-form">' . $select_category . '</div>
			<br />
			
			<label>' . $this->l('Type Checkout') . ':</label>
			<div class="margin-form">' . $select_type_checkout . '</div>
			<br />

			<label>' . $this->l('Sandbox') . ':</label>
			<div class="margin-form">' . $select_sandbox . '</div>
			<br />
			
                        <label>' . $this->l('Country') . ':</label>
			<div class="margin-form">' . $showcountries . '</div>
			<br />
                    
                        <label>' . $this->l('Installments Limit') . ':</label>
			<div class="margin-form">' . $select_installments . '</div>
			<br />
			
                        <label>' . $this->l('Exclude methods') . ':</label>
			<div class="margin-form">' . $showmethods . '</div>
			<br />
                        
                        <label>' . $this->l('Url Process Payment') . ':</label>
			<div class="margin-form"><input type="text" size="33" name="pg_url_retorno" value="' . $url_retorno . '" /></div>
			<br />
			
			<label>' . $this->l('URL Aproved Payment') . ':</label>
			<div class="margin-form"><input type="text" size="33" name="pg_url_succesfull" value="' . $url_succesfull . '" /></div>
			<br />
			
			<center><input type="submit" name="submitmercadopago" value="' . $this->l('Atualizar') . '" class="button" /></center>
		</fieldset>
		</form>';

        $this->_html .= '
		
		</center>
		</fieldset>
		</form>';
    }
    
    //STEP - Select type method payment
    public function hookPayment($params) {
	
        global $smarty;
	
	//Send variables to payment.tpl
        $smarty->assign(
		array(
		    'imgBtn' => "modules/mercadopago/imagens/logo.gif",
		    'imgBannerSelectPayment' => $this->getBannerSelectPayment(),
		    'this_path' => $this->_path, 'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ?
		    'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
		)
	    );
	
        return $this->display(__file__, 'payment.tpl');
    }

    //STEP - Confirm method payment selected
    public function execPayment($cart) {

        global $cookie, $smarty;
        $invoiceAddress = new Address(intval($cart->id_address_invoice));
        $customerPag = new Customer(intval($cart->id_customer));
        $currencies = Currency::getCurrencies();
        $currencies_used = array();
        $currency = $this->getCurrency();
        $currencies = Currency::getCurrencies();
	
        foreach ($currencies as $key => $currency)
            $smarty->assign(
		array(
                'currency_default' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                'currencies' => $currencies_used,
                'imgBanner' => $this->getBanner(),
                'currency_default' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                'currencies' => $currencies_used,
                'total' => number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency), 2, '.', ''),
                'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
		)
	    );

        return $this->display(__file__, 'confirm.tpl');
    }

    //STEP - generate link to pay
    public function hookPaymentReturn($params) {
        
	global $cookie, $smarty;
        // dados do cliente
        $customer = new Customer(intval($cookie->id_customer));
        $ArrayCliente = $customer->getFields();
	
        // dados do pedido
        $DadosOrder = new Order($params['objOrder']->id);
        $ArrayListaProdutos = $DadosOrder->getProducts();

	//Get shipment
	$address_delivery = new Address(intval($params['cart']->id_address_delivery));
        $shipments = array(
            "receiver_address" => array(
            "floor" => "-",
            "zip_code" => $address_delivery->postcode,
            "street_name" => $address_delivery->address1 . " - " . $address_delivery->address2 . " - " . $address_delivery->city. "/" . $address_delivery->country,
            "apartment" => "-",
            "street_number" => "-"
            )
        );
	
        //Force format YYYY-DD-MMTH:i:s
        $date_creation_user = date('Y-m-d', strtotime($ArrayCliente['date_add'])) . "T" . date('H:i:s',strtotime($ArrayCliente['date_add']));
	$address_invoice = new Address(intval($params['cart']->id_address_invoice));
	
	$phone = $address_invoice->phone;
	$phone .= $phone == "" ? "" : "|";
	$phone .= $address_invoice->phone_mobile;
	
        $payer = array(
            "name" => $ArrayCliente['firstname'],
            "surname" => $ArrayCliente['lastname'],
            "email" => $ArrayCliente['email'],
            "date_created" => $date_creation_user,
            "phone" => array(
                "area_code" => "-",
                "number" => $phone
            ),
            "address" => array(
                "zip_code" => $address_invoice->postcode,
		"street_name" => $address_invoice->address1 . " - " . $address_delivery->address2 . " - " . $address_delivery->city. "/" . $address_delivery->country,
                "street_number" => "-"
            ),
            "identification" => array(
                "number" => "null",
                "type" => "null"
            )
        );
        
        //items
	$image_url = "";
        // gera descrição
        foreach ($ArrayListaProdutos as $info) {
            $item = array(
                // Cria um Array com a descri?�?�es dos produtos
                $zb[] = $info['product_name'] . ' * ' . $info['product_quantity']
            );
	    
	    //get object image on product object
	    $id_image = $info['image'];
	    
	    // get Image by id
	    if (sizeof($id_image) > 0) {
		$image = new Image($id_image->id_image);
		// get image full URL
		$image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".".$image->image_format;
	    }
        }
	
        $descricao = implode(" + ", $zb);
        $item_price = number_format($params['total_to_pay'], 2, '.', '');
	$currency = new Currency($DadosOrder->id_currency);
        $items = array(
            array (
            "id" => $params['objOrder']->id,
            "title" => utf8_encode($descricao),
            "description" => utf8_encode($descricao),
            "quantity" => 1,
            "unit_price" => round($item_price, 2),
            "currency_id" => $currency->iso_code,
            "picture_url"=> $image_url,
            "category_id"=> Configuration::get('mercadopago_CATEGORY')
            )
        );
	
        //excludes_payment_methods
	$exclude = Configuration::get('mercadopago_METHODS');
	$installments = Configuration::get('mercadopago_INSTALLMENTS');
	$installments = str_replace("inst-", "", $installments);
	$installments = (int)$installments;
	
        if($exclude != ''):
	
	    //case exist exclude methods
            $methods_excludes = preg_split("/[\s,]+/", $exclude);
	    $excludemethods = array();
	    foreach ($methods_excludes as $exclude ){
		if($exclude != "")
		   $excludemethods[] = array('id' => $exclude);     
	    }
        
            $payment_methods = array(
                "installments" => $installments,
                "excluded_payment_methods" => $excludemethods
            );
        else:
            //case not exist exclude methods
            $payment_methods = array(
                "installments" => $installments
            );
        endif;
        
        
        //set back url
        $back_urls = array(
            "pending" => Configuration::get('mercadopago_URLPROCESS'),
            "success" => Configuration::get('mercadopago_URLSUCCESFULL')
        );
        
        
        //mount array pref
        $pref = array();
        $pref['external_reference'] = $params['objOrder']->id;
        $pref['payer'] = $payer;
        $pref['shipments'] = $shipments;
        $pref['items'] = $items;
        $pref['back_urls'] = $back_urls;
        $pref['payment_methods'] = $payment_methods;
	
        $client_id = Configuration::get('mercadopago_CLIENT_ID');
        $client_secret = Configuration::get('mercadopago_CLIENT_SECRET');

	$mp = new MP ($client_id, $client_secret);
	$preferenceResult = $mp->create_preference($pref);
	
	$sandbox = Configuration::get('mercadopago_SANDBOX') == "active" ? true:false;
	
	$url = "";
	if($sandbox):
	    $url = $preferenceResult['response']['sandbox_init_point'];
	else:
	    $url = $preferenceResult['response']['init_point'];
	endif;
	
	switch(Configuration::get('mercadopago_TYPECHECKOUT')):
	    case "Iframe":
		$botton = '
		    <iframe src="' . $url . '" name="MP-Checkout" width="740" height="600" frameborder="0"></iframe>
		    <script type="text/javascript">
			(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;
			s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"http://mp-tools.mlstatic.com/buttons/")+"render.js";
			var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}
			window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false)) : null;})();
		    </script>
		';
		
		break;
	    case "Redirect":
		header("location: " . $url );
		break;
	    case "Lightbox":
	    default:
		$botton = '
		    <a href="' . $url . '" name="MP-Checkout" class="blue-L-Rn" mp-mode="modal" onreturn="execute_my_onreturn">Pagar</a>
		    <script type="text/javascript">
			(function(){function $MPBR_load(){window.$MPBR_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;
			s.src = ("https:"==document.location.protocol?"https://www.mercadopago.com/org-img/jsapi/mptools/buttons/":"http://mp-tools.mlstatic.com/buttons/")+"render.js";
			var x = document.getElementsByTagName("script")[0];x.parentNode.insertBefore(s, x);window.$MPBR_loaded = true;})();}
			window.$MPBR_loaded !== true ? (window.attachEvent ? window.attachEvent("onload", $MPBR_load) : window.addEventListener("load", $MPBR_load, false)) : null;})();
		    </script>
		';
		
		break;
	endswitch;
	
        $smarty->assign(array(
            'totalApagar' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
            'status' => 'ok',
            'seller_op_id' => $params['objOrder']->id,
            'secure_key' => $params['objOrder']->secure_key,
            'id_module' => $this->id,
            'formmercadopago' => $botton,
            'imgBanner' => $this->getBanner()
        ));

        return $this->display(__file__, 'payment_return.tpl');
    }

    function hookHome($params) {
        include(dirname(__FILE__) . '/includes/retorno.php');
    }

    function getStatus($param) {
        global $cookie;

        $sql_status = Db::getInstance()->Execute
                ('
			SELECT `name`
			FROM `' . _DB_PREFIX_ . 'order_state_lang`
			WHERE `id_order_state` = ' . $param . '
			AND `id_lang` = ' . $cookie->id_lang . '
			
		');

        return mysql_result($sql_status, 0);
    }

    public function enviar($mailVars, $template, $assunto, $DisplayName, $idCustomer, $idLang, $CustMail, $TplDir) {

        Mail::Send
                (intval($idLang), $template, $assunto, $mailVars, $CustMail, null, null, null, null, null, $TplDir);
    }

    public function getUrlByMyOrder($myOrder) {

        $module = Module::getInstanceByName($myOrder->module);
        $pagina_qstring = __PS_BASE_URI__ . "order-confirmation.php?id_cart="
                . $myOrder->id_cart . "&id_module=" . $module->id . "&id_order="
                . $myOrder->id . "&key=" . $myOrder->secure_key;

        if ($_SERVER['HTTPS'] != "on")
            $protocolo = "http";

        else
            $protocolo = "https";

        $retorno = $protocolo . "://" . $_SERVER['SERVER_NAME'] . $pagina_qstring;
        return $retorno;
    }

    
    public function getBannerSelectPayment(){
	$country = Configuration::get('mercadopago_COUNTRY');

        switch ($country):
            CASE ('MLA'):
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/125X125.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="125" height="125"/>';
                break;
            CASE ('MLM'):
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_125X125.JPG" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="125" height="125"/>';
                break;
            CASE ('MLV'):
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ve/medios/125X125.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="125" height="125"/>';
                break;
	    CASE ('MLB'):
            default :
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_125X125.jpg" alt="MercadoPago - Meios de pagamento" title="MercadoPago - Meios de pagamento" width="125" height="125"/>';
                break;
        endswitch;
	
	return $banner;
    }
    
    public function getBanner(){
	$country = Configuration::get('mercadopago_COUNTRY');

        switch ($country):
            CASE ('MLA'):
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>" />';
                break;
            CASE ('MLM'):
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/mx/medios/MLM_468X60.JPG" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>';
                break;
            CASE ('MLV'):
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/banners/ve/medios/468X60.jpg" title="MercadoPago - Medios de pago" alt="MercadoPago - Medios de pago" width="468" height="60"/>';
                break;
	    CASE ('MLB'):
            default :
                $banner = '<img src="http://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_468X60.jpg" alt="MercadoPago - Meios de pagamento" title="MercadoPago - Meios de pagamento" width="468" height="60"/>';
                break;
        endswitch;
	
	return $banner;
    }
}

?>