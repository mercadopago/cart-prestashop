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
<div class="mp-module">
	{if empty($client_id)} 
		{include file='./marketing.tpl'
		this_path_ssl=$this_path_ssl|escape:'htmlall':'UTF-8'}
	{/if} 
	<div id="settings" style="display: block">
	<div id="alerts">
	{if $version eq 6}
		{if $success eq 'true'}
		<div id="alert" class="bootstrap">
			<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert">×</button>
				{l s='Settings changed successfully.' mod='mercadopago'}
			</div>
		</div>
		{elseif $errors|@count > 0}
			{foreach from=$errors item=error}
			<div class="bootstrap">
				<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert">×</button>
					{l s='Settings failed to change.' mod='mercadopago'}
				</div>
			</div>
			<div class="bootstrap">
				<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert">×</button>
					{$error|escape:'htmlall':'UTF-8'}
				</div>
			</div>
			{/foreach}
		{/if}
	{elseif $version eq 5}
		{if $success eq 'true'}
			<div class="conf">
				{l s='Settings changed successfully.' mod='mercadopago'}
			</div>
		</div>
		{elseif $errors|@count > 0}
			<div class="error">
				{l s='Settings failed to change.' mod='mercadopago'}
			</div>
			{foreach from=$errors item=error}
			<div class="error">
				{$error|escape:'htmlall':'UTF-8'}	
			</div>
			{/foreach}
		{/if}
	{/if}
	</div>
	<img class="logo" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo_large.png">
	<br>
	<br>
	<br>
	<h3> {l s='Notes:' mod='mercadopago'}</h3>
	<h4>{l s='- To obtain your Client Id, Client Secret, Public Key and Access Token please click on your country:' mod='mercadopago'}</h4>
		<a href="https://www.mercadopago.com/mla/account/credentials" target="_blank"><u>{l s='Argentina' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlb/account/credentials" target="_blank"><u>{l s='Brazil' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mco/account/credentials" target="_blank"><u>{l s='Colombia' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlc/account/credentials" target="_blank"><u>{l s='Chile' mod='mercadopago'}</u></a> |		
		<a href="https://www.mercadopago.com/mlm/account/credentials" target="_blank"><u>{l s='Mexico' mod='mercadopago'}</u></a> | 
		<a href="https://www.mercadopago.com/mlv/account/credentials" target="_blank"><u>{l s='Venezuela' mod='mercadopago'}</u></a> 
	
	<form action="{$uri|escape:'htmlall':'UTF-8'}" method="post">
		<fieldset>
			<legend>
				<img src="../img/admin/contact.gif" />{l s='Settings - General' mod='mercadopago'}
			</legend>
			<label>{l s='Client Id:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="33" required="true" name="MERCADOPAGO_CLIENT_ID" value="{$client_id|escape:'htmlall':'UTF-8'}" />
			</div>
			<br />
			<label>{l s='Client Secret:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="33" required="true" name="MERCADOPAGO_CLIENT_SECRET" value="{$client_secret|escape:'htmlall':'UTF-8'}" />
			</div>
			<br />
			<label>{l s='Access Token:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="60" required="true" name="MERCADOPAGO_ACCESS_TOKEN" value="{$access_token|escape:'htmlall':'UTF-8'}" />
			</div>
			<br />
			{if !empty($country)}
			<label>{l s='Category:' mod='mercadopago'}</label>
				<div class=""> 
					<select name="MERCADOPAGO_CATEGORY" id="category">
						 <option value="art">{l s='Collectibles & Art' mod='mercadopago'}</option>
						 <option value="baby">{l s='Toys for Baby, Stroller, Stroller Accessories, Car Safety Seats' mod='mercadopago'}</option>
						 <option value="coupons">{l s='Coupons' mod='mercadopago'}</option>
						 <option value="donations">{l s='Donations' mod='mercadopago'}</option>
						 <option value="computing">{l s='Computers & Tablets' mod='mercadopago'}</option>
						 <option value="cameras">{l s='Cameras & Photography' mod='mercadopago'}</option>
						 <option value="video_games">{l s='Video Games & Consoles' mod='mercadopago'}</option>
						 <option value="television">{l s='LCD, LED, Smart TV, Plasmas, TVs' mod='mercadopago'}</option>
						 <option value="car_electronics">{l s='Car Audio, Car Alarm Systems & Security, Car DVRs, Car Video Players, Car PC' mod='mercadopago'}</option>
						 <option value="electronics">{l s='Audio & Surveillance, Video & GPS, Others' mod='mercadopago'}</option>
						 <option value="automotive">{l s='Parts & Accessories' mod='mercadopago'}</option>
						 <option value="entertainment">{l s='Music, Movies & Series, Books, Magazines & Comics, Board Games & Toys' mod='mercadopago'}</option>
						 <option value="fashion">{l s='Men\'s, Women\'s, Kids & baby, Handbags & Accessories, Health & Beauty, Shoes, Jewelry & Watches' mod='mercadopago'}</option>
						 <option value="games"> {l s='Online Games & Credits' mod='mercadopago'}</option>
						 <option value="home">{l s='Home appliances. Home & Garden' mod='mercadopago'}</option>
						 <option value="musical">{l s='Instruments & Gear' mod='mercadopago'}</option>
						 <option value="phones">{l s='Cell Phones & Accessories' mod='mercadopago'}</option>
						 <option value="services">{l s='General services' mod='mercadopago'}</option>
						 <option value="learnings" >{l s='Trainings, Conferences, Workshops' mod='mercadopago'}</option>
						 <option value="tickets">{l s='Tickets for Concerts, Sports, Arts, Theater, Family, Excursions tickets, Events & more' mod='mercadopago'}</option>
						 <option value="travels">{l s='Plane tickets, Hotel vouchers, Travel vouchers' mod='mercadopago'}</option>
						 <option value="virtual_goods">{l s='E-books, Music Files, Software, Digital Images,  PDF Files and any item which can be electronically stored in a file, Mobile Recharge, DTH Recharge and any Online Recharge' mod='mercadopago'}</option>
						 <option value="others" selected="selected">{l s='Other categories' mod='mercadopago'}</option>
					</select>	
				</div>
								<br/>
				<label>{l s='Notification URL' mod='mercadopago'}:</label>
				<div>{$notification_url|escape:'htmlall':'UTF-8'}</div>
			{/if}

				<br />					
		</fieldset>
		{if $country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MLC' || $country == 'MCO' || $country == 'MLV' || $country == 'MPE'}
			<fieldset>
				<legend>
					<img src="../img/admin/contact.gif" />{l s='Settings - Custom Credit Card' mod='mercadopago'}
				</legend>
				<label>{l s='Active: ' mod='mercadopago'}</label>
				<div class="">
					<select name="MERCADOPAGO_CREDITCARD_ACTIVE" id="creditcard_active">
						<option value="true">{l s='Yes' mod='mercadopago'}</option>
						<option value="false">{l s='No' mod='mercadopago'} </option>
					</select>
				</div>
				<br />	
				<label>{l s='Public Key:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_PUBLIC_KEY" value="{$public_key|escape:'htmlall':'UTF-8'}" />
				</div>
				<br/>
				<label>{l s='Banner:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_CREDITCARD_BANNER" value="{$creditcard_banner|escape:'htmlall':'UTF-8'}" />
				</div>
			</fieldset>
			{foreach from=$offline_payment_settings key=offline_payment item=value}
				<fieldset>
					<legend>
						<img src="../img/admin/contact.gif" />{l s='Settings - ' mod='mercadopago'}{$value.name|ucfirst|escape:'htmlall':'UTF-8'} {l s=' Custom' mod='mercadopago'}
					</legend>
					<label>{l s='Active: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_{$offline_payment|upper|escape:'htmlall':'UTF-8'}_ACTIVE" class="ticket" id="{$offline_payment|escape:'htmlall':'UTF-8'}_active">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
					<br />
					<label>{l s='Banner:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="33" name="MERCADOPAGO_{$offline_payment|escape:'htmlall':'UTF-8'}_BANNER" value="{$value.banner|escape:'htmlall':'UTF-8'}" />
					</div>
				</fieldset>
				<br />
			{/foreach}
		{/if}
		{if $country != ''}
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
				<br />
				<label>{l s='Banner:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_STANDARD_BANNER" value="{$standard_banner|escape:'htmlall':'UTF-8'}" />
				</div>
				<br />
				<label>{l s='Checkout window:' mod='mercadopago'}</label>
				<div class="">
					<select name="MERCADOPAGO_WINDOW_TYPE" id="window_type">
						<option value="iframe">{l s='iFrame' mod='mercadopago'} </option>
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
				{if $country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MLC' || $country == 'MCO' || $country == 'MLV'}			
					<div class="row">
						<div class="col-md12">
							<img class="logo" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadoenvios_hori.jpg" width="250px;" style="margin-left:-250px;">
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md12">
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
			{if $country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MPE'}				
				<fieldset>
					<legend class="ch-form-row discount-link" style="padding-left: 30px;">
						{l s='Coupon MercadoPago' mod='mercadopago'}
					</legend>
					<p style="text-align: center;">{l s='* Valid option only for sites participating coupon campaigns.' mod='mercadopago'}</p>
					<br/>
					<label>{l s='Enable Coupon of Discount: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_COUPON_ACTIVE" id="coupon_active">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
						
					</div>			
				</fieldset>
			{/if}
			<fieldset>
				<legend>
					<img src="../img/admin/contact.gif" />{l s='Settings - Active log' mod='mercadopago'}
				</legend>
				<label>{l s='Active: ' mod='mercadopago'}</label>
				<div class="">
					<select name="MERCADOPAGO_LOG" id="log_active">
						<option value="true">{l s='Yes' mod='mercadopago'} </option>
						<option value="false">{l s='No' mod='mercadopago'} </option>
					</select>
				</div>
			</fieldset>			
		{/if}
		{if empty($country)}
			<input type="submit" name="login" value="{l s='Login' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
		{else}
			<input type="submit" name="submitmercadopago" value="{l s='Save' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
		{/if}
			<!-- <input type="button" id="back" value="{l s='Back' mod='mercadopago'}" class="ch-btn-orange ch-btn-big-orange"/> -->
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function (){
		// hide marketing when settings are updated
		if ($("#alerts").children().length > 0) {
			$(".marketing").hide();
			$("#settings").show();
			$.scrollTo(0, 0);
		}
	})
	
	window.onload = function() {
		if (document.getElementById("category")){
			document.getElementById("category").value = "{$category|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("creditcard_active")){
			document.getElementById("creditcard_active").value = "{$creditcard_active|escape:'htmlall':'UTF-8'}";
		}
		if (document.getElementById("coupon_active")){
			document.getElementById("coupon_active").value = "{$coupon_active|escape:'htmlall':'UTF-8'}";
		}	
		if (document.getElementById("coupon_ticket_active")){
			document.getElementById("coupon_ticket_active").value = "{$coupon_ticket_active|escape:'htmlall':'UTF-8'}";
		}			
		

		if (document.getElementById("standard_active")){
			document.getElementById("standard_active").value = "{$standard_active|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("MERCADOENVIOS_ACTIVATE")){
			document.getElementById("MERCADOENVIOS_ACTIVATE").value = "{$MERCADOENVIOS_ACTIVATE|escape:'htmlall':'UTF-8'}" == "" ? "false" : 
			"{$MERCADOENVIOS_ACTIVATE|escape:'htmlall':'UTF-8'}";
		}
		if (document.getElementById("log_active")){
			document.getElementById("log_active").value = "{$log_active|escape:'htmlall':'UTF-8'}";
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
		
		{foreach from=$offline_payment_settings key=offline_payment item=value}
			document.getElementById("{$offline_payment|escape:'htmlall':'UTF-8'}_active").value = "{$value.active|escape:'htmlall':'UTF-8'}";
		{/foreach}
	}
	
	$("#back").click(
			function() { 
				$(".marketing").show();
				$("#settings").hide();
				$("#alerts").remove();
				$.scrollTo(0, 0);
		});
	
	$("#MERCADOENVIOS_ACTIVATE").change(
			function() { 
				if (this.value == "true") {
					retorno = window.confirm("{l s='If you enable this, the others payment type will be disable. Do you want to continue?' mod='mercadopago'}");
					if (retorno) {
						$( "#creditcard_active" ).val("false");
						$( ".ticket" ).val("false");
						$('[name=MERCADOPAGO_PUBLIC_KEY]').val("");
					}
				}
		});
	$( "#MERCADOENVIOS_ACTIVATE" ).prop("disabled", true);
	$("#standard_active").change(
			function() { 
				$( "#MERCADOENVIOS_ACTIVATE" ).val("false");
				if (this.value == "true") {
					$( "#MERCADOENVIOS_ACTIVATE" ).prop("disabled", false);
				} else {
					$( "#MERCADOENVIOS_ACTIVATE" ).prop("disabled", true);
				}
			}
	);


	$(document).ready(function (){
		var cliend_id = "{$client_id|escape:'htmlall':'UTF-8'}";
		// hide marketing when settings are updated
		if (cliend_id.length > 0) {
			$(".marketing").hide();
			$("#settings").show();
			$.scrollTo(0, 0);
		} else {
			$(".marketing").show();
			$("#settings").hide();
			$.scrollTo(0, 0);
		}
	})	
</script>

<style>
.ch-form-row.discount-link {
    background: url(https://secure.mlstatic.com/checkout-resources/resourses/assets/icon-discount.bd9c2205796f.png) no-repeat;
    line-height: 25px;
}
</style>