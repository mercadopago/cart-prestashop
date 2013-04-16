<?php

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
        //  $this->tab 			= 'Payment';
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
                OR !Configuration::updateValue('mercadopago_COUNTRY', '')
                OR !Configuration::updateValue('mercadopago_EXCLUDE', '')
                OR !Configuration::updateValue('mercadopago_URLPROCESS', 'http://www.sualoja.com.br/history.php')
                OR !Configuration::updateValue('mercadopago_URLSUCCESFULL', 'http://www.sualoja.com.br/history.php')
                OR !Configuration::updateValue('mercadopago_BTN', 0)
                OR !$this->registerHook('payment')
                OR !$this->registerHook('paymentReturn')
        )
            return false;

        return true;
    }

    public function create_states() {

        $this->order_state = array(
            array('ccfbff', '00100', 'MercadoPago - Transa?�??o em Andamento', ''),
            array('c9fecd', '11110', 'MercadoPago - Transa?�??o Conclu?�da', 'payment'),
            array('fec9c9', '11110', 'MercadoPago - Transa?�??o Cancelada', 'order_canceled'),
            array('fec9c9', '11110', 'MercadoPago - Transa?�??o Rejeitada', 'payment_error')
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
        if
        (
                !Configuration::deleteByName('mercadopago_CLIENT_ID')
                OR !Configuration::deleteByName('mercadopago_CLIENT_SECRET')
                OR !Configuration::deleteByName('mercadopago_URLPROCESS')
                OR !Configuration::deleteByName('mercadopago_URLSUCCESFULL')
                OR !Configuration::deleteByName('mercadopago_BTN')
                OR !Configuration::deleteByName('mercadopago_EXCLUDE')
                OR !Configuration::deleteByName('mercadopago_COUNTRY')
                OR !parent::uninstall()
        )
            return false;

        return true;
    }

    public function getContent() {
        $this->_html = '<h2>MercadoPago</h2>';
        if (isset($_POST['submitmercadopago'])) {


            if (!sizeof($this->_postErrors)) {
                Configuration::updateValue('mercadopago_CLIENT_ID', $_POST['mercadopago_CLIENT_ID']);
                if (!empty($_POST['mercadopago_CLIENT_ID'])) {
                    Configuration::updateValue('mercadopago_CLIENT_ID', $_POST['mercadopago_CLIENT_ID']);
                }
                if (!empty($_POST['mercadopago_CLIENT_SECRET'])) {
                    Configuration::updateValue('mercadopago_CLIENT_SECRET', $_POST['mercadopago_CLIENT_SECRET']);
                }
                if (!empty($_POST['mercadopago_COUNTRY'])) {
                    Configuration::updateValue('mercadopago_COUNTRY', $_POST['mercadopago_COUNTRY']);
                }
                if (!empty($_POST['mercadopago_METHODS'])) {
                    $methods = '';
                    foreach ($_POST['mercadopago_METHODS'] as $name) {
                        $methods .= $name . ',';
                    }
                    Configuration::updateValue('mercadopago_METHODS', $methods);
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
			<img src="../img/admin/ok.gif" alt="' . $this->l('Confirmation') . '" />
			' . $this->l('Configura?�?�es atualizadas') . '
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
        $this->_html .= '
		<img src="../modules/mercadopago/imagens/mercadopago.jpg" style="float:left; margin-right:15px;" />
		<b>' . $this->l('Setup your account in MercadoPago.') . '</b><br /><br />
		' . $this->l('<b>Step 1:</b> Enter your Client_id, Client_Secret and Country, and Save') . '<br />
            	' . $this->l('<b>Step 2:</b> If you wish, select the payment methods that you dont want to accept.') . '<br />
                ' . $this->l('<b>Important:</b> If change the country, update the page before to chose the exclude payment methods. <br /><br />') . '	
                   
                 ' . $this->l('<b>IPN - Instant Payment Notification</b></br> Setup in MercadoPago the url to receive, should be like [yourstoreadress.com]<b>/modules/mercadopago/includes/retorno.php</b>') . '<br >
                 -><a href="https://www.mercadopago.com/mlb/ferramentas/notificacoes" target="_blank">Brasil</a>|<a href="https://www.mercadopago.com/mla/herramientas/notificaciones" target="_blank">Argentina</a>|<a href="https://www.mercadopago.com/mlm/herramientas/notificaciones" target="_blank">Mexico</a>|<a href="https://www.mercadopago.com/mlv/herramientas/notificaciones" target="_blank">Venezuela</a>
                 <br /><br /><br />';
    }

    public function displayFormSettingsmercadopago() {
        include(dirname(__FILE__) . '/includes/Shop.php');
        $conf = Configuration::getMultiple
                        (array(
                    'mercadopago_CLIENT_ID',
                    'mercadopago_CLIENT_SECRET',
                    'mercadopago_COUNTRY',
                    'mercadopago_METHODS',
                    'mercadopago_URLPROCESS',
                    'mercadopago_URLSUCCESFULL',
                    'mercadopago_BTN',
                    'mercadopago_BANNER'
                        )
        );

        $client_id = array_key_exists('mercadopago_CLIENT_ID', $_POST) ? $_POST['mercadopago_CLIENT_ID'] : (array_key_exists('mercadopago_CLIENT_ID', $conf) ? $conf['mercadopago_CLIENT_ID'] : '');
        $client_secret = array_key_exists('mercadopago_CLIENT_SECRET', $_POST) ? $_POST['mercadopago_CLIENT_SECRET'] : (array_key_exists('mercadopago_CLIENT_SECRET', $conf) ? $conf['mercadopago_CLIENT_SECRET'] : '');
        $mercado_pago_country = array_key_exists('mercadopago_COUNTRY', $_POST) ? $_POST['mercadopago_COUNTRY'] : (array_key_exists('mercadopago_COUNTRY', $conf) ? $conf['mercadopago_COUNTRY'] : '');
        $mercadopago_method = array_key_exists('mercadopago_METHODS', $_POST) ? $_POST['mercadopago_METHODS'] : (array_key_exists('mercadopago_METHODS', $conf) ? preg_split("/[\s,]+/", $conf['mercadopago_METHODS']) : '');
        $url_retorno = array_key_exists('pg_url_retorno', $_POST) ? $_POST['pg_url_retorno'] : (array_key_exists('mercadopago_URLPROCESS', $conf) ? $conf['mercadopago_URLPROCESS'] : '');
        $url_succesfull = array_key_exists('pg_url_succesfull', $_POST) ? $_POST['pg_url_succesfull'] : (array_key_exists('mercadopago_URLSUCCESFULL', $conf) ? $conf['mercadopago_URLSUCCESFULL'] : '');
        $btn = array_key_exists('btn_pg', $_POST) ? $_POST['btn_pg'] : (array_key_exists('mercadopago_BTN', $conf) ? $conf['mercadopago_BTN'] : '');
        $bnr = array_key_exists('banner_pg', $_POST) ? $_POST['banner_pg'] : (array_key_exists('mercadopago_BANNER', $conf) ? $conf['mercadopago_BANNER'] : '');


        $mercadopago_methods = array();
        if (is_array($mercadopago_method)) {
            foreach ($mercadopago_method as $exclude) {
                $mercadopago_methods[] = $exclude;
            };
        }
        $mp = new Mpublic();
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

        if ($mercado_pago_country != '' || $mercado_pago_country != null) {
            $methods = $mp->GetMethods($mercado_pago_country);
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
			<legend><img src="../img/admin/contact.gif" />' . $this->l('Configura?�?�es') . '</legend>
			<label>' . $this->l('Client ID') . ':</label>
			<div class="margin-form"><input type="text" size="33" name="mercadopago_CLIENT_ID" value="' . htmlentities($client_id, ENT_COMPAT, 'UTF-8') . '" /><a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank" > <b>Bra</b></a>|<a href="http://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank" ><b>Arg</b></a>|<a href="http://www.mercadopago.com/mlm/herramientas/aplicaciones" target="_blank" ><b>Mex</b></a>|<a href="http://www.mercadopago.com/mlv/herramientas/aplicaciones" target="_blank" ><b>Ven</b></a></div>
			
                        <br />
			
			<label>' . $this->l('Client Secret') . ':</label>
			<div class="margin-form"><input type="text" size="33" name="mercadopago_CLIENT_SECRET" value="' . $client_secret . '" /><a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes" target="_blank" > <b>Bra</b></a>|<a href="http://www.mercadopago.com/mla/herramientas/aplicaciones" target="_blank" ><b>Arg</b></a>|<a href="http://www.mercadopago.com/mlm/herramientas/aplicaciones" target="_blank" ><b>Mex</b></a>|<a href="http://www.mercadopago.com/mlv/herramientas/aplicaciones" target="_blank" ><b>Ven</b></a></div>
			<br />
                        
                        <label>' . $this->l('Country') . ':</label>
			<div class="margin-form">' . $showcountries . '</div>
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

    private function getCountries() {

        $header = '';
        $url = 'https://api.mercadolibre.com/sites/';

        $this->DoPost(null, $url, $header, '200', 'none', 'get');
        $countries = $this->callJson($url);

        return $countries;
    }

    public function execPayment($cart) {

        global $cookie, $smarty;
        $invoiceAddress = new Address(intval($cart->id_address_invoice));
        $customerPag = new Customer(intval($cart->id_customer));
        $currencies = Currency::getCurrencies();
        $currencies_used = array();
        $currency = $this->getCurrency();
        $currencies = Currency::getCurrencies();

        $country = Configuration::get('mercadopago_COUNTRY');

        switch ($country):
            CASE ('MLA'):
                $banner = 'imagens/mercadopagoar.jpg';
                break;
            CASE ('MLB'):
                $banner = 'imagens/mercadopagobr.jpg';
                break;
            CASE ('MLV'):
                $banner = 'imagens/mercadopagov.jpg';
                break;
            CASE ('MLM'):
                $banner = 'imagens/mercadopagomx.jpg';
                break;
            default :
                $banner = 'imagens/mercadopagobr.jpg';
        endswitch;

        foreach ($currencies as $key => $currency)
            $smarty->assign(array(
                'currency_default' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                'currencies' => $currencies_used,
                'imgBtn' => "mercadopago.jpg",
                'imgBnr' => $banner,
                'currency_default' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
                'currencies' => $currencies_used,
                'total' => number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency), 2, '.', ''),
                'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ?
                        'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'));

        return $this->display(__file__, 'confirm.tpl');
    }

    public function hookPayment($params) {

        include(dirname(__FILE__) . '/includes/Shop.php');

        global $smarty;
        $smarty->assign(array(
            'imgBtn' => "modules/mercadopago/imagens/logo.gif",
            'this_path' => $this->_path, 'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ?
                    'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'));
        return $this->display(__file__, 'payment.tpl');
    }

    public function hookPaymentReturn($params) {
        global $cookie, $smarty;
        include(dirname(__FILE__) . '/includes/Shop.php');
        // dados do cliente
        $customer = new Customer(intval($cookie->id_customer));
        $ArrayCliente = $customer->getFields();

        // dados do pedido
        $DadosOrder = new Order($params['objOrder']->id);
        $ArrayListaProdutos = $DadosOrder->getProducts();

        // gera descri�?o
        foreach ($ArrayListaProdutos as $info) {
            $item = array(
                // Cria um Array com a descri?�?�es dos produtos
                $zb[] = $info['product_name'] . ' * ' . $info['product_quantity']
            );
        }
        $descricao = implode(" + ", $zb);
        $currency = new Currency($DadosOrder->id_currency);


        $dados = array(
            "external_reference" => $params['objOrder']->id, // seu codigo de referencia, i.e. Numero do pedido da sua loja 
            "currency" => $currency->iso_code, // string Argentina: ARS (peso argentino) � USD (D�lar estadounidense); Brasil: BRL (Real).
            "title" => $descricao, //string
            "description" => $descricao, // string
            'quantity' => 1, // int 
            'image' => '', // Imagem, string
            'amount' => $params['total_to_pay'], //decimal
            'payment_firstname' => $ArrayCliente['firstname'], // string
            'payment_lastname' => $ArrayCliente['lastname'], // string
            'email' => $ArrayCliente['email'], // string
            'pending' => Configuration::get('mercadopago_URLPROCESS'),
            'approved' => Configuration::get('mercadopago_URLSUCCESFULL')
        );
        $client = Configuration::get('mercadopago_CLIENT_ID');
        $secret = Configuration::get('mercadopago_CLIENT_SECRET');
        $exclude = Configuration::get('mercadopago_METHODS');

        $pagamento = New MPShop($client, $secret);
        $botton = $pagamento->GetCheckout($dados, $exclude);

        $country = Configuration::get('mercadopago_COUNTRY');
        switch ($country):
            CASE ('MLA'):
                $banner = 'modules/mercadopago/imagens/mercadopagoar.jpg';
                break;
            CASE ('MLB'):
                $banner = 'modules/mercadopago/imagens/mercadopagobr.jpg';
                break;
            CASE ('MLM'):
                $banner = 'modules/mercadopago/imagens/mercadopagomx.jpg';
                break;
            CASE ('MLV'):
                $banner = 'modules/mercadopago/imagens/mercadopagov.jpg';
                break;
            default :
                $banner = 'modules/mercadopago/imagens/mercadopagobr.jpg';
        endswitch;


        $smarty->assign(array(
            'totalApagar' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
            'status' => 'ok',
            'seller_op_id' => $params['objOrder']->id,
            'secure_key' => $params['objOrder']->secure_key,
            'id_module' => $this->id,
            'formmercadopago' => $botton,
            'imgBnr' => $banner
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

}

?>