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
*  @author    ricardobrito
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of MercadoPago
*}
<div class="mp-module">
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
					{$error|escape:'htmlall'}
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
				{$error|escape:'htmlall'}	
			</div>
			{/foreach}
		{/if}
	{/if}
	</div>
	<img class="logo" src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo_large.png">
	</br>
	</br>
	</br>
	<h3> {l s='Notes:' mod='mercadopago'}</h3>
	<h4> {l s='- To obtain your Client Id and Client Secret please click on your country: ' mod='mercadopago'}
		<a href="https://www.mercadopago.com/mlb/ferramentas/aplicacoes"><u>{l s='Brazil' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mla/herramientas/aplicaciones"><u>{l s='Argentina' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlm/herramientas/aplicaciones"><u>{l s='Mexico' mod='mercadopago'}</u></a> | 
		<a href="https://www.mercadopago.com/mlv/herramientas/aplicaciones"><u>{l s='Venezuela' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mco/herramientas/aplicaciones"><u>{l s='Colombia' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlc/herramientas/aplicaciones"><u>{l s='Chile' mod='mercadopago'}</u></a>
	</h4>
	{if $country eq "MLB"}
		<h4> {l s='- Get your public_key in the following address: https://www.mercadopago.com/mlb/account/credentials' mod='mercadopago'}</h4>
	{elseif $country eq "MLM"}
		<h4> {l s='- Get your public_key in the following address: https://www.mercadopago.com/mlm/account/credentials' mod='mercadopago'}</h4>
	{elseif $country eq "MLA"}
		<h4> {l s='- Get your public_key in the following address: https://www.mercadopago.com/mla/account/credentials' mod='mercadopago'}</h4>
	{elseif $country eq "MLC"}
		<h4> {l s='- Get your public_key in the following address: https://www.mercadopago.com/mlc/account/credentials' mod='mercadopago'}</h4>
	{elseif $country eq "MCO"}
		<h4> {l s='- Get your public_key in the following address: https://www.mercadopago.com/mco/account/credentials' mod='mercadopago'}</h4>
	{elseif $country eq "MLV"}
		<h4> {l s='- Get your public_key in the following address: https://www.mercadopago.com/mlv/account/credentials' mod='mercadopago'}</h4>
	{/if}
	<form action="{$uri|escape:'htmlall'}" method="post">
		<fieldset>
			<legend>
				<img src="../img/admin/contact.gif" />{l s='Settings - General' mod='mercadopago'}
			</legend>
			<label>{l s='Client Id:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="33" name="MERCADOPAGO_CLIENT_ID" value="{$client_id|escape:'htmlall'}" />
			</div>
			<br />
			<label>{l s='Client Secret:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="33" name="MERCADOPAGO_CLIENT_SECRET" value="{$client_secret|escape:'htmlall'}" />
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
			{/if}
				<br/>
				<label>{l s='Notification URL' mod='mercadopago'}:</label>
				<div>{$notification_url|escape:'javascript'}</div>

				<br />					
		</fieldset>
		{if $country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MLC' || $country == 'MCO' || $country == 'MLV'}
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
					<input type="text" size="33" name="MERCADOPAGO_PUBLIC_KEY" value="{$public_key|escape:'htmlall'}" />
				</div>
				<br />
				<label>{l s='Banner:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_CREDITCARD_BANNER" value="{$creditcard_banner|escape:'htmlall'}" />
				</div>
			</fieldset>
			{foreach from=$offline_payment_settings key=offline_payment item=value}
				<fieldset>
					<legend>
						<img src="../img/admin/contact.gif" />{l s='Settings - ' mod='mercadopago'}{$value.name|ucfirst} {l s=' Custom' mod='mercadopago'}
					</legend>
					<label>{l s='Active: ' mod='mercadopago'}</label>
					<div class="">
						<select name="MERCADOPAGO_{$offline_payment|upper}_ACTIVE" id="{$offline_payment}_active">
							<option value="true">{l s='Yes' mod='mercadopago'} </option>
							<option value="false">{l s='No' mod='mercadopago'} </option>
						</select>
					</div>
					<br />
					<label>{l s='Banner:' mod='mercadopago'}</label>
					<div class="">
						<input type="text" size="33" name="MERCADOPAGO_{$offline_payment|upper}_BANNER" value="{$value.banner|escape:'htmlall'}" />
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
					<input type="text" size="33" name="MERCADOPAGO_STANDARD_BANNER" value="{$standard_banner|escape:'htmlall'}" />
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
					<input type="checkbox" name="MERCADOPAGO_{$payment_method.id|upper}" id="{$payment_method.id}">{$payment_method.name}</input>
				{/foreach}
				</div>
				<label>{l s='iFrame width:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_IFRAME_WIDTH" value="{$iframe_width|escape:'htmlall'}" />
				</div>
				<br />
				<label>{l s='iFrame height:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_IFRAME_HEIGHT" value="{$iframe_height|escape:'htmlall'}" />
				</div>
				<br />
				<label>{l s='Max installments:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_INSTALLMENTS" value="{$installments|escape:'htmlall'}" />
				</div>
				<br />
				<label>{l s='Auto Return: ' mod='mercadopago'}</label>
				<div class="">
					<select name="MERCADOPAGO_AUTO_RETURN" id="auto_return">
						<option value="approved">{l s='Yes' mod='mercadopago'} </option>
						<option value="false">{l s='No' mod='mercadopago'} </option>
					</select>
				</div>
			</fieldset>
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
			document.getElementById("category").value = "{$category|escape:'javascript'}";
		}

		if (document.getElementById("creditcard_active")){
			document.getElementById("creditcard_active").value = "{$creditcard_active|escape:'javascript'}";
		}

		if (document.getElementById("standard_active")){
			document.getElementById("standard_active").value = "{$standard_active|escape:'javascript'}";
		}


		if (document.getElementById("log_active")){
			document.getElementById("log_active").value = "{$log_active|escape:'javascript'}";
		}

		if (document.getElementById("window_type")){
			document.getElementById("window_type").value = "{$window_type|escape:'javascript'}";
		}

		if (document.getElementById("auto_return")){
			document.getElementById("auto_return").value = "{$auto_return|escape:'javascript'}";
		}

		{foreach from=$payment_methods_settings key=payment_method item=value}
			document.getElementById("{$payment_method|escape:'javascript'}").checked = "{$value|escape:'javascript'}";
		{/foreach}
		
		{foreach from=$offline_payment_settings key=offline_payment item=value}
			document.getElementById("{$offline_payment}_active").value = "{$value.active|escape:'javascript'}";
		{/foreach}
	}
</script>
