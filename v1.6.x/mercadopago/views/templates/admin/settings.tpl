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
	<br>
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

		<h4> <mark> {l s='- To obtain your Client Id, Client Secret, Public Key and Access Token please click on your country:' mod='mercadopago'}</mark> </h4>
		<h3><a href="https://www.mercadopago.com/mla/account/credentials?type=basic" target="_blank"><u>{l s='Argentina' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlb/account/credentials?type=basic" target="_blank"><u>{l s='Brazil' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mco/account/credentials?type=basic" target="_blank"><u>{l s='Colombia' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlc/account/credentials?type=basic" target="_blank"><u>{l s='Chile' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlm/account/credentials?type=basic" target="_blank"><u>{l s='Mexico' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mpe/account/credentials?type=basic" target="_blank"><u>{l s='Peru' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlv/account/credentials?type=basic" target="_blank"><u>{l s='Venezuela' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlu/account/credentials?type=basic" target="_blank"><u>{l s='Uruguay' mod='mercadopago'}</u></a> </h3>

	</div>

	<div id="Duvidas" class="tabcontentSettings">
	  	<h3>{l s='Question' mod='mercadopago'}</h3>
		<p><strong><a href="https://www.youtube.com/playlist?list=PLl8LGzRu2_sXxChIJm1e0xY6dU3Dj_tNi" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/youtube.png" width="20px;" height="20px">YouTube</a> </strong></p>
		<p><strong><a href="https://www.facebook.com/groups/modulos.mercadopago" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/facebook.png" width="20px;" height="20px">Facebook</a> </strong></p> 


		<p><a href="mailto:modulos@mercadopago.com.br?subject=Suport - Prestashop"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/email.png" width="20px;" height="20px">modulos@mercadopago.com.br</a> </p>

		<p><a href="https://www.mercadopago.com.br/developers/pt/solutions/payments/basic-checkout/test/test-payments/" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/credit_card.png" width="20px;" height="20px">{l s='Credit Card for test' mod='mercadopago'}</a></p>
        <ps-label-information label="{l s='Video Tutorial' mod='mercadopago'}">
			{if $country == 'MLB'}
				<iframe width="100%" style="max-width:560px" height="315" src="https://www.youtube.com/embed/Rsotj_9paOw" frameborder="0" allowfullscreen></iframe>
			{else}
				<iframe width="100%" style="max-width:560px" height="315" src="https://www.youtube.com/embed/rtXNkdaqUJ8" frameborder="0" allowfullscreen></iframe>
			{/if}
        </ps-label-information>
		
		<h4>{l s='Notification URL' mod='mercadopago'}</h4>
		<p> <small>{l s='Notification URL' mod='mercadopago'}: {$notification_url|escape:'htmlall':'UTF-8'}</small> </p>
		
	</div>

	<br>
			
	<ul class="tab">
	  <li><a href="javascript:void(0)" class="tablinks" onclick="openTab(event, 'Global')" id="globalTab">{l s='Global' mod='mercadopago'}</a></li>				
	  <li><a href="javascript:void(0)" class="tablinks" onclick="openTab(event, 'Basic')">{l s='Basic Checkout' mod='mercadopago'}</a></li>
	  <li><a href="javascript:void(0)" class="tablinks" onclick="openTab(event, 'Custom')">{l s='Custom Checkout' mod='mercadopago'}</a></li>
	</ul>		
		
	<form action="{$uri|escape:'htmlall':'UTF-8'}" method="post">	
		<div id="Global" class="tabcontent">
			
			<h2>{l s='Status Checkouts' mod='mercadopago'}</h2>

			<p>
				<label> Checkout Standard:</label>
				<strong>
					{if $standard_active}
						{l s='Enable' mod='mercadopago'}
					{else}
						{l s='Disable' mod='mercadopago'}
					{/if}	
				</strong>
			</p>
			<p>
				<label> Checkout Custom:</label>
				<strong>
					{if $custom_active}
						{l s='Enable' mod='mercadopago'}
					{else}
						{l s='Disable' mod='mercadopago'}
					{/if}	
				</strong>
			</p>
			
			<p>
				{if !empty($country)}
				<span><label>{l s='Category:' mod='mercadopago'}</label></span>
				<span><select name="MERCADOPAGO_CATEGORY" id="category">
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
					</select></span>
				{/if}
			</p>
		</div>	
		<br>
	<div id="Basic" class="tabcontent">
		<fieldset>
			<legend>
				<img src="../img/admin/contact.gif" />{l s='Credential' mod='mercadopago'}
			</legend>
			<p><i>{l s='Lets to configure your module, so you need to get your client_id and client_secret. Do you need to use the link above of your country.' mod='mercadopago'}</i></p>
			<br/>
			<label>{l s='Client Id:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="33" required="true" name="MERCADOPAGO_CLIENT_ID" value="{$client_id|escape:'htmlall':'UTF-8'}" />
			</div>
			<br/>
			<label>{l s='Client Secret:' mod='mercadopago'}</label>
			<div class="">
				<input type="text" size="33" required="true" name="MERCADOPAGO_CLIENT_SECRET" value="{$client_secret|escape:'htmlall':'UTF-8'}" />
			</div>
				<br />
		</fieldset>

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
				<br>
				<label>{l s='Custom Text:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="50" name="MERCADOPAGO_CUSTOM_TEXT" value="{$custom_text|unescape:'htmlall'}" />
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
				{if $country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MLC' || $country == 'MCO' || $country == 'MLV'}
				<br/>
					<hr style="border-top: dotted 1px;"/>
					<h3>{l s='Settings Mercado Envios' mod='mercadopago'}</p>
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
		{if empty($country)}
			<input type="submit" name="login" value="{l s='Login' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
		{else}
			<input type="submit" name="submitmercadopago" value="{l s='Save' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
		{/if}					
	</div>
	<div id="Custom" class="tabcontent">
		<fieldset id="custom-list">
			<legend>
				<img src="../img/admin/contact.gif" />{l s='Settings - Custom Payments' mod='mercadopago'}
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
			<h3><a href="https://www.mercadopago.com/{$country|lower|escape:'htmlall':'UTF-8'}/account/credentials?type=custom" target="_blank"><u>{l s='To obtain your Public Key and Access Token please click here' mod='mercadopago'}</u></a></h3>

			<h3><p>{l s='Enable or Disable your custom payments' mod='mercadopago'}:</p></h3>

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
					{if $value.active == 'true'}
						checked
					{/if} />
				</div>
			<br />
			{/foreach}

			<hr style="border-top: dotted 1px;"/>
					<h3><p>{l s='Mercado Pago Discount (Only to payments one installments)' mod='mercadopago'}</p></h3>

			<label>{l s='Discount percent:' mod='mercadopago'}:</label>
			<div >
				<input type="text" name="MERCADOPAGO_DISCOUNT_PERCENT" value="{$percent|escape:'htmlall':'UTF-8'}" />
			</div><br />
			<label>{l s='Discount payment methods:' mod='mercadopago'}</label>
			<div >
				<input type="checkbox" name="MERCADOPAGO_ACTIVE_CREDITCARD" {if $active_credicard == 1}checked='checked'{/if} value="1">{l s='Credit card (in cash)' mod='mercadopago'}</input><br />
				<input type="checkbox" name="MERCADOPAGO_ACTIVE_BOLETO" {if $active_boleto == 1}checked='checked'{/if} value="1">{l s='Ticket' mod='mercadopago'}</input>
			</div>
			<br />

			{if $country == 'MLB' || $country == 'MLM' || $country == 'MLA' || $country == 'MPE'}
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

			<h3><p>{l s='Display installments calculator' mod='mercadopago'}:</p></h3>

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
		{if empty($country)}
			<input type="submit" name="login" value="{l s='Login' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
		{else}
			<input type="submit" name="submitmercadopago" value="{l s='Save' mod='mercadopago'}" class="ch-btn ch-btn-big"/>
		{/if}
	</div>	
	</form>
</div>

<script type="text/javascript">

	window.onload = function() {
		if (document.getElementById("category")){
			document.getElementById("category").value = "{$category|escape:'htmlall':'UTF-8'}";
		}

		if (document.getElementById("checkout_2")){
			document.getElementById("checkout_2").value = "{$checkout_2|escape:'htmlall':'UTF-8'}";
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

		document.getElementById("two_cards").value = "{$two_cards|escape:'htmlall':'UTF-8'}";

		if (document.getElementById("standard_active")){
			document.getElementById("standard_active").value = "{$standard_active|escape:'htmlall':'UTF-8'}";
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

		console.info("{$custom_text|unescape:'htmlall'}");

	}


	function bloquearEnvios(obj) {
		$( "#MERCADOENVIOS_ACTIVATE" ).val("false");
		if (obj.value == "true") {
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
	document.getElementById("globalTab").click();		

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
		

	$('#MERCADOPAGO_PUBLIC_KEY').on("change", function(){
		loadCustom();
	});

	$('#MERCADOPAGO_ACCESS_TOKEN').on("change", function(){
		loadCustom();
	});

	$('#custom-list input:checkbox:checked').each(function(){
		console.info("entrou aqui");
	});

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
	loadCustom();

    $('.ticket').change(function() {
        if($(this).is(":checked")) {
            $(this).val('true');
        } else {
        	$(this).val('false');
        }
    });

    $('#creditcard_active').change(function() {
        if($(this).is(":checked")) {
            $(this).val('true');
        } else {
        	$(this).val('false');
        }
    });


</script>

