{** * 2007-2015 PrestaShop * * NOTICE OF LICENSE * * This source file is
subject to the Open Software License (OSL 3.0) * that is bundled with
this package in the file LICENSE.txt. * It is also available through the
world-wide-web at this URL: * http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to *
obtain it through the world-wide-web, please send an email * to
license@prestashop.com so we can send you a copy immediately. * *
DISCLAIMER * * Do not edit or add to this file if you wish to upgrade
PrestaShop to newer * versions in the future. If you wish to customize
PrestaShop for your * needs please refer to http://www.prestashop.com
for more information. * * @author MercadoPago * @copyright Copyright
(c) MercadoPago [http://www.mercadopago.com] * @license
http://opensource.org/licenses/osl-3.0.php Open Software License (OSL
3.0) * International Registered Trademark & Property of MercadoPago *}
<style type="text/css">
.ui-widget-header {
	border: 1px solid #2D3277;
	background: transparent
		url("https://imgmp.mlstatic.com/org-img/banners/ve/medios/575X40.jpg")
		no-repeat;
	color: transparent;
	font-weight: bold;
}
</style>
<div class="lightbox" id="text">
	<div class="box">
		<div class="content">
			<div class="processing">
				<span>{l s='Processing...' mod='mercadopago'}</span>
			</div>
		</div>

	</div>
</div>

<link
	href="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/css/bootstrap.css"
	rel="stylesheet" type="text/css" />

<link
	href="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/css/dd.css"
	rel="stylesheet" type="text/css" />

<div class="mp-module">

{if $percent != 0 && count($percent) > 0}

    <div class="row">

			{if $credit_card_discount > 0 && $boleto_discount == 0}

	        <h4 class="payment-label">{l s='Save' mod='mercadopago'}&nbsp;<span style="color: red;">{$percent|escape:'htmlall':'UTF-8'}% </span>{l s='discount payment by Mercado Pago with credit card in cash.' mod='mercadopago'}</h4>

			{elseif $boleto_discount > 0 && $credit_card_discount == 0}

	        <h4 class="payment-label">{l s='Save' mod='mercadopago'}&nbsp;<span style="color: red;">{$percent|escape:'htmlall':'UTF-8'}%</span> {l s='discount payment by Mercado Pago with ticket.' mod='mercadopago'}</h4>

			{elseif $credit_card_discount > 0 && $boleto_discount > 0}

	        <h4 class="payment-label">{l s='Save' mod='mercadopago'}&nbsp;<span style="color: red;">{$percent|escape:'htmlall':'UTF-8'}%</span> {l s='discount payment by Mercado Pago with ticket and credit card  in cash.' mod='mercadopago'}</h4>
			{/if}

    </div>

