{**
* 2007-2015 PrestaShop
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
*  @author    MercadoPago
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of MercadoPago
*}
<style>
.important  {
	color: red;
	font-family: verdana;
	font-size: 100%;
}
</style>

<div class="mp-module">
	<div>
		<div id="alerts">
			{if $success eq 'true' and $errors|@count == 0}
			<div id="alert" class="bootstrap">
				<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert">×</button>
					{l s='Settings changed successfully.' mod='mercadopago'}
				</div>
			</div>
			{/if}
			{if $errors|@count > 0}
				<div class="bootstrap">
					<div class="alert alert-danger">
						<button type="button" class="close" data-dismiss="alert">×</button>
						{l s='Settings failed to change.' mod='mercadopago'}
					</div>
				</div>
				{foreach from=$errors item=error}
				<div class="bootstrap">
					<div class="alert alert-danger">
						<button type="button" class="close" data-dismiss="alert">×</button>
						{$error|escape:'htmlall':'UTF-8'}
					</div>
				</div>
				{/foreach}
			{/if}
		</div>
	<img class="logo" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo_large.png">
	<br>
	<ul class="tab">
		<li><a href="javascript:void(0)" class="tablinksSettings" onclick="openTabSettings(event, 'Requisitos')" id="defaultOpen">{l s='Requirements' mod='mercadopago'}</a></li>
		<li><a href="javascript:void(0)" class="tablinksSettings" onclick="openTabSettings(event, 'Duvidas')">{l s='Question' mod='mercadopago'}</a></li>
	</ul>

	<div id="Requisitos" class="tabcontentSettings">
	 	<h3>{l s='Requirements' mod='mercadopago'}</h3>
		<p>
			<strong>{l s='Installed Curl' mod='mercadopago'}:</strong><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.curl|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>

		{if $MERCADOENVIOS_ACTIVATE eq 'true'}
		<p>
			<strong>{l s='Dimensions of the product registered' mod='mercadopago'}:</strong> <img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.dimensoes|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>
		{/if}
		<p>
			<strong>{l s='Installed SSL' mod='mercadopago'}:</strong><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.ssl|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>
		<p>
			<strong>{l s='PHP Version' mod='mercadopago'}:</strong><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.version|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>
	</div>

	<div id="Duvidas" class="tabcontentSettings">
	  	<h3>{l s='Question' mod='mercadopago'}</h3>
		<p><strong><a href="https://www.youtube.com/playlist?list=PLl8LGzRu2_sXxChIJm1e0xY6dU3Dj_tNi" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/youtube.png" width="20px;" height="20px">YouTube</a> </strong></p>
		<p><strong><a href="https://www.facebook.com/groups/modulos.mercadopago" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/facebook.png" width="20px;" height="20px">Facebook</a> </strong></p>
		<p><a href="https://www.mercadopago.com.br/developers/pt/solutions/payments/basic-checkout/test/test-payments/" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/credit_card.png" width="20px;" height="20px">{l s='Credit Card for test' mod='mercadopago'}</a></p>
		<p><a href="https://www.mercadopago.com.br/developers/en/support" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/douts.png" width="20px;" height="20px">{l s='Suporte Mercado Pago' mod='mercadopago'}</a></p>		

        <ps-label-information label="{l s='Video Tutorial' mod='mercadopago'}">
			{if $country == 'MLB'}
				<iframe width="100%" style="max-width:560px" height="315" src="https://www.youtube.com/embed/Rsotj_9paOw" frameborder="0" allowfullscreen></iframe>
			{else}
				<iframe width="100%" style="max-width:560px" height="315" src="https://www.youtube.com/embed/rtXNkdaqUJ8" frameborder="0" allowfullscreen></iframe>
			{/if}
        </ps-label-information>
		
		<h4>{l s='Notification URL' mod='mercadopago'}</h4>
		<p><li>{l s='Notification URL' mod='mercadopago'}: "{$notification_url|escape:'htmlall':'UTF-8'}"</li> </p>
		<h4>{l s='Mercado Pago Log' mod='mercadopago'}</h4>
		<p>
			<li><a href="{$log|escape:'htmlall':'UTF-8'}" download>"{l s='Download' mod='mercadopago'}"</a></li>
		</p>
	</div>
	<br>

	<ul class="tab">
		{foreach from=array('Global', 'Basic', 'Custom') item=tab}
			<li>
				{if $active_tab == $tab}
					<a href="javascript:void(0)" class="tablinks" onclick="openTab(event, '{$tab|escape:'htmlall':'UTF-8'}')" id="defaultTab">
						{l s="`$tab` Checkout" mod='mercadopago'}
					</a>
				{else}
					<a href="javascript:void(0)" class="tablinks" onclick="openTab(event, '{$tab|escape:'htmlall':'UTF-8'}')">
						{l s="`$tab` Checkout" mod='mercadopago'}
					</a>
				{/if}
			</li>
		{/foreach}
	</ul>

	<div id="Global" class="tabcontent">
		<form action="{$uri|escape:'htmlall':'UTF-8'}" method="post">
			<h2>{l s='Status Checkouts' mod='mercadopago'}</h2>
			<p>
				<label> Checkout Standard:</label>
				<strong>
					{if $standard_active == 'true'}
						{l s='Enabled' mod='mercadopago'}
					{else}
						{l s='Disabled' mod='mercadopago'}
					{/if}
				</strong>
			</p>
			<p>
				<label> Checkout Custom:</label>
				<strong>
					{if $custom_active == 'true'}
						{l s='Enabled' mod='mercadopago'}
					{else}
						{l s='Disabled' mod='mercadopago'}
					{/if}
				</strong>
			</p>
			<p>
				{if !empty($country)}
					<span><label>{l s='Category:' mod='mercadopago'}</label></span>
					<span>
						<select name="MERCADOPAGO_CATEGORY" id="category">
							{foreach from=$categories key=value item=name}
								<option value="{$value|escape:'htmlall':'UTF-8'}">{l s=$name mod='mercadopago'}</option>
							{/foreach}
						</select>
					</span>
				{/if}
			</p>
			<p>
				{if !empty($country)}
					<span><label>{l s='Sponsor ID:' mod='mercadopago'}</label></span>
					<span>
						<input type="number" size="33" name="SPONSOR_ID" value="{$sponsor_id|escape:'htmlall':'UTF-8'}" />
					</span>
				{/if}
			</p>			
			<input type="submit"
						 name="save_general"
						 value="{l s='Save General Settings' mod='mercadopago'}"
						 class="ch-btn ch-btn-big"/>
		</form>
	</div>

	<div id="Basic" class="tabcontent">
		<form action="{$uri|escape:'htmlall':'UTF-8'}" method="post">
			<fieldset>
				<legend>
					<img src="../img/admin/contact.gif" />{l s='Basic Checkout Credential' mod='mercadopago'}
				</legend>
				<p><i>{l s='Lets to configure your module, so you need to get your client_id and client_secret. Do you need to use the link above of your country.' mod='mercadopago'}</i></p>
				<br/>
				<label>{l s='Client Id:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_CLIENT_ID" value="{$client_id|escape:'htmlall':'UTF-8'}" />
				</div>
				<br/>
				<label>{l s='Client Secret:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_CLIENT_SECRET" value="{$client_secret|escape:'htmlall':'UTF-8'}" />
				</div>
				<h4>{l s='- To obtain your Client Id and Client Secret please click on your country:' mod='mercadopago'}</h4>
				<h3>
					<a href="https://www.mercadopago.com/mla/account/credentials?type=basic" target="_blank"><u>{l s='Argentina' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlb/account/credentials?type=basic" target="_blank"><u>{l s='Brazil' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mco/account/credentials?type=basic" target="_blank"><u>{l s='Colombia' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlc/account/credentials?type=basic" target="_blank"><u>{l s='Chile' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlm/account/credentials?type=basic" target="_blank"><u>{l s='Mexico' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mpe/account/credentials?type=basic" target="_blank"><u>{l s='Peru' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlv/account/credentials?type=basic" target="_blank"><u>{l s='Venezuela' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlu/account/credentials?type=basic" target="_blank"><u>{l s='Uruguay' mod='mercadopago'}</u></a>
				</h3>
					<br />
			</fieldset>

			{if $country != '' && $client_id != ''}
				<fieldset>
					<legend>
						<img src="../img/admin/contact.gif" />{l s='Settings - MercadoPago Standard' mod='mercadopago'}
					</legend>
					<label>{l s='Active: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_STANDARD_ACTIVE" id="standard_active">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
					<br>
					<label>{l s='Custom Text:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="50" name="MERCADOPAGO_CUSTOM_TEXT" value="{$custom_text|escape:'htmlall'}" />
					</div>
					<br>
					{if $country == 'MLA'}
						<label>Añadir valor:</label>
						<div class="">
							<input type="text" size="2" type="number" name="MERCADOPAGO_PERCENT_EXTRA" value="{$percent_extra|escape:'htmlall':'UTF-8'}" /><span class="important">%</span>
						</div>
						<br>
					{/if}
					<label>{l s='Payments with two cards: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_TWO_CARDS" id="two_cards" alt="Checkout Standard">
							<option value="active">{l s='Yes' mod='mercadopago'} </option>
							<option value="inactive">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
					<br />

					<label>{l s='Banner:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="33" name="MERCADOPAGO_STANDARD_BANNER" value="{$standard_banner|escape:'htmlall':'UTF-8'}" />
					</div>
					<br />
					<label>{l s='Checkout window:' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_WINDOW_TYPE" id="window_type">
							<!-- <option value="iframe">{l s='iFrame' mod='mercadopago'} </option> -->
							<option value="redirect">{l s='Redirect' mod='mercadopago'} </option>
						</select>
					</div>
					<br />
					<label>{l s='Exclude payment methods:' mod='mercadopago'}</label>
					<div class="payment-methods">
					<br />
					{foreach from=$payment_methods item=payment_method}
						<br />
						<input type="checkbox" name="MERCADOPAGO_{$payment_method.id|upper|escape:'htmlall':'UTF-8'}" id="{$payment_method.id|escape:'htmlall':'UTF-8'}">{$payment_method.name|escape:'htmlall':'UTF-8'}</input>
					{/foreach}
					</div>
					<label>{l s='iFrame width:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="33" name="MERCADOPAGO_IFRAME_WIDTH" value="{$iframe_width|escape:'htmlall':'UTF-8'}" />
					</div>
					<br />
					<label>{l s='iFrame height:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="33" name="MERCADOPAGO_IFRAME_HEIGHT" value="{$iframe_height|escape:'htmlall':'UTF-8'}" />
					</div>
					<br />
					<label>{l s='Max installments:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="33" name="MERCADOPAGO_INSTALLMENTS" value="{$installments|escape:'htmlall':'UTF-8'}" />
					</div>
					<br />
					<label>{l s='Auto Return: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_AUTO_RETURN" id="auto_return">
							<option value="approved">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
      
					{if in_array($country, array('MLB', 'MLM', 'MLA', 'MLC', 'MCO', 'MLV'))}
					<br/>
						<hr style="border-top: dotted 1px;"/>
						<h3>{l s='Settings Mercado Envios' mod='mercadopago'}</p></h3>
							<ul>
								<li><p>{l s='Mercado Envios works only with Checkout Standard' mod='mercadopago'}</p>
								{if $country == 'MLA'}
										<li><p><a target="_blank" href="https://www.mercadopago.com.ar/envios">Activa MercadoEnvíos</a></p></li>
										<li><p>
										Consulta los <a target="_blank" href="https://www1.oca.com.ar/ocaexpresspak/help/serviviosbasicos.asp">valores admitidos por OCA</a>.</p> </li>
								{/if}
								{if $country == 'MLM'}
									<li>
										<p><a target="_blank" href="https://www.mercadopago.com.mx/envios">Activa MercadoEnvíos</a></p>
									</li>
									<li>
										<p>Consulta los <a target="_blank" href="http://www.dhl.com.mx/content/dam/downloads/language_masters/express/es/shipping/weights_and_dimensions/weights_and_dimensions_es_lm.pdf">valores admitidos por DHL</a>.</p>
									</li>
								{/if}
								{if $country == 'MLB'}
									<li>
										<p><a target="_blank" href="https://www.mercadopago.com.br/envios">Ativar MercadoEnvios</a></p>
									</li>
									<li>
										<p>Consultar os <a target="_blank" href="http://www.correios.com.br/para-voce/precisa-de-ajuda/limites-de-dimensoes-e-de-peso">valores permitidos pelos Correios</a>.</hp>
									</li>
								{/if}
							</ul>
						<br>
						<div class="row">
							<div class="col-md-12">
								<label>{l s='Active MercadoEnvios: ' mod='mercadopago'}</label>
								<div class="">
									<select name="MERCADOENVIOS_ACTIVATE" id="MERCADOENVIOS_ACTIVATE">
										<option value="true">{l s='Yes' mod='mercadopago'} </option>
										<option value="false">{l s='No' mod='mercadopago'} </option>
									</select>
								</div>
							</div>
						</div>
					{/if}
				</fieldset>
			{/if}

			{if $client_id == ''}
				<input type="submit" name="login_standard" value="{l s='Login' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
			{else}
				<input type="submit" name="submit_checkout_standard" value="{l s='Save' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
			{/if}
		</form>
	</div>

	<div id="Custom" class="tabcontent">
		<form action="{$uri|escape:'htmlall':'UTF-8'}" method="post">
			<fieldset>
				<legend>
					<img src="../img/admin/contact.gif" />{l s='Custom Payments Credentials' mod='mercadopago'}
				</legend>
				<p><i>{l s='This functionality is for your clients to pay without go to another environment.' mod='mercadopago'}</i> </p>
				<br/>
				<label>{l s='Public Key:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="60" id="MERCADOPAGO_PUBLIC_KEY" name="MERCADOPAGO_PUBLIC_KEY" value="{$public_key|escape:'htmlall':'UTF-8'}" />
				</div>
				<br />
				<label>{l s='Access Token:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="60" id="MERCADOPAGO_ACCESS_TOKEN" name="MERCADOPAGO_ACCESS_TOKEN" value="{$access_token|escape:'htmlall':'UTF-8'}" />
				</div>
				<h4>{l s='- To obtain your Public Key and Access Token please click on your country:' mod='mercadopago'}</h4>
				<h3>
					<a href="https://www.mercadopago.com/mla/account/credentials?type=custom" target="_blank"><u>{l s='Argentina' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlb/account/credentials?type=custom" target="_blank"><u>{l s='Brazil' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mco/account/credentials?type=custom" target="_blank"><u>{l s='Colombia' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlc/account/credentials?type=custom" target="_blank"><u>{l s='Chile' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlm/account/credentials?type=custom" target="_blank"><u>{l s='Mexico' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mpe/account/credentials?type=custom" target="_blank"><u>{l s='Peru' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlv/account/credentials?type=custom" target="_blank"><u>{l s='Venezuela' mod='mercadopago'}</u></a> |
					<a href="https://www.mercadopago.com/mlu/account/credentials?type=custom" target="_blank"><u>{l s='Uruguay' mod='mercadopago'}</u></a>
				</h3>
			</fieldset>
			{if $country != '' && $access_token != ''}
				<fieldset>
					<legend>
						<img src="../img/admin/contact.gif" />{l s='Settings - Custom Payments' mod='mercadopago'}
					</legend>
					<label>{l s='Active: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_CUSTOM_ACTIVE" id="custom_active">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
					<br>
					<h3><p>{l s='Exclude payment methods:' mod='mercadopago'}</p></h3>
					<div class="">
						<label>{l s='Credit Card' mod='mercadopago'}:</label>
						<input type="checkbox" class="options_custom" name="MERCADOPAGO_CREDITCARD_ACTIVE" value="true" id="creditcard_active"

						{if $creditcard_active == 'true'}
							checked
						{/if}
						/>
					</div>

					<br />
					{foreach from=$offline_payment_settings key=offline_payment item=value}
						<div class="">
							<label>{$value.name|ucfirst|escape:'htmlall':'UTF-8'}:</label>
							<input type="checkbox" name="MERCADOPAGO_{$offline_payment|upper|escape:'htmlall':'UTF-8'}_ACTIVE" class="ticket" value="true" id="MERCADOPAGO_{$offline_payment|escape:'htmlall':'UTF-8'}_ACTIVE"
							{if $value.disabled == 'true'}
								checked
							{/if} />
						</div>
					<br />
					{/foreach}

					<hr style="border-top: dotted 1px;"/>
							<h3><p>{l s='Mercado Pago Discount (Only to payments one installments)' mod='mercadopago'}</p></h3>

					<label>{l s='Discount percent:' mod='mercadopago'}</label>
					<div >
						<input type="text" name="MERCADOPAGO_DISCOUNT_PERCENT" value="{$percent|escape:'htmlall':'UTF-8'}" />
					</div><br />
					<label>{l s='Discount payment methods:' mod='mercadopago'}</label>
          
					<div >
						<input type="checkbox" name="MERCADOPAGO_ACTIVE_DISCOUNT_CREDITCARD" {if $active_credicard_discount == 1}checked='checked'{/if} value="1">{l s='Credit card (in cash)' mod='mercadopago'}</input><br />
						<input type="checkbox" name="MERCADOPAGO_ACTIVE_DISCOUNT_BOLETO" {if $active_boleto_discount == 1}checked='checked'{/if} value="1">{l s='Ticket' mod='mercadopago'}</input>
					</div>
					<br />
					{if in_array($country, array('MLB', 'MLM', 'MLA', 'MPE'))}
		  			<hr style="border-top: dotted 1px;"/>
		  			<h3><p>{l s='Coupon MercadoPago' mod='mercadopago'}</p></h3>
		  			<p style="text-align: center;" class="important">{l s='* Valid option only for sites participating coupon campaigns.' mod='mercadopago'}</p>
		  			<br/>
		  			<label>{l s='Enable Coupon of Discount: ' mod='mercadopago'}</label>
		  			<div class="">
		  				<select name="MERCADOPAGO_COUPON_ACTIVE" id="coupon_active">
		  					<option value="true">{l s='Yes' mod='mercadopago'} </option>
		  					<option value="false">{l s='No' mod='mercadopago'} </option>
		  				</select>

		  			</div>
					{/if}
					<br />
					<hr style="border-top: dotted 1px;"/>

					<h3><p>{l s='Display installments calculator' mod='mercadopago'}</p></h3>

					<label>{l s='Product Page' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_PRODUCT_CALCULATE" id="MERCADOPAGO_PRODUCT_CALCULATE">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
					<br />
					<label>{l s='Cart Page' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_CART_CALCULATE" id="MERCADOPAGO_CART_CALCULATE">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
				</fieldset>
			{/if}

			{if $access_token == ''}
				<input type="submit" name="login_custom" value="{l s='Login' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
			{else}
				<input type="submit" name="submit_checkout_custom" value="{l s='Save' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
			{/if}
		</form>
	</div>
</div>

<script type="text/javascript">

	window.onload = function() {
		if (document.getElementById("category")){
			document.getElementById("category").value = "{$category|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("coupon_active")){
			document.getElementById("coupon_active").value = "{$coupon_active|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("MERCADOPAGO_CART_CALCULATE")){
			document.getElementById("MERCADOPAGO_CART_CALCULATE").value = "{$MERCADOPAGO_CART_CALCULATE|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("MERCADOPAGO_PRODUCT_CALCULATE")){
			document.getElementById("MERCADOPAGO_PRODUCT_CALCULATE").value = "{$MERCADOPAGO_PRODUCT_CALCULATE|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("coupon_ticket_active")){
			document.getElementById("coupon_ticket_active").value = "{$coupon_ticket_active|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("point_active")){
			document.getElementById("point_active").value = "{$point_active|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("two_cards")){
			document.getElementById("two_cards").value = "{$two_cards|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("standard_active")){
			document.getElementById("standard_active").value = "{$standard_active|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("custom_active")){
			document.getElementById("custom_active").value = "{$custom_active|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("MERCADOENVIOS_ACTIVATE")){
			document.getElementById("MERCADOENVIOS_ACTIVATE").value = "{$MERCADOENVIOS_ACTIVATE|escape:'htmlall':'UTF-8'}" == "" ? "false" :
			"{$MERCADOENVIOS_ACTIVATE|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("window_type")){
			document.getElementById("window_type").value = "{$window_type|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("auto_return")){
			document.getElementById("auto_return").value = "{$auto_return|escape:'htmlall':'UTF-8'}";
		}

		{foreach from=$payment_methods_settings key=payment_method item=value}
			document.getElementById("{$payment_method|escape:'htmlall':'UTF-8'}").checked = "{$value|escape:'htmlall':'UTF-8'}";
		{/foreach}

	}


	function bloquearEnvios(obj) {
		$( "#MERCADOENVIOS_ACTIVATE" ).val("false");
		if (obj != null && obj.value == "true") {
			$( "#MERCADOENVIOS_ACTIVATE" ).prop("disabled", false);
		} else {
			$( "#MERCADOENVIOS_ACTIVATE" ).prop("disabled", true);
		}
	}

	$("#MERCADOENVIOS_ACTIVATE").change(
			function() {
				if (this.value == "true") {
					retorno = window.confirm("{l s='If you enable this, the others payment type will be disable. Do you want to continue?' mod='mercadopago'}");
					if (retorno) {
						$( "#creditcard_active" ).val("false");
						$( ".ticket" ).val("false");
						$('[name=MERCADOPAGO_PUBLIC_KEY]').val("");
						$('[name=MERCADOPAGO_ACCESS_TOKEN]').val("");
						loadCustom();
					}
				}
		});
	$( "#MERCADOENVIOS_ACTIVATE" ).prop("disabled", true);
	$("#standard_active").change(function(){
		bloquearEnvios(this);
	}
	);


	$(document).ready(function (){
		bloquearEnvios(document.getElementById("standard_active"));
	});

	function openTabSettings(evt, cityName) {
	    // Declare all variables
	    var i, tabcontent, tablinks;

	    // Get all elements with class="tabcontent" and hide them
	    tabcontent = document.getElementsByClassName("tabcontentSettings");
	    for (i = 0; i < tabcontent.length; i++) {
	        tabcontent[i].style.display = "none";
	    }

	    // Get all elements with class="tablinks" and remove the class "active"
	    tablinks = document.getElementsByClassName("tablinksSettings");
	    for (i = 0; i < tablinks.length; i++) {
	        tablinks[i].className = tablinks[i].className.replace(" active", "");
	    }

	    // Show the current tab, and add an "active" class to the link that opened the tab
	    document.getElementById(cityName).style.display = "block";
	    evt.currentTarget.className += " active";
	}
	// Get the element with id="defaultOpen" and click on it
	document.getElementById("defaultOpen").click();

	// Get the element with id="defaultOpen" and click on it
	document.getElementById("defaultTab").click();

	function openTab(evt, cityName) {
	    // Declare all variables
	    var i, tabcontent, tablinks;

	    // Get all elements with class="tabcontent" and hide them
	    tabcontent = document.getElementsByClassName("tabcontent");
	    for (i = 0; i < tabcontent.length; i++) {
	        tabcontent[i].style.display = "none";
	    }

	    // Get all elements with class="tablinks" and remove the class "active"
	    tablinks = document.getElementsByClassName("tablinks");
	    for (i = 0; i < tablinks.length; i++) {
	        tablinks[i].className = tablinks[i].className.replace(" active", "");
	    }

	    // Show the current tab, and add an "active" class to the link that opened the tab
	    document.getElementById(cityName).style.display = "block";
	    evt.currentTarget.className += " active";
	}


// 	$('#MERCADOPAGO_PUBLIC_KEY').on("change", function(){
// 		loadCustom();
// 	});

// 	$('#MERCADOPAGO_ACCESS_TOKEN').on("change", function(){
// 		loadCustom();
// 	});
/*
function loadCustom() {
		if($('#MERCADOPAGO_ACCESS_TOKEN').val() == ""
			|| $('#MERCADOPAGO_PUBLIC_KEY').val() == "") {

				$( "#creditcard_active" ).val("false");
				$( ".ticket" ).val("false");

				$("#creditcard_active").attr('disabled', true);
				$(".ticket").attr('disabled', true);

				$("#creditcard_active").prop('checked', false);
				$(".ticket").prop('checked', false);
		} else {
				$("#creditcard_active").attr('disabled', false);
				$(".ticket").attr('disabled', false);
		}
	}
	loadCustom();*/

//     $('.ticket').change(function() {
//         if($(this).is(":checked")) {
//             $(this).val('true');
//         } else {
//         	$(this).val('false');
//         }
//     });

//     $('#creditcard_active').change(function() {
//         if($(this).is(":checked")) {
//             $(this).val('true');
//         } else {
//         	$(this).val('false');
//         }
//     });


</script>
