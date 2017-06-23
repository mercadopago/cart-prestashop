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

	<div id="settings" style="display: none">

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


	<ul class="tab">
	  <li><a href="javascript:void(0)" class="tablinks" onclick="openTab(event, 'Requisitos')" id="defaultOpen">{l s='Requirements' mod='mercadopago'}</a></li>
	  <!--<li><a href="javascript:void(0)" class="tablinks" onclick="openTab(event, 'Testes')">Testes</a></li>-->
	  <li><a href="javascript:void(0)" class="tablinks" onclick="openTab(event, 'Duvidas')">{l s='Question' mod='mercadopago'}</a></li>
	</ul>

	<div id="Requisitos" class="tabcontent">
	 	<h3>{l s='Requirements' mod='mercadopago'}</h3>
		<p>
			<strong>{l s='Installed Curl' mod='mercadopago'}:</strong><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.curl|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>
		<p>
			<strong>{l s='Dimensions of the product registered' mod='mercadopago'}:</strong> <img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.dimensoes|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>
		<p>
			<strong>{l s='Installed SSL' mod='mercadopago'}:</strong><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.ssl|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>
		<p>
			<strong>{l s='PHP Version' mod='mercadopago'}:</strong><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/{$requirements.version|escape:'htmlall':'UTF-8'}.png" width="20px;" height="20px">
		</p>		
	</div>

	<!--<div id="Testes" class="tabcontent">
	  	<h3>Dados para testes</h3>

	  	<p>
	  		<strong>Utilizar esses dados de teste?</strong>
	  	</p>
	  	<p>
	  		<input type="radio" name="usuarioTeste" value="Sim"> Sim  
	  		&nbsp;<input type="radio" name="usuarioTeste" value="Nao" checked="true"> Não 
	  	</p>
	  	<table>
	  		<tr>
	  			<td width="300px;">
				  	<table>
					  	<tr>
					 		<td>
								<strong>Email Vendedor:</strong> teste@teste.com.br
							</td>
						</tr>
						<tr>
							<td>
								<strong>Senha Vendedor</strong> @#$%ˆ&
							</td>
						</tr>
						<tr>
							<td>
								<strong>Client ID</strong> 123456789
							</td>
						</tr>
						<tr>
							<td>
								<strong>Client Secret</strong> lkhgid5r
							</td>
						</tr>	
						<tr>
							<td>
								<strong>Access Token</strong> fkljdksjf3456787456yfsd543436576uhg
							</td>
						</tr>	
					</table>	  			
	  			</td>
	  			<td>
					<table>
					  	<tr>
					 		<td>
								<strong>Email comprador:</strong> teste@teste.com.br
							</td>
						</tr>
						<tr>
							<td>
								<strong>Senha comprador</strong> @#$%ˆ&
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>										
				  	</table>
	  			</td>
	  		</tr>

	  		<tfoot align="center">
	  			<tr>
	  				<td colspan="2" height="50px">
	  					<button value="Carregar Usuários" class="ch-btn-user">{l s='Load users' mod='mercadopago'}</button>
	  				</td>
	  			</tr>
	  		</tfoot>
	  	</table>

		<p>
			<strong><a href="https://www.mercadopago.com.br/developers/pt/solutions/payments/custom-checkout/test-cards/" target="_blank"> {l s='Credit Card for test' mod='mercadopago'}</a> </strong>
		</p>
	</div>-->

	<div id="Duvidas" class="tabcontent">
	  	<h3>{l s='Question' mod='mercadopago'}</h3>
		<p><strong><a href="https://www.youtube.com/playlist?list=PLl8LGzRu2_sXxChIJm1e0xY6dU3Dj_tNi" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/youtube.png" width="20px;" height="20px">YouTube</a> </strong></p>
		<p><strong><a href="https://www.facebook.com/groups/modulos.mercadopago" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/facebook.png" width="20px;" height="20px">Facebook</a> </strong></p> 


		<p><a href="mailto:developers@mercadopago.com.br?subject=Suport - Prestashop"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/email.png" width="20px;" height="20px">developers@mercadopago.com.br</a> </p>

		<p><a href="https://www.mercadopago.com.br/developers/pt/solutions/payments/basic-checkout/test/test-payments/" target="_blank"><img class="logoCheck" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/credit_card.png" width="20px;" height="20px">{l s='Credit Card for test' mod='mercadopago'}</a></p>
	</div>

	<br>

	<h3> {l s='Notes:' mod='mercadopago'}</h3>
	<h4>{l s='- To obtain your Client Id, Client Secret, Public Key and Access Token please click on your country:' mod='mercadopago'}</h4>
		<a href="https://www.mercadopago.com/mla/account/credentials" target="_blank"><u>{l s='Argentina' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlb/account/credentials" target="_blank"><u>{l s='Brazil' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mco/account/credentials" target="_blank"><u>{l s='Colombia' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlc/account/credentials" target="_blank"><u>{l s='Chile' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlm/account/credentials" target="_blank"><u>{l s='Mexico' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlv/account/credentials" target="_blank"><u>{l s='Venezuela' mod='mercadopago'}</u></a> |
		<a href="https://www.mercadopago.com/mlu/account/credentials" target="_blank"><u>{l s='Uruguay' mod='mercadopago'}</u></a>
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
					<img src="../img/admin/contact.gif" />{l s='Settings - Custom' mod='mercadopago'}
				</legend>
				<label>{l s='Public Key:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_PUBLIC_KEY" value="{$public_key|escape:'htmlall':'UTF-8'}" />
				</div>
				<br />
				<label>{l s='Access Token:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="60" name="MERCADOPAGO_ACCESS_TOKEN" value="{$access_token|escape:'htmlall':'UTF-8'}" />
				</div>
			</fieldset>

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
				<br/>
				<label>{l s='Banner:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="33" name="MERCADOPAGO_CREDITCARD_BANNER" value="{$creditcard_banner|escape:'htmlall':'UTF-8'}" />
				</div>
				<br/>
				<label>{l s='New Checkout: ' mod='mercadopago'}</label>
				<div class="">
					<select name="MERCADOPAGO_CHECKOUT_2" id="checkout_2">
						<option value="true">{l s='Yes' mod='mercadopago'}</option>
						<option value="false" selected>{l s='No' mod='mercadopago'} </option>
					</select>
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
				<br>
				<label>{l s='Custom Text:' mod='mercadopago'}</label>
				<div class="">
					<input type="text" size="50" name="MERCADOPAGO_CUSTOM_TEXT" value="{$custom_text|escape:'htmlall':'UTF-8'}" />
				</div>
				<br>
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
							<img class="logo" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadoenvios_hori.jpg" width="250px;" style="margin-left:-10px;">
						</div>
					</div>
					<br>
					<h3>{l s='Settings Mercado Envios' mod='mercadopago'}</h3>
						<ul>
							<li><h3>{l s='Mercado Envios works only with Checkout Standard' mod='mercadopago'}</h3>
							{if $country == 'MLA'}
									<li><a target="_blank" href="https://www.mercadopago.com.ar/envios">Activa MercadoEnvíos</a></li>
									<li>
									Consulta los <a target="_blank" href="https://www1.oca.com.ar/ocaexpresspak/help/serviviosbasicos.asp">valores admitidos por OCA</a>.</li>
							{/if}
							{if $country == 'MLM'}
								<ul>
									<li>
										<h3><a target="_blank" href="https://www.mercadopago.com.mx/envios">Activa MercadoEnvíos</a></h3>
									</li>
									<li>
										<h3>Consulta los <a target="_blank" href="http://www.dhl.com.mx/content/dam/downloads/language_masters/express/es/shipping/weights_and_dimensions/weights_and_dimensions_es_lm.pdf">valores admitidos por DHL</a>.</h3>
									</li>
								{/if}
								{if $country == 'MLB'}
									<li>
										<h3><a target="_blank" href="https://www.mercadopago.com.br/envios">Ativar MercadoEnvios</a></h3>
									</li>
									<li>
										<h3>Consultar os <a target="_blank" href="http://www.correios.com.br/para-voce/precisa-de-ajuda/limites-de-dimensoes-e-de-peso">valores permitidos pelos Correios</a>.</h3>
									</li>
								{/if}
						</ul>
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
      <fieldset>
          <legend>
              <img src="../img/admin/contact.gif" />{l s='Settings - Mercado Pago Discount' mod='mercadopago'}
          </legend>
          <label>{l s='Discount percent:' mod='mercadopago'}</label>
          <div >
              <input type="text" name="MERCADOPAGO_DISCOUNT_PERCENT" value="{$percent|escape:'htmlall':'UTF-8'}" />
          </div><br />
          <label>{l s='Discount payment methods:' mod='mercadopago'}</label>
          <div >
              <input type="checkbox" name="MERCADOPAGO_ACTIVE_CREDITCARD" {if $active_credicard == 1}checked='checked'{/if} value="1">{l s='Credit card (in cash)' mod='mercadopago'}</input><br />
              <input type="checkbox" name="MERCADOPAGO_ACTIVE_BOLETO" {if $active_boleto == 1}checked='checked'{/if} value="1">{l s='Ticket' mod='mercadopago'}</input>
          </div>
          <br />
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
					<img src="../img/admin/contact.gif"/>{l s='Point' mod='mercadopago'}
				</legend>

				<table>
					<tr>
						<td>
							<label>{l s='Enable payments with POINT: ' mod='mercadopago'}</label>
							<select name="MERCADOPAGO_POINT" id="point_active">
								<option value="true">{l s='Yes' mod='mercadopago'} </option>
								<option value="false">{l s='No' mod='mercadopago'} </option>
							</select>
						</td>
						<td>
							<img class="logo" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadopago_point_2.jpg" width="100px;">
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend>
					<img src="../img/admin/contact.gif" />{l s='Settings' mod='mercadopago'}
				</legend>

				<label>{l s='Log: ' mod='mercadopago'}</label>
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

		if (document.getElementById("checkout_2")){
			document.getElementById("checkout_2").value = "{$checkout_2|escape:'htmlall':'UTF-8'}";
		} else {
			document.getElementById("checkout_2").value = "false";
		}

		if (document.getElementById("coupon_active")){
			document.getElementById("coupon_active").value = "{$coupon_active|escape:'htmlall':'UTF-8'}";
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
	});

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
	// Get the element with id="defaultOpen" and click on it
	document.getElementById("defaultOpen").click();
</script>