{/if}

	{if $coupon_active == 'true' }

	<div class="row">
		<div class="col-xs-12 col-md-6">
			<div class="mp-form-custom">

				<div class="row">
					<div class="col titleCoupon">
						<span class="payment-label">

						{if $country eq "MLB"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MLB/CUPOM_MLB.jpg">
						{elseif $country eq "MLM"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MLM/CUPOM_MLM.jpg">
						{elseif $country eq "MLA"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MLA/CUPOM_MLA.jpg">
						{elseif $country eq "MLC"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MLC/CUPOM_MLC.jpg">
						{elseif $country eq "MCO"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MCO/CUPOM_MCO.jpg">
						{elseif $country eq "MLV"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MLV/CUPOM_MLV.jpg">
						{elseif $country eq "MPE"}
							<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/MPE/CUPOM_MPE.jpg">
						{/if}

						</span>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<label for="couponCode">{l s='Coupon' mod='mercadopago'} <strong>Mercado
								Pago:</strong>
						</label> <input type="text" size="19" name="mercadopago_coupon"
							id="mercadopago_coupon" /> <span id="removerDesconto"
							class="btn btn-danger btn-sm">{l s='Remove'
							mod='mercadopago'}</span> <span id="aplicarDesconto"
							class="btn btn-primary btn-sm">{l s='Apply'
							mod='mercadopago'}</span> <span id="aplicarDescontoDisable"
							class="btn btn-default btn-disabled btn-sm"><i
							class="fa fa-spinner fa-spin"></i> {l s='Waiting'
							mod='mercadopago'}...</span>
					</div>
				</div>

				<div class="alert alert-danger" style="margin-top: 10px;"
					id="error_alert" role="alert">...</div>

				<br>
				<ul class="couponApproved nav nav-pills nav-stacked">
					<li>
						<p class="couponApproved ch-form-row discount-link">
							{l s='You save' mod='mercadopago'} <b>&nbsp;<span
								id="amount_discount"></span></b> {l s='with the exclusive discount'
							mod='mercadopago'} <strong style="color: #02298D;">Mercado
								Pago</strong>
						</p>
						<p id="totalCompra">
							{l s='Total of your purchase' mod='mercadopago'}: <b>&nbsp;<span
								id="total-amount">{$amount|escape:'htmlall':'UTF-8'}</span></b>.
						</p>
						<p class="couponApproved">
							<strong>{l s='Total of discount in your purchase'
								mod='mercadopago'}:</strong> <b style="font-size: 20px">&nbsp;<span
								class="total_amount_discount" id="total_amount_discount"
								alt="decimal"></span><span style="color: red;">*</span>
							</b>.
						</p>
						<p class="couponApproved">
							<span style="color: red;">*</span><label style="font-size: 12px;">{l
								s='Upon approval of the purchase.' mod='mercadopago'}</label>
						</p>
						<h6 class="couponApproved">
							<a href="" id="mercadopago_coupon_termsTicket" class="alert-link"
								target="_blank"><strong style="text-decoration: underline;">{l
									s='See conditions' mod='mercadopago'}</strong></a>
						</h6>
					</li>
				</ul>
			</div>
		</div>
	</div>

	{/if}
{if $mercadoenvios_activate == 'false' && $creditcard_disable == 'false'}
	<div class="card row">
		<div class="mp-form">
			<div class="row">
				<div class="col title">
					<span class="payment-label">{l s='CREDIT CARD'
						mod='mercadopago'} </span> <br/> <span class="poweredby">{l s='Powered
						by' mod='mercadopago'}</span> <img class="logo"
						src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">
				</div>
				{if !empty($creditcard_banner)}
				<div class="col title">
					<img src="{$creditcard_banner|escape:'htmlall':'UTF-8'}"
						class="mp-creditcard-banner" />
				</div>
				{/if}
			</div>
			<div class="row">
				<label class="ch-form-hint">* {l s='Required fields'
					mod='mercadopago'}</label>
			</div>
			<form action="" method="post" id="form-pagar-mp">

				<input id="opcaoPagamentoCreditCard" type="hidden"
					name="opcaoPagamentoCreditCard" value="" /> <input
					id="customerID" type="hidden" name="customerID"
					value="{$customerID|escape:'htmlall':'UTF-8'}" /> <input id="amount"
					type="hidden" value="{$amount|escape:'htmlall':'UTF-8'}" /> <input
					id="payment_method_id" type="hidden" name="payment_method_id" />
				<input id="payment_type_id" type="hidden" name="payment_type_id" />
				<input name="mercadopago_coupon" type="hidden" class="mercadopago_coupon_ticket" />
				<input type="hidden" id="card_token_id" name="card_token_id"/>

				<div id="customerCardsAll">
					<div class="row" id="myCreditCard">
						<div class="col">
							<label for="id-card-number">{l s='My credit cards: '
								mod='mercadopago'}<em>*</em>
							</label>&nbsp;<select id="id-customerCards" name="customerCards" type="text"  data-checkout='cardId'></select>
							<div id="id-card-number-status-cust" class="status"></div>
						</div>
					</div>

					<div id="customerCardsDiv">

						<div class="row">
							<div class="col">
								<label for="id-security-code-cust" style="font-weight: 700;">{l
									s='Security Code: ' mod='mercadopago'}<em>*</em>
								</label> <input id="id-security-code-cust" data-checkout="securityCode" data-checkout="securityCode"
									type="text" maxlength="4" /> <img
									src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/cvv.png"
									class="cvv" />
								<div id="id-security-code-status-cust" class="status"></div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<label for="id-installments">{l s='Installments: '
									mod='mercadopago'}<em>*</em>
								</label> <select id="id-installments-cust" name="installmentsCust" type="text"></select>
								<div id="id-installments-status-cust" class="status"></div>
							</div>

							<div class="col">
								<div class="mp-text-cft">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col">
								<div class="mp-text-tea">
								</div>
							</div>
						</div>

					</div>
				</div>

				<div id="cardDiv">
					{if $country == 'MLM' || $country == 'MPE'}
					<div class="row">
						<div class="col">
							<label for="id-card-number">{l s='Card Type: '
								mod='mercadopago'}<em>*</em>
							</label>
								<select id="credit_option" name="credit_option" type="text"></select>
						</div>
					</div>
					{/if}


					<div class="row">
						<div class="col">
							<label for="id-card-number">{l s='Card number: '
								mod='mercadopago'}<em>*</em>
							</label> <input id="id-card-number" data-checkout="cardNumber"
								type="text" />
							<div id="id-card-number-status" class="status"></div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<label for="id-card-expiration-month">{l s='Expiration:'
								mod='mercadopago'}<em>*</em>
							</label> <select id="id-card-expiration-month" class="small-select"
								data-checkout="cardExpirationMonth" type="text"></select>
						</div>
						<div class="col">
							<select id="id-card-expiration-year" class="small-select"
								data-checkout="cardExpirationYear" type="text"></select>
							<div id="id-card-expiration-year-status" class="status"></div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<label for="id-card-holder-name">{l s='Card Holder Name:' mod='mercadopago'}<em>*</em>
							</label> <input id="id-card-holder-name" data-checkout="cardholderName"
								type="text" name="cardholderName" />
							<div id="id-card-holder-name-status" class="status"></div>

						</div>

					</div>
					<div class="row">
						<div class="col">
							<label for="id-security-code" style="font-weight: 700;">{l
								s='Security Code: ' mod='mercadopago'}<em>*</em>
							</label> <input id="id-security-code" data-checkout="securityCode"
								type="text" maxlength="4" //> <img
								src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/cvv.png"
								class="cvv" />
							<div id="id-security-code-status" class="status"></div>
						</div>
					</div>
					{if $country == 'MLB'}
					<div class="row">
						<div class="col">
							<label for="id-doc-number">{l s='CPF: '
								mod='mercadopago'}<em>*</em>
							</label> <input id="id-doc-number" data-checkout="docNumber" type="text"
								maxlength="11" />
							<div id="id-doc-number-status" class="status"></div>
							<input name="docType" data-checkout="docType" type="hidden"
								id="id-docType" value="CPF" />
						</div>
					</div>
					{elseif $country == 'MLM' || $country == 'MLA' || $country == 'MPE' || $country == 'MLU'}
					<div class="row">
						<div class="col">
							<label class="issuers-options" for="id-issuers-options">{l
								s='Bank: ' mod='mercadopago'}
							</label> <select class="issuers-options" id="id-issuers-options"
								name="issuersOptions" type="text"></select>
						</div>
					</div>
					{/if} {if $country == 'MLA' || $country == 'MCO' || $country ==
					'MLV'  || $country == 'MPE' || $country == 'MLU'}
					<div class="row">
						<div class="col">
							<label for="docType">{l s='Document type: '
								mod='mercadopago'}<em>*</em>
							</label> <select name="docType" type="text" class="document-type"
								id="id-docType" style="width: 92px;" data-checkout="docType"></select>
						</div>
						<div class="col">
							<input id="id-doc-number" name="docNumber" style="width: 102px;"
								data-checkout="docNumber" type="text" />
							<div id="id-doc-number-status" class="status"></div>
						</div>
					</div>

					<div class="row"></div>
					{/if} {if $country == 'MLC'}
					<div class="row">
						<div class="col">
							<label for="id-doc-number">RUT: <em>*</em></label> <input
								type="hidden" name="docType" id="docType" value="RUT"
								id="id-docType" data-checkout="docType"> <input
								type="text" id="id-doc-number" data-checkout="docNumber"
								maxlength="10" size="14" placeholder="11111111-1">
							<div id="id-doc-number-status" class="status"></div>
						</div>
					</div>
					{/if}

					<div class="row">
						<div class="col">
							<label for="id-installments">{l s='Installments: '
								mod='mercadopago'}<em>*</em>
							</label> <select id="id-installments" name="installments" type="text"></select>
							<div id="id-installments-status" class="status"></div>
						</div>

						<div class="col">
							<div class="mp-text-cft">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col">
							<div class="mp-text-tea">
							</div>
						</div>
					</div>

					<div class="row" style="display: none;">
						<div class="col-xs-12">
							<p class="payment-errors"></p>
						</div>
					</div>
				</div>
				<div style="text-align: center;">
					{if $country != "MLB"}
					<button class="ch-btn ch-btn-big"
						value="{l s=' Confirm payment' mod='mercadopago'}" type="submit"
						id="btnSubmit">{l s=' Confirm payment'
						mod='mercadopago'}</button>
					{else}
					<button class="ch-btn ch-btn-big es-button submit"
						value="{l s=' Confirm payment' mod='mercadopago'}" type="submit"
						id="btnSubmit">{l s=' Confirm payment'
						mod='mercadopago'}</button>
					{/if}
				</div>
			</form>
		</div>
	</div>
	{/if}

	{if $country == 'MLB'}
		{foreach from=$offline_payment_settings key=offline_payment item=value}
			{if $boleto_disable == "false"  && $mercadoenvios_activate == 'false' && $offline_payment != 'pec'}
			<form action="{$custom_action_url|escape:'htmlall':'UTF-8'}" method="post"
							id="form-{$offline_payment|escape:'htmlall':'UTF-8'}" class="formTicket" onsubmit="return submitBoletoFebraban();">
				<input name="email" type="hidden" value="{$ticket.email|escape:'htmlall':'UTF-8'}"/> 
				<input name="mercadopago_coupon" type="hidden"
					class="mercadopago_coupon_ticket" /> 

				<input type="hidden" name="typeDocument" id="typeDocument" value="CPF" />

				<input
					name="payment_method_id" type="hidden"
					value="{$offline_payment|escape:'htmlall':'UTF-8'}" />
				
				<div class="mp-form">
						<div class="row">
							<div class="col title">
								<span class="payment-label">{l s='BOLETO'
									mod='mercadopago'} </span> <br/> <span class="poweredby">{l s='Powered
									by' mod='mercadopago'}</span> <img class="logo"
									src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">
							</div>
							<div class="col title">
								<img src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/boleto.png" />
							</div>
						</div>

						<div class="alert">
						  INFORMAÇÕES SOLICITADAS EM CONFORMIDADE COM AS NORMAS DAS CIRCULARES NRO. 3.461/09, 3.598/12 E 3.656/13 DO BANCO CENTRAL DO BRASIL.
						</div>
						<div class="">
						    <div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="cpf">CPF/CNPJ:<em style="color: red;">*</em>
										</label> <input  class="form-control" name="cpfcnpj" id="cpfcnpj" required="true" type="text" maxlength="18" value="{$ticket.cpf|escape:'htmlall':'UTF-8'}"/>
										<div id="cpf-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="firstname" id="labelFirstname">Nome:</label> <em style="color: red;">*</em>
										<input  class="form-control" id="firstname" name="firstname" required="true" type="text" maxlength="50" value="{$ticket.firstname|escape:'htmlall':'UTF-8'}" />
										<div id="firstname-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="lastname" id="labelLastname">Sobrenome:<em style="color: red;">*</em>
										</label> <input  class="form-control" id="lastname" name="lastname" type="text" maxlength="50" value="{$ticket.lastname|escape:'htmlall':'UTF-8'}"/>
										<div id="lastname-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-8">
									<div class="form-group">
										<label for="address">Endereço:<em style="color: red;">*</em>
										</label> <input class="form-control" id="address"  name="address" style="max-width: none;" required="true" type="text" maxlength="50" value="{$ticket.address|escape:'htmlall':'UTF-8'}"/>
										<div id="address-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="number">Número:<em style="color: red;">*</em>
										</label> <input  class="form-control" id="number" name="number" required="true" type="text" maxlength="50" value="{$ticket.number|escape:'htmlall':'UTF-8'}"/>
										<div id="number-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="city">Cidade:<em style="color: red;">*</em>
										</label> <input  class="form-control" required="true" id="city" name="city" type="text" maxlength="50" value="{$ticket.city|escape:'htmlall':'UTF-8'}"/>
										<div id="city-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="state">Estado:<em style="color: red;">*</em></label>
									    <select class="form-control" id="state" required="true" name="state">
			                              <option value="{$ticket.state|escape:'htmlall':'UTF-8'}" selected="selected">{$ticket.state|escape:'htmlall':'UTF-8'}</option>
			                              <option value="AC">Acre</option>
			                              <option value="AL">Alagoas</option>
			                              <option value="AP">Amapá</option>
			                              <option value="AM">Amazonas</option>
			                              <option value="BA">Bahia</option>
			                              <option value="CE">Ceará</option>
			                              <option value="DF">Distrito Federal</option>
			                              <option value="ES">Espírito Santo</option>
			                              <option value="GO">Goiás</option>
			                              <option value="MA">Maranhão</option>
			                              <option value="MT">Mato Grosso</option>
			                              <option value="MS">Mato Grosso do Sul</option>
			                              <option value="MG">Minas Gerais</option>
			                              <option value="PA">Pará</option>
			                              <option value="PB">Paraíba</option>
			                              <option value="PR">Paraná</option>
			                              <option value="PE">Pernambuco</option>
			                              <option value="PI">Piauí</option>
			                              <option value="RJ">Rio de Janeiro</option>
			                              <option value="RN">Rio Grande do Norte</option>
			                              <option value="RS">Rio Grande do Sul</option>
			                              <option value="RO">Rondônia</option>
			                              <option value="RA">Roraima</option>
			                              <option value="SC">Santa Catarina</option>
			                              <option value="SP">São Paulo</option>
			                              <option value="SE">Sergipe</option>
			                              <option value="TO">Tocantins</option>
									    </select>
									    <div id="state-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="postcode">Cep:<em style="color: red;">*</em>
										</label> <input  class="form-control" required="true" id="postcode" name="postcode" type="text" maxlength="50" value="{$ticket.postcode|escape:'htmlall':'UTF-8'}"/>
										<div id="postcode-status" class="status_febraban">Campo obrigatório</div>
									</div>
								</div>
							</div>
						</div>
						<br/>
						<div style="text-align: center;">
							<button class="ch-btn ch-btn-big es-button submit create-boleto-febraban"
								value="Gerar Boleto" type="submit"
								id="btnSubmitFebraban">Gerar Boleto</button>
						</div>
				</div>
			</form>
			{/if}
		{/foreach}
	{/if}

	{if $country == 'MLM' || $country == 'MPE' || $country ==
	'MLA' || $country == 'MLC' || $country == 'MCO' || $country == 'MLV' || $country == 'MLU'}
	{foreach from=$offline_payment_settings key=offline_payment item=value}
	<label>"{$value.disabled|escape:'htmlall':'UTF-8'}"</label>
	{if $value.disabled  != "true" && $mercadoenvios_activate == "false"}
	<div class="row">
		<div class="col-xs-12 col-md-6">
			<a href="javascript:void(0);"
				id="id-{$offline_payment|escape:'htmlall':'UTF-8'}"
				onclick="enviarBoleto(this, 'id-create-{$offline_payment|escape:'htmlall':'UTF-8'}')">

				<div class="mp-form-boleto hover">
					<div class="row boleto">
						<div class="col">
							<img src="{$value.thumbnail|escape:'htmlall':'UTF-8'}">

							<span class="payment-label">{$value.name|upper|escape:'htmlall':'UTF-8'} </span><br> <span
								class="poweredby">{l s='Powered by' mod='mercadopago'}</span>
								<img
								class="logo"
								src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">
						</div>
						<form action="{$custom_action_url|escape:'htmlall':'UTF-8'}" method="post"
							id="form-{$offline_payment|escape:'htmlall':'UTF-8'}" class="formTicket">

							<input name="mercadopago_coupon" type="hidden"
								class="mercadopago_coupon_ticket" /> <input
								name="payment_method_id" type="hidden"
								value="{$offline_payment|escape:'htmlall':'UTF-8'}" /> <input
								type="submit" class="create-boleto"
								id="id-create-{$offline_payment|escape:'htmlall':'UTF-8'}">

						</form>
					</div>
				</div>
			</a>
		</div>
	</div>
	{/if}
	{/foreach}
	{/if}

	{if $standard_active eq 'true'}

	<div class="row">
		<div class="col-xs-12 col-md-6">
			{if $window_type != 'iframe'} <a
				href="{$standard_action_url|escape:'htmlall':'UTF-8'}" id="id-standard"
				mp-mode="{$window_type|escape:'htmlall':'UTF-8'}" name="MP-Checkout">
				<div class="mp-form hover">
					<div class="row">
						<div class="col">
							<img
								src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo_120_31.png"
								id="id-standard-logo"> <img
								src="{$standard_banner|escape:'htmlall':'UTF-8'}"
								class="mp-standard-banner" /> <span
								class="payment-label standard"><h5> {$custom_text|escape:'htmlall'}</h5> </span>
						</div>
					</div>
				</div>
			</a> {else}
			<div class="mp-form">
				<iframe src="{$preferences_url|escape:'htmlall':'UTF-8'}" name="MP-Checkout"
					width="{$iframe_width|escape:'htmlall':'UTF-8'}"
					height="{$iframe_height|escape:'htmlall':'UTF-8'}" frameborder="0">
				</iframe>
			</div>
			{/if}
		</div>
	</div>
	{/if}
</div>

<script type="text/javascript"
	src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/jquery.dd.js"></script>

<script type="text/javascript"
	src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/util.js"></script>

<script type="text/javascript">

	var credit_card_discount = "{$credit_card_discount|escape:'javascript':'UTF-8'}";
	var orderTotal = "{$orderTotal|escape:'javascript':'UTF-8'}";

	var country = "{$country|escape:'javascript':'UTF-8'}";

	function loadSubDocType(value) {
		var options = [];
		var subDocType = $("#subDocType");

		if (value == "Pasaporte") {
			subDocType.hide();
		} else if (value == "CI") {
			options.push('<option value="V">V</option>');
			options.push('<option value="E">E</option>');
			subDocType.show();
		} else if (value == "RIF") {
			options.push('<option value="J">J</option>');
			options.push('<option value="P">P</option>');
			options.push('<option value="V">V</option>');
			options.push('<option value="E">E</option>');
			options.push('<option value="G">G</option>');
			subDocType.show();
		}

		subDocType.html(options.join(''));

	}
	// first load force to clear all fields
	$("#id-card-number").val("");
	$("#id-security-code").val("");
	$("#id-card-holder-name").val("");
	$("#id-doc-number").val("");

	var cardBefore = "";

	$("input[data-checkout='cardNumber'], input[name='card-types']").bind(
			"change", function() {
				loadCard();
			});

	function loadCard() {
		if ($("#id-card-number").val() == cardBefore) {
			return;
		}

		cardBefore = $("#id-card-number").val();

		//limpa o cupom
		//removerCoupon();
		//limpa validação
		$("#id-card-number-status").html("");
		$("#id-card-number").removeClass("form-error");

		var bin = getBin();
		if (bin.length == 6) {

			var json = {}
			json.bin = bin;
			Mercadopago.getPaymentMethod(json, setPaymentMethodInfo);
			if (country != "MLM") {
				Mercadopago.getIdentificationTypes();
			}


		} else if (bin.length < 6) {
			$("#id-card-number").css('background-image', '');
			$("#id-installments").html('');
			if (country == "MLM" || country == "MPE") {
				$("#id-issuers-options").html('');
			}

		}
	}

	function getBin() {
		var card = $("#id-card-number").val().replace(/ /g, '').replace(/-/g,
				'').replace(/\./g, '');
		var bin = card.substr(0, 6);

		return bin;

	}

	// Estabeleça a informação do meio de pagamento obtido
	function setPaymentMethodInfo(status, result) {
		if (status != 404 && status != 400 && result != undefined) {
			//adiciona a imagem do meio de pagamento
			var payment_method = result[0];
			var amount = returnAmount();
			var bin = getBin();
			if (country === "MLM" || country === "MLA" || country === "MPE" || country === 'MLU') {
				// check if the issuer is necessary to pay
				var issuerMandatory = false, additionalInfo = result[0].additional_info_needed;

				for (var i = 0; i < additionalInfo.length; i++) {
					if (additionalInfo[i] == "issuer_id") {
						issuerMandatory = true;
					}
				}

				if (issuerMandatory) {
					payment_method_issue = 0;
					if (country === "MLM" || country === "MPE") {
						payment_method_issue = document.getElementById("credit_option").value;
					} else {
						payment_method_issue = result[0].id;
					}
					Mercadopago.getIssuers(payment_method_issue, showCardIssuers);
					//$("#id-issuers-options").bind("change", function() {
						//setInstallmentsByIssuerId(status, result)
					//});
				} else {
					document.querySelector("#id-issuers-options").options.length = 0;
					document.querySelector("#id-issuers-options").style.display = 'none';
					document.querySelector(".issuers-options").style.display = 'none';
				}
			}

			$("#id-card-number").css(
					"background",
					"url(" + payment_method.secure_thumbnail
							+ ") 98% 50% no-repeat");
			$("#payment_method_id").val(payment_method.id);

			$("#payment_type_id").val(payment_method.payment_type_id);

			loadInstallments();

		} else {
			$("#id-card-number").css('background-image', '');
			$("#id-installments").html('');
		}
	};
	function returnAmount() {
		if ($("#amount_discount").text() != "") {
			return $("#total_amount_discount").text();
		} else {
			return $("#amount").val();
		}

	}
	function setInstallmentsByIssuerId(status, response) {
		var amount = returnAmount();

		var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
		var issuerId = null;
		var bin = null;

		if (opcaoPagamento == "Customer") {
			var card = document.querySelector('select[data-checkout="cardId"]');
			bin = card[card.options.selectedIndex].getAttribute('first_six_digits');
		}else{
			issuerId = document.querySelector('#id-issuers-options').value, amount;
			bin = getBin();
		}
		if (issuerId == '-1') {
			console.info("Entrou aqui");
			return;
		}

		//var jsonPaymentMethod = getPaymentMethods();
		//"payment_method_id" : jsonPaymentMethod.payment_method_id,
		//"payment_type_id" : jsonPaymentMethod.payment_type_id,
		Mercadopago.getInstallments({

			"bin" : bin,
			"amount" : amount,
			"issuer_id" : issuerId
		}, setInstallmentInfo);
	};

	//Mostre as parcelas disponíveis no div 'installmentsOption'
	function setInstallmentInfo(status, installments) {
		var html_options = "";

		if (status != 404 && status != 400 && installments.length > 0) {

			html_options += "<option value='' selected>{l s='Choice' mod='mercadopago'}...</option>";
			var installments = installments[0].payer_costs;
			$.each(installments, function(key, value) {

				// tax resolution 51/2017 arg
				var dataInput = "";
				var tax = value.labels;
				if(tax.length > 0){
					for (var l = 0; l < tax.length; l++) {
						if (tax[l].indexOf('CFT_') !== -1){
							dataInput = 'data-tax="' + tax[l] + '"'
						}
					}
				}


				if(value.installments == 1 && credit_card_discount == 1){
					html_options += "<option value='"+ value.installments + "' "+ dataInput +">"+ value.installments +" parcela de R$ "+ orderTotal +" ("+ orderTotal +") </option>";
				}else{
					html_options += "<option value='"+ value.installments + "' "+ dataInput +">"
						+ value.recommended_message + "</option>";
				}

			});
		} else {
			console.error("Installments Not Found.");
		}

		var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
		if (opcaoPagamento == "Customer") {
			$("#id-installments-cust").html(html_options);
			taxesInstallmentsCust();
		} else {
			$("#id-installments").html(html_options);
			taxesInstallments();
		}

	};

	function showCardIssuers(status, issuers) {

		var issuersSelector = null;
		var id_issuers_options = null;
		var issuers_options = null;

		var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
		if (opcaoPagamento == "Customer") {
			issuersSelector = document.querySelector("#id-issuers-options-cust"), fragment = document
			.createDocumentFragment();
			id_issuers_options = document.querySelector("#id-issuers-options-cust");
			issuers_options = document.querySelector(".issuers-options-cust");
		} else {
			issuersSelector = document.querySelector("#id-issuers-options"), fragment = document
			.createDocumentFragment();
			id_issuers_options = document.querySelector("#id-issuers-options");
			issuers_options = document.querySelector(".issuers-options");
		}


		if (issuers.length > 0) {
			issuersSelector.options.length = 0;
			var option = new Option("{l s='Choose' mod='mercadopago'}...", '-1');
			fragment.appendChild(option);
			for (var i = 0; i < issuers.length; i++) {
				if (issuers[i].name != "default") {
					option = new Option(issuers[i].name, issuers[i].id);
				} else {
					option = new Option("Otro", issuers[i].id);
				}
				fragment.appendChild(option);
			}
			issuersSelector.appendChild(fragment);
			issuersSelector.removeAttribute('disabled');

			id_issuers_options.removeAttribute('style');
			issuers_options.removeAttribute('style');
		}

	};

	if (country === "MLM" || country === "MLA" || country === "MPE" || country === "MLU") {
		$("#id-issuers-options").change(function() {

			var issuerId = $('#id-issuers-options').val();
			var amount = returnAmount();

			Mercadopago.getInstallments({
				"bin" : getBin(),
				"amount" : amount,
				"issuer_id" : issuerId
			}, setInstallmentInfo);


		});
	}

	if (country === "MLA") {
		$("#id-installments").change(taxesInstallments);
		$("#id-installments-cust").change(taxesInstallmentsCust);
		$(".mp-text-cft").show();
		$(".mp-text-tea").show();
	}

	function taxesInstallments(){
		var selectorInstallments = document.querySelector("#id-installments");
		showTaxes(selectorInstallments);
	}
	function taxesInstallmentsCust(){
		var selectorInstallments = document.querySelector("#id-installments-cust");
		showTaxes(selectorInstallments);
	}

	function showTaxes(selectorInstallments){
		var tax = null;

		if(selectorInstallments.selectedIndex > -1){
		  tax = selectorInstallments.options[selectorInstallments.selectedIndex].getAttribute('data-tax');
		}

		var cft = ""
		var tea = ""

		if(tax != null){
			var tax_split = tax.split('|');
			cft = tax_split[0].replace('_', ' ');
			tea = tax_split[1].replace('_', ' ');

			if(cft == "CFT 0,00%" && tea == "TEA 0,00%"){
				cft = ""
				tea = ""
			}

		}

		$(".mp-text-cft").html(cft);
		$(".mp-text-tea").html(tea);

	 }

	function disabledSubmit(disabled) {
		if (disabled) {
			$(".submit").attr("disabled", "true");
		} else {
			$(".submit").removeAttr("disabled");
		}

	}

	disabledSubmit(false);

	var submit = false;
	$("#form-pagar-mp")
			.submit(
					function(event) {
						console.debug("entro no submit");
						disabledSubmit(true);
						event.preventDefault();
						clearErrorStatus();

						if (!validate()) {
							disabledSubmit(false);
							event.preventDefault();
							submit = false;
						} else {
							var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
							if (opcaoPagamento == "Customer") {
						       	var $form = document.querySelector('#customerCardsAll');

						     	Mercadopago.createToken($form, function (status, response) {

						     		if (response.error) {
						     			disabledSubmit(false);
										$.each(response.cause, function(p,e) {
											switch (e.code) {
											case "E203":
											case "E302":
												submit = false;
												$("#id-security-code-status-cust").html("{l s='CVV invalid' mod='mercadopago'}");
												$("#id-security-code-cust").addClass("form-error");
												break;

											}

										});

										if (!submit) {
											event.preventDefault();
										}
								      } else {
								      		$(".lightbox").show();

									      	$('#card_token_id').val(response.id);

											document.getElementById("form-pagar-mp").action = "{$custom_action_url|escape:'quotes':'UTF-8'}";
											document.getElementById("form-pagar-mp").submit();
								      }
						     	});


							} else {
							var $form = $('#form-pagar-mp');
							var $cardDiv = $('#cardDiv');

							Mercadopago
									.createToken(
											$cardDiv,
											function(status, response) {
												if (response.error) {
													disabledSubmit(false);
													submit = false;
													event.preventDefault();
													$
															.each(
																	response.cause,
																	function(p,
																			e) {
																		switch (e.code) {
																		case "E301":
																			$(
																					"#id-card-number-status")
																					.html(
																							"{l s='Card invalid' mod='mercadopago'}");
																			$(
																					"#id-card-number")
																					.addClass(
																							"form-error");
																			break;
																		case "E302":
																			$(
																					"#id-security-code-status")
																					.html(
																							"{l s='CVV invalid' mod='mercadopago'}");
																			$(
																					"#id-security-code")
																					.addClass(
																							"form-error");
																			break;
																		case "325":
																		case "326":
																			$(
																					"#id-card-expiration-year-status")
																					.html(
																							"{l s='Date invalid' mod='mercadopago'}");
																			$(
																					"#id-card-expiration-month")
																					.addClass(
																							"boxshadow-error");
																			$(
																					"#id-card-expiration-year")
																					.addClass(
																							"boxshadow-error");
																			break;
																		case "316":
																		case "221":
																			$(
																					"#id-card-holder-name-status")
																					.html(
																							"{l s='Name invalid' mod='mercadopago'}");
																			$(
																					"#id-card-holder-name")
																					.addClass(
																							"form-error");
																			break;
																		case "324":
																		case "214":
																			$(
																					"#id-doc-number-status")
																					.html(
																							"{l s='Document invalid' mod='mercadopago'}");
																			$(
																					"#id-doc-number")
																					.addClass(
																							"form-error");
																			break;
																		}
																	});
												} else {
													$(".lightbox").show();
													submit = true;
													var card_token_id = response.id;

													var jsonPaymentMethod = getPaymentMethods();

													document.getElementById("payment_method_id").value = jsonPaymentMethod.payment_method_id;
													document.getElementById("payment_type_id").value = jsonPaymentMethod.payment_type_id;

													$form
															.append($(
																	'<input type="hidden" id="card_token_id" name="card_token_id"/>')
																	.val(
																			card_token_id));

													var cardNumber = $(
															"#id-card-number")
															.val();

													var lastFourDigits = cardNumber
															.substring(cardNumber.length - 4);
													$form
															.append($('<input name="lastFourDigits" type="hidden" value="' + lastFourDigits + '"/>'));
													document
															.getElementById("form-pagar-mp").action = "{$custom_action_url|escape:'quotes':'UTF-8'}";
													document.getElementById(
															"form-pagar-mp")
															.submit();
												}

											})

						}
					}
				});

	var submit = false;

	function validate() {
		var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();

		if (opcaoPagamento == "Customer") {

			if ($("#id-customerCards").val().length == 0) {
				$("#id-card-number-status-cust").html(
						"{l s='Card invalid' mod='mercadopago'}");
				$( "#id-customerCards_msdd" ).addClass("form-error");
			}
			if ($("#id-security-code-cust").val().length == 0) {
				$("#id-security-code-status-cust").html(
						"{l s='CVV invalid' mod='mercadopago'}");
				$("#id-security-code-cust").addClass("form-error");
			}

			if ($("#id-installments-cust").val() == null
					|| $("#id-installments-cust").val().length == 0) {
				$("#id-installments-status-cust").html(
						"{l s='Installments invalid' mod='mercadopago'}");
				$("#id-installments-cust").addClass("form-error");
			}


			if ($("#id-installments-cust").val() == null
					|| $("#id-installments-cust").val().length == 0
					|| $("#id-security-code-cust").val().length == 0
					|| $("#id-customerCards").val().length == 0
					) {
				return false;
			}
			return true;
		}

		if ($("#id-card-number").val().length == 0) {
			$("#id-card-number-status").html(
					"{l s='Card invalid' mod='mercadopago'}");
			$("#id-card-number").addClass("form-error");
		}

		if ($("#id-card-holder-name").val().length == 0) {
			$("#id-card-holder-name-status").html(
					"{l s='Name invalid' mod='mercadopago'}");
			$("#id-card-holder-name").addClass("form-error");
		}

		if ($("#id-security-code").val().length == 0) {
			$("#id-security-code-status").html(
					"{l s='CVV invalid' mod='mercadopago'}");
			$("#id-security-code").addClass("form-error");
		}

		if ($("#id-docType").val() == null || $("#id-docType").val() == "") {
			$("#id-docType").addClass("form-error");
		}

		if (country != "MLM") {
			if ($("#id-doc-number").val().length == 0) {
				$("#id-doc-number-status").html(
						"{l s='Document invalid' mod='mercadopago'}");
				$("#id-doc-number").addClass("form-error");
			}
		} else {

		}

		if ($("#id-installments").val() == null
				|| $("#id-installments").val().length == 0) {
			$("#id-installments-status").html(
					"{l s='Installments invalid' mod='mercadopago'}");
			$("#id-installments").addClass("form-error");
		}

		if ($("#id-installments").val() == null
				|| $("#id-installments").val().length == 0
				|| $("#id-security-code").val().length == 0
				|| $("#id-card-holder-name").val().length == 0
				|| $("#id-card-number").val().length == 0

				|| (country != "MLM" && $("#id-doc-number").val().length == 0)) {
			return false;
		}

		if (country == "MLB") {
			if (!validateCpf($("#id-doc-number").val())) {
				$("#id-doc-number-status").html(
						"{l s='CPF invalid' mod='mercadopago'}");
				$("#id-doc-number").addClass("form-error");
				return false;
			}
		}

		return true;
	}

	function clearErrorStatus() {
		$("#id-card-number-status").html("");
		$("#id-security-code-status").html("");
		$("#id-card-expiration-month-status").html("");
		$("#id-card-expiration-year-status").html("");
		$("#id-card-holder-name-status").html("");
		$("#id-doc-number-status").html("");
		$("#id-installments-status").html("");

		$("#id-card-number-status-cust").html("");
		$("#id-security-code-status-cust").html("");
		$("#id-installments-status-cust").html("");

		$("#id-card-number").removeClass("form-error");
		$("#id-security-code").removeClass("form-error");
		$("#id-card-expiration-month").removeClass("boxshadow-error");
		$("#id-card-expiration-year").removeClass("boxshadow-error");
		$("#id-card-holder-name").removeClass("form-error");
		$("#id-doc-number").removeClass("form-error");
		$("#id-installments").removeClass("form-error");
		$("#id-docType").removeClass("form-error");

		$("#id-customerCards_msdd").removeClass("form-error");
		$("#id-security-code-cust").removeClass("form-error");
		$("#id-installments-cust").removeClass("form-error");


	}
	function validateCpf(cpf) {
		var soma;
		var resto;
		soma = 0;
		if (cpf == "00000000000")
			return false;

		for (i = 1; i <= 9; i++) {
			soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
			resto = (soma * 10) % 11;
		}

		if ((resto == 10) || (resto == 11))
			resto = 0;

		if (resto != parseInt(cpf.substring(9, 10)))
			return false;

		soma = 0;

		for (i = 1; i <= 10; i++) {
			soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
			resto = (soma * 10) % 11;
		}

		if ((resto == 10) || (resto == 11))
			resto = 0;

		if (resto != parseInt(cpf.substring(10, 11))) {
			return false;
		} else {
			return true;
		}
	}

	function setExpirationYear() {
		var html_options = "";
		var currentYear = new Date().getFullYear();

		for (i = 0; i <= 20; i++) {
			html_options += "<option value='"
					+ (currentYear + i).toString().substr(2, 2) + "'>"
					+ (currentYear + i) + "</option>";
		}
		;
		$("#id-card-expiration-year").html(html_options);
	}

	function setExpirationMonth() {
		var html_options = "";
		var currentMonth = new Date().getMonth();
		var months = [ "{l s='January' mod='mercadopago'}",
				"{l s='Febuary' mod='mercadopago'}",
				"{l s='March' mod='mercadopago'}",
				"{l s='April' mod='mercadopago'}",
				"{l s='May' mod='mercadopago'}",
				"{l s='June' mod='mercadopago'}",
				"{l s='July' mod='mercadopago'}",
				"{l s='August' mod='mercadopago'}",
				"{l s='September' mod='mercadopago'}",
				"{l s='October' mod='mercadopago'}",
				"{l s='November' mod='mercadopago'}",
				"{l s='December' mod='mercadopago'}" ];

		for (i = 0; i < 12; i++) {
			if (currentMonth == i)
				html_options += "<option value='" + (i + 1) + "' selected>"
						+ months[i] + "</option>";
			else
				html_options += "<option value='" + (i + 1) + "'>" + months[i]
						+ "</option>";
		}
		;

		$("#id-card-expiration-month").html(html_options);
	}

	setExpirationYear();
	setExpirationMonth();

	function createModal() {
		$("body").append($(".lightbox"));
	}

	createModal();

	// need to set 0 so modal checkout can work
	//$("#header").css("z-index", 0);
	if ("{$standard_active|escape:'javascript':'UTF-8'}" == "true"
			&& "{$window_type|escape:'javascript':'UTF-8'}" == "iframe") {
		$(".mp-form")
				.css(
						"width",
						parseInt("{$iframe_width|escape:'javascript':'UTF-8'}", 10)
								+ 20 + "px");
		$(".mp-form").css(
				"height",
				parseInt("{$iframe_height|escape:'javascript':'UTF-8'}", 10) + 20
						+ "px");
	}

	/*
	 *
	 * COUPON
	 *
	 */
	//hide all info
	$("#aplicarDescontoDisable").hide();

	//show loading
	$("#removerDesconto").hide();

	//Esconde todas as mensagens
	$("#error_alert").hide();

	removerDesconto("");
	removerDesconto("Ticket");

	$("#mercadopago_coupon").bind("change", function() {
		if (couponMensagemError(null, "")) {
			carregarDesconto("");
		}

	})

	//action apply
	$("#aplicarDescontoTicket").click(function() {
		if (couponMensagemError(null, "Ticket")) {
			carregarDesconto("Ticket");
		}
	});

	//action apply
	$("#aplicarDesconto").click(function() {
		if (couponMensagemError(null, "")) {
			carregarDesconto("");
		}
	});

	function carregarDesconto(cupomTicket) {

		var aplicarDescontoDisable = null;
		var error_alert = null;
		var aplicarDesconto = null;
		var mercadopago_coupon = null;

		var totalCompra = null;
		var removerDescontoButton = null;
		var couponApproved = null;
		var amount_discount = null;
		var total_amount = null;
		var total_amount_discount = null;
		var mercadopago_coupon_terms = null;
		var amount = null;

		var coupon = null;

		var mercadopago_coupon_ticket = $(".mercadopago_coupon_ticket");

		aplicarDescontoDisable = $("#aplicarDescontoDisable");
		error_alert = $("#error_alert");
		aplicarDesconto = $("#aplicarDesconto");
		mercadopago_coupon = $("#mercadopago_coupon");

		totalCompra = $("#totalCompra");
		removerDescontoButton = $("#removerDesconto");
		couponApproved = $(".couponApproved");
		amount_discount = $("#amount_discount");
		total_amount = $("#total_amount");
		total_amount_discount = $("#total_amount_discount");
		mercadopago_coupon_terms = $("#mercadopago_coupon_terms");
		amount = $("#amount");
		coupon = $("#mercadopago_coupon");

		aplicarDescontoDisable.show();
		error_alert.hide();
		aplicarDesconto.hide();
		aplicarDescontoDisable.show();

		var parametros = null;

		var discount_action_url = "{$discount_action_url|escape:'htmlall':'UTF-8'}";

		console.info(discount_action_url.indexOf("?"));

		if (discount_action_url.indexOf("?") >= 0) {
			parametros = "&coupon_id=";
		} else {
			parametros = "?coupon_id=";
		}

		$
				.ajax({
					type : "GET",
					url : "{$discount_action_url|escape:'htmlall':'UTF-8'}"
							+ parametros + coupon.val(),
					success : function(r) {

						if (r.status == 200) {
							mercadopago_coupon_ticket.val(coupon.val());

							totalCompra.css('text-decoration', 'line-through');

							aplicarDesconto.hide();
							removerDescontoButton.show();
							couponApproved.show();

							coupon.attr('readonly', true);

							var coupon_amount = (r.response.coupon_amount)
									.toFixed(2)
							var transaction_amount = (r.response.transaction_amount)
									.toFixed(2)
							var id_coupon = r.response.id;

							var url_term = "https://api.mercadolibre.com/campaigns/"
									+ id_coupon
									+ "/terms_and_conditions?format_type=html"

							amount_discount.html(coupon_amount);
							total_amount.html(transaction_amount);

							var total_amount_discount_v = (transaction_amount - coupon_amount)
									.toFixed(2);

							total_amount_discount.html(total_amount_discount_v);

							mercadopago_coupon_terms.attr("href", url_term);
							if (validateCard()) {
								loadInstallments();
							}

						} else {

							removerDesconto(cupomTicket);

							couponMensagemError(r, cupomTicket);

							if ($("#id-installments").val() != null
									&& $("#id-installments").val().length > 0) {
								loadInstallments();
							}
						}
					},
					error : function() {
						aplicarDesconto.show();
						removerDescontoButton.hide();

						if ($("#id-installments").val() != null
								&& $("#id-installments").val().length > 0) {
							loadInstallments();
						}

					},
					complete : function() {
						aplicarDescontoDisable.hide();

					}
				})
	}

	$("#removerDesconto").click(function() {
		removerDesconto("");
		loadInstallments();
	});

	$("#removerDescontoTicket").click(function() {
		removerDesconto("Ticket");
	});

	function removerDesconto(cupomTicket) {
		var coupon = null;
		var aplicarDesconto = null;
		var removerDesconto = null;
		var couponApproved = null;
		var totalCompra = null;
		var amount_discount = null;
		var aplicarDescontoDisable = null;
		var error_alert = null;

		mercadopago_coupon_ticket = $(".mercadopago_coupon_ticket");
		coupon = $("#mercadopago_coupon");
		aplicarDesconto = $("#aplicarDesconto");
		removerDesconto = $("#removerDesconto");
		couponApproved = $(".couponApproved");
		totalCompra = $("#totalCompra");
		amount_discount = $("#amount_discount");
		aplicarDescontoDisable = $("#aplicarDescontoDisable");
		error_alert = $("#error_alert");

		coupon.attr('readonly', false);
		coupon.val("");
		mercadopago_coupon_ticket.val("");
		aplicarDesconto.show();
		removerDesconto.hide();
		couponApproved.hide();
		totalCompra.css('text-decoration', '');
		amount_discount.text("");
		aplicarDescontoDisable.hide();
		error_alert.hide();
	}

	function couponMensagemError(r, cupomTicket) {
		var error_alert = null;
		var mercadopago_coupon = null;
		var amount_discount = null;
		error_alert = $("#error_alert");
		mercadopago_coupon = $("#mercadopago_coupon");
		amount_discount = $("#amount_discount");

		error_alert.html("");
		var retorno = true;
		if (r == null) {
			if (mercadopago_coupon.val().trim().length == 0) {
				error_alert
						.html("{l s='Coupon is required.' mod='mercadopago'}");
				retorno = false;
			}
		} else {
			retorno = false;
			if (r.response.error == "campaign-code-doesnt-match") {
				error_alert
						.html("{l s='Doesn\'t find a campaign with the given code.' mod='mercadopago'}");
			} else if (r.response.error == "transaction_amount_invalid") {
				error_alert
						.html("{l s='The coupon can not be applied to this amount.' mod='mercadopago'}");
			} else if (r.response.error == "run-out-of-uses") {
				error_alert
						.html("{l s='Run Out of uses per user.' mod='mercadopago'}");
			} else if (r.response.error == "run-out-of-uses") {
				error_alert
						.html("{l s='Please enter a valid coupon code.' mod='mercadopago'}");
			} else if (r.response.error == "amount-doesnt-match") {
				error_alert
						.html("{l s='Doesn\'t reach the mininimal amount or max amount.' mod='mercadopago'}");
			} else {
				error_alert
						.html("{l s='An error occurred while validating the coupon. Try again.' mod='mercadopago'}");
			}
		}

			error_alert.show();
			error_alert.fadeTo(10000, 2000).slideUp(2000, function() {
			error_alert.hide();
		});

		return retorno;

	}

	function validateCard() {
		var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
		if(opcaoPagamento == "Customer") {
			return true;
		}

		if ($("#id-card-number").val().length == 0) {
			return false;
		}
		return true;
	}

	$("#credit_option")
	.change(
			function(e) {
				$("#id-card-number").val("");
				loadCard();
			});

	function getPaymentMethods() {
		var json = {};

		if(country == "MLM" || country == "MPE") {
			var credit_option = document.querySelector('select[name="credit_option"]');
			console.info(credit_option[credit_option.options.selectedIndex]);
			console.info(credit_option);
			console.info("credit===="+credit_option[credit_option.options.selectedIndex].getAttribute('value'));
			json.payment_method_id = credit_option[credit_option.options.selectedIndex].getAttribute('value');

			var payment_type_id = credit_option[credit_option.options.selectedIndex].getAttribute('payment_type_id');
			console.info("payment==="+payment_type_id);
			json.payment_type_id = payment_type_id;

		} else {
			json.payment_method_id = $("#payment_method_id").val();
			json.payment_type_id = $("#payment_type_id").val();
		}

		console.info("json paymentMethod" + json);

		return json;
	}

	function loadInstallments() {

		var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
		if (opcaoPagamento == "Customer") {

			var customerCards = $("#id-customerCards");
			var id = customerCards.val();

			var card = document.querySelector('select[data-checkout="cardId"]');

			var payment_type_id = card[card.options.selectedIndex].getAttribute('payment_type_id');
			var firstSixDigits = card[card.options.selectedIndex].getAttribute('first_six_digits');

			var json = {}
			json.amount = returnAmount();
			json.bin = firstSixDigits;

			console.info("teste MLM 1");
			jsonPaymentMethod = getPaymentMethods();

			//json.payment_method_id = jsonPaymentMethod.payment_method_id;
			//json.payment_type_id = jsonPaymentMethod.payment_type_id;

		} else {
			//load Installment
			var bin = getBin();
			var json = {}
			json.amount = returnAmount();
			json.bin = bin;

			if (country === "MLM" || country === "MLA" || country === "MLU") {
				var issuerId = document.querySelector('#id-issuers-options').value;
				if (issuerId != undefined && issuerId != "-1") {
					json.issuer_id = issuerId;
				}
			}
		}
		try{
			console.info("loadInstallments 1");

			Mercadopago.getInstallments(json, setInstallmentInfo);
		}catch(e){
			console.info(e);
		}

	}

	$('#mercadopago_coupon').on('keyup keypress', function(e) {
		var keyCode = e.keyCode || e.which;
		if (keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	// $(".offline-payment").click(
	// 	function(e) {
	// 		console.info("entrou aqui 3");
	// 		var $form = $('.formTicket');
	// 		console.info("entrou aqui 4");
	// 		$form
	// 				.append($(
	// 						'<input type="hidden" id="mercadopago_coupon" name="mercadopago_coupon"/>')
	// 						.val($("#mercadopago_coupon").val()));
	// 		console.info("entrou aqui 5");
	// 		$(".createboleto2", this).click();
	// });

	function enviarBoleto(e, id) {
		console.info(id);
		var $form = $('.formTicket', e);
		"{if $coupon_active == 'true' }"
			$form
			.append($(
					'<input type="hidden" id="mercadopago_coupon" name="mercadopago_coupon"/>')
					.val($("#mercadopago_coupon").val()));
		"{/if}"
		console.info("entrou aqui no return");
		document.getElementById(id).click();
	}

	 $(".create-boleto").click(function(e) {
	 	console.info("entrou aqui 6");		
	 	$(".lightbox").show();
	 	e.stopImmediatePropagation();
	 });


	 $(".create-boleto-febraban").click(function(e) {
	 	if (validateFieldsFebraban()) {
	 		$(".lightbox").show();
	 		e.stopImmediatePropagation();
	 	}
	 });

	if (country == "MLB") {
		$(".status_febraban").hide();
		function submitBoletoFebraban() {
			var $form = $('.formTicket');
			$form
					.append($(
							'<input type="hidden" id="mercadopago_coupon" name="mercadopago_coupon"/>')
							.val($("#mercadopago_coupon").val()));
			return validateFieldsFebraban();
		}

		function validateFieldsFebraban() {
			$(".status_febraban").hide();
			var fiedsValid = true;
			if ($("#firstname").val().trim() == "") {
				$("#firstname-status").show();
				fiedsValid = false;
			}
			if ($("#cpfcnpj").val().trim() == "") {
				$("#cpf-status").show();
				fiedsValid = false;
			}
			if ($("#typeDocument").val() == "CPF")  {
				if ($("#lastname").val().trim() == "") {
					$("#lastname-status").show();
					fiedsValid = false;
				}

				if(!validaCPF($("#cpfcnpj").val())){
					$("#cpf-status").show();
					fiedsValid = false;
				}

			} else if ($("#typeDocument").val() == "CNPJ")  {
				if(!validaCNPJ($("#cpfcnpj").val())){
					$("#cpf-status").show();
					fiedsValid = false;
				}
			}

			if ($("#address").val().trim() == "") {
				$("#address-status").show();
				fiedsValid = false;
			}
			if ($("#number").val().trim() == "") {
				$("#number-status").show();
				fiedsValid = false;
			}
			if ($("#city").val().trim() == "") {
				$("#city-status").show();
				fiedsValid = false;
			}
			if ($("#state").val().trim() == "") {
				$("#state-status").show();
				fiedsValid = false;
			}
			if ($("#postcode").val().trim() == "") {
				$("#postcode-status").show();
				fiedsValid = false;
			}
			return fiedsValid;
		}

		$("#cpfcnpj").bind("change", function() {
			cpfCNPJ(this);
		});

		$("#cpfcnpj").bind("keypress", function() {
			cpfCNPJ(this);
		});

		function cpfCNPJ(obj) {
			$("#cpfcnpj").val(cpfCnpj(obj.value));
		    var tamanho = $("#cpfcnpj").val().length;
		    if(tamanho <= 14){
				$("#lastname").attr('required',true);
		        $("#lastname").show();
		        $("#labelLastname").show();
				$("#labelFirstname").text("Nome");
				$("#typeDocument").val("CPF");
		    } else if(tamanho > 14){
		        $("#lastname").attr('required',false);
		        $("#lastname").val("");
		        $("#lastname").hide();
		        $("#labelLastname").hide();
		        $("#labelFirstname").text("Razão Social");
		        $("#typeDocument").val("CNPJ");
		    }
		}

	}


	loadCustomerCard();

	function loadCustomerCard() {
		$("#myCreditCard").hide();
		$("#customerCardsDiv").hide();
		$("#opcaoPagamentoCreditCard").val("Cards");

		if("{$customerCards|escape:'javascript':'UTF-8'}".length > 0){

			var customerCards = JSON.parse("{$customerCards|escape:'javascript':'UTF-8'}");
			var html_options = "";
			if (customerCards.status != 404 && customerCards.status != 400) {
				html_options += "<option value='' selected>{l s='Choice' mod='mercadopago'}...</option>";
				var response = customerCards.response;
				var cards = response.cards;

				if (cards.length > 0) {
					html_options += "<optgroup label='{l s='Your card' mod='mercadopago'}'>";
					for (var i = 0; i < cards.length; i++) {
						html_options += "<option value='"+ cards[i].id +
								"'  payment_type_id='" + cards[i].payment_method.payment_type_id +
								"'  first_six_digits='" + cards[i].first_six_digits +
								"'  security_code_length='" + cards[i].security_code.length +
								"'  title='" + cards[i].payment_method.secure_thumbnail + "'    >"
								+ " ****** " + cards[i].last_four_digits + "</option>";
					}

					html_options += "<option value='outros'>{l s='Another credit card' mod='mercadopago'}...</option>";

					$("#myCreditCard").show();
					$("#customerCardsDiv").show();
					$("#cardDiv").hide();
					$("#opcaoPagamentoCreditCard").val("Customer");

					$("#id-customerCards").html(html_options);
				    $("#id-customerCards").val(response.default_card);
				} else {
					$("#myCreditCard").hide();
				}
			}

		}
	}

	$("#id-customerCards").bind("change", function() {
		if (this.value == "outros") {
			$("#customerCardsDiv").hide();
			$("#cardDiv").show();
			$("#opcaoPagamentoCreditCard").val("Cards");
			clearErrorStatus()
			taxesInstallments();
		} else if (this.value != "") {
			$("#customerCardsDiv").show();
			$("#cardDiv").hide();
			$("#opcaoPagamentoCreditCard").val("Customer");
			//loadInstallments();
			loadInstallmentsOneClick();
			clearErrorStatus();
			taxesInstallmentsCust();
		}
	});

	function loadInstallmentsOneClick(){
		var card = document.querySelector('select[data-checkout="cardId"]');
		var payment_type_id = card[card.options.selectedIndex].getAttribute('payment_type_id');
		var firstSixDigits = card[card.options.selectedIndex].getAttribute('first_six_digits');
		var json = {};
		json.amount = returnAmount();
		json.bin = firstSixDigits;
		Mercadopago.getPaymentMethod(json, setPaymentMethodInfOneClick);
	}

	function setPaymentMethodInfOneClick (status, result){
		$("#payment_method_id").val("");

		$("#payment_type_id").val("");

		var card = document.querySelector('select[data-checkout="cardId"]');

		//alert("setPaymentMethodInfOneClick");
		if (status != 404 && status != 400 && result != undefined) {
			//adiciona a imagem do meio de pagamento
			var payment_method = result[0];
			var amount = returnAmount();
			var bin = card[card.options.selectedIndex].getAttribute('first_six_digits');

			loadInstallments();
			$("#payment_method_id").val(payment_method.id);
			$("#payment_type_id").val(payment_method.payment_type_id);

		} else {
			$("#id-installments-cust").html('');
		}
	}


	$(document).ready(function(e) {
		$("#id-customerCards").msDropDown();
	});


	if (country === "MLM" || country === "MPE") {
		var html_options = "";
		var html_options_mp = "";
		var credit = "";
		"{foreach from=$payment_methods_credit item=value}"
			"{if $value.status == 'active' && ($value.payment_type_id == 'credit_card' || $value.payment_type_id == 'debit_card' || $value.payment_type_id == 'prepaid_card')}"
				credit = "";
				if ('{$value.payment_type_id}' == "credit_card") {
					credit = "{l s='Credit' mod='mercadopago'}";
				}
				if ('{$value.payment_type_id}' == "prepaid_card") {
					html_options_mp  += "<option value='{$value.id|escape:'htmlall':'UTF-8'}' payment_type_id='{$value.payment_type_id|escape:'htmlall':'UTF-8'}'>{$value.name|escape:'htmlall':'UTF-8'}</option>";
				}else{
					html_options += "<option value='{$value.id|escape:'htmlall':'UTF-8'}' payment_type_id='{$value.payment_type_id|escape:'htmlall':'UTF-8'}'>{$value.name|escape:'htmlall':'UTF-8'}&nbsp;" + credit + "</option>";
				}

		"{/if} {/foreach}"
	}
	html_options  += html_options_mp;
	console.info(html_options);
	$("#credit_option").html(html_options);


</script>

{if !$creditcard_disable && $public_key != ''}
	<script type="text/javascript">
		if (window.Mercadopago === undefined) {
			$.getScript("https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js")
				.done(function( script, textStatus ) {
					Mercadopago.setPublishableKey("{$public_key|escape:'javascript':'UTF-8'}");
					//loadInstallments();
					var opcaoPagamento = $("#opcaoPagamentoCreditCard").val();
					if (opcaoPagamento == "Customer") {
						//loadInstallments();
						loadInstallmentsOneClick();
					}
				});
		}
	</script>
{/if}


