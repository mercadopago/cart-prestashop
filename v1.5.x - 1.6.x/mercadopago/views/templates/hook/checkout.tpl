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
for more information. * * @author ricardobrito * @copyright Copyright
(c) MercadoPago [http://www.mercadopago.com] * @license
http://opensource.org/licenses/osl-3.0.php Open Software License (OSL
3.0) * International Registered Trademark & Property of MercadoPago *}
<div class="lightbox" id="text">
	<div class="box">
		<div class="content">
			<div class="processing">
				<span>{l s='Processing...' mod='mercadopago'}</span>
			</div>
		</div>

	</div>
</div>
<div class="mp-module">
	{if $creditcard_active == 'true' && $public_key != ''} {if $version ==
	5}
	<div class="payment_module mp-form-custom">
		<div class="row">
			<span class="payment-label">{l s='CREDIT CARD'
				mod='mercadopago'} </span> </br> <span class="poweredby">{l s='Powered
				by' mod='mercadopago'}</span> <img class="logo"
				src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png" />
			{if !empty($creditcard_banner)} <img
				src="{$creditcard_banner|escape:'htmlall'}"
				class="mp-creditcard-banner" /> {/if}
		</div>
		<form action="{$custom_action_url|escape:'htmlall'}" method="post"
			id="form-pagar-mp">
			<div class="row">
				<div class="col">
					<label for="id-card-number">{l s='Card number: '
						mod='mercadopago'}</label> <input id="id-card-number"
						data-checkout="cardNumber" type="text" />
					<div id="id-card-number-status" class="status"></div>
				</div>
				<div class="col col-expiration">
					<label for="id-card-expiration-month">{l s='Month Exp: '
						mod='mercadopago'}</label> <select id="id-card-expiration-month"
						class="small-select" data-checkout="cardExpirationMonth"
						type="text"></select>
				</div>
				<div class="col col-expiration">
					<label for="id-card-expiration-month">{l s='Year Exp: '
						mod='mercadopago'}</label> <select id="id-card-expiration-year"
						class="small-select" data-checkout="cardExpirationYear"
						type="text"></select>
					<div id="id-card-expiration-year-status" class="status"></div>
				</div>
				<div class="col">
					<label for="id-card-holder-name">{l s='Card Holder Name: '
						mod='mercadopago'}</label> <input id="id-card-holder-name"
						data-checkout="cardholderName" type="text" name="cardholderName" />
					<div id="id-card-holder-name-status" class="status"></div>
				</div>
			</div>
			<div class="row">
				<div class="col col-security">
					<label for="id-security-code">{l s='Security Code: '
						mod='mercadopago'}</label> <input id="id-security-code"
						data-checkout="securityCode" type="text" maxlength="4" //> <img
						src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/cvv.png"
						class="cvv" />
					<div id="id-security-code-status" class="status"></div>
				</div>
				{if $country == 'MLB'}
				<div class="col col-cpf">
					<label for="id-doc-number">{l s='CPF: ' mod='mercadopago'}</label>
					<input id="id-doc-number" name="docNumber"
						data-checkout="docNumber" type="text" maxlength="11" />
					<div id="id-doc-number-status" class="status"></div>
					<input name="docType" data-checkout="docType" type="hidden"
						value="CPF" />
				</div>
				{elseif $country == 'MLM' || $country == 'MLA'}
				<div class="col col-bank">
					<label class="issuers-options" for="id-issuers-options">{l
						s='Bank: ' mod='mercadopago'}</label> <select class="issuers-options"
						id="id-issuers-options" name="issuersOptions" type="text>"></select>
				</div>
				{/if}
				<div class="col">
					<label for="id-installments">{l s='Installments: '
						mod='mercadopago'}</label> <select id="id-installments"
						name="installments" type="text"></select>
					<div id="id-installments-status" class="status"></div>
				</div>
			</div>
			<input id="amount" type="hidden" value="{$amount|escape:'htmlall'}" />
			<input id="payment_method_id" type="hidden" name="payment_method_id" />
			<div class="row">
				<div class="col-bottom">
					{if $country == 'MLM'}
					<div id="div-card-type">
						<label for="card-types">{l s='Card Type: '
							mod='mercadopago'}</label> <input id="id-credit-card" name="card-types"
							type="radio" value="" checked>{l s='Credit'
						mod='mercadopago'}</input> <input id="id-debit-card" name="card-types"
							type="radio" value="deb">{l s='Debit' mod='mercadopago'}</input>
					</div>
					{elseif $country == 'MLA'}
					<div class="row">
						<div class="col">
							<label for="docType">{l s='Document type: '
								mod='mercadopago'}</label> <select name="docType"
								data-checkout="docType"></select>
						</div>
						<div class="col">
							<input id="id-doc-number" name="docNumber"
								data-checkout="docNumber" type="text" />
							<div id="id-doc-number-status" class="status"></div>
						</div>
					</div>
					{/if} <input type="submit"
						value="{l s='Confirm payment' mod='mercadopago'}"
						class="ch-btn ch-btn-big" />
				</div>
			</div>
		</form>
		</p>
	</div>

	{elseif $version == 6}

	<div class="row">
		<div class="col-xs-12 col-md-6">
			<div class="mp-form-custom">
				<div class="row">
					<div class="col title">
						<span class="payment-label">{l s='CREDIT CARD'
							mod='mercadopago'} </span> </br> <span class="poweredby">{l s='Powered
							by' mod='mercadopago'}</span> <img class="logo"
							src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png">
					</div>
					{if !empty($creditcard_banner)}
					<div class="col title">
						<img src="{$creditcard_banner|escape:'htmlall'}"
							class="mp-creditcard-banner" />
					</div>
					{/if}
				</div>
				<div class="row">
					<label class="ch-form-hint">* {l s='Required fields'
						mod='mercadopago'}</label>
				</div>
				<form action="{$custom_action_url|escape:'htmlall'}" method="post"
					id="form-pagar-mp">
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
							<label for="id-card-expiration-month">{l s='Expiration: '
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
							<label for="id-card-holder-name">{l s='Card Holder Name:
								' mod='mercadopago'}<em>*</em>
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
								src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/cvv.png"
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
								value="CPF" />
						</div>
					</div>
					{elseif $country == 'MLM' || $country == 'MLA'}
					<div class="row">
						<div class="col">
							<label class="issuers-options" for="id-issuers-options">{l
								s='Bank: ' mod='mercadopago'}<em>*</em>
							</label> <select class="issuers-options" id="id-issuers-options"
								name="issuersOptions" type="text"></select>
						</div>
					</div>
					{/if}
					
					{if $country == 'MLM'}
					<div class="row">
						<div class="col">
							<label for="card-types">{l s='Card Type: '
								mod='mercadopago'}</label> <input id="id-credit-card" name="card-types"
								type="radio" value="" checked>{l s='Credit'
							mod='mercadopago'}</input> <input id="id-debit-card" name="card-types"
								type="radio" value="deb">{l s='Debit' mod='mercadopago'}</input>
						</div>
					</div>
					{elseif $country == 'MLA' || $country == 'MCO'}
					<div class="row">
						<div class="col">
							<label for="docType">{l s='Document type: '
								mod='mercadopago'}<em>*</em>
							</label> <select name="docType" type="text" class="document-type"
								data-checkout="docType"></select>
						</div>
						<div class="col">
							<input id="id-doc-number" name="docNumber"
								data-checkout="docNumber" type="text" />
							<div id="id-doc-number-status" class="status"></div>
						</div>
					</div>
					<div class="row"></div>
					{/if}
						
					{if $country == 'MLC'}
					<div class="row">
						<div class="col">
							<label for="id-doc-number">RUT: <em>*</em></label> <input
								type="hidden" name="docType" id="docType" value="RUT" data-checkout="docType">
							<input type="text" id="id-doc-number" data-checkout="docNumber" maxlength="10" size="14" placeholder="11111111-1">
							<div id="id-doc-number-status" class="status"></div>
						</div>
					</div>
   					{/if}	

					<div class="row">
						<div class="col">
							<label for="id-installments">{l s='Installments: '
								mod='mercadopago'}<em>*</em>
							</label> <select id="id-installments" name="installments" type="text"></select>
						</div>
					</div>
					<input id="amount" type="hidden" value="{$amount|escape:'htmlall'}" />
					<input id="payment_method_id" type="hidden"
						name="payment_method_id" />
					<div class="row">
						<div class="col-bottom">
							{if $country != "MLB"} <input type="submit"
								value="{l s=' Confirm payment' mod='mercadopago'}"
								class="ch-btn ch-btn-big es-button" /> {else} <input
								type="submit" value="{l s=' Confirm payment' mod='mercadopago'}"
								class="ch-btn ch-btn-big" /> {/if}
						</div>
					</div>
				</form>
			</div>
			</p>
		</div>
	</div>
	{/if} {/if} {if $country == 'MLB' || $country == 'MLM' || $country ==
	'MLA' || $country == 'MLC' || $country == 'MCO'} {foreach from=$offline_payment_settings
	key=offline_payment item=value} {if $value.active == "true"} {if
	$version == 5}
	<div class="payment_module mp-form">
		<div class="row">
			<div class="row">
				<div class="col offline">
					<span class="payment-label">{$value.name|upper}</span><br /> <span
						class="poweredby">{l s='Powered by' mod='mercadopago'}</span> <img
						class="logo"
						src="{$this_path_ssl|escape:'htmlall'}modules/mercadopagobr/views/img/payment_method_logo.png">
				</div>
				<a href="javascript:void(0);"
					id="id-{$offline_payment|escape:'htmlall'}" class="offline-payment">
					{l s='Pay through ' mod='mercadopago'}{$value.name|ucfirst}{l s='
					via MercadoPago' mod='mercadopago'}
					<form action="{$custom_action_url|escape:'htmlall'}" method="post">
						<input name="payment_method_id" type="hidden"
							value="{$offline_payment|escape:'htmlall'}" /> <input
							type="submit" class="create-boleto"
							id="id-create-{$offline_payment|escape:'htmlall'}">
					</form>
				</a>
			</div>
		</div>
	</div>
	{elseif $version == 6}
	<div class="row">
		<div class="col-xs-12 col-md-6">
			<a href="javascript:void(0);"
				id="id-{$offline_payment|escape:'htmlall'}" class="offline-payment">
				<div class="mp-form-boleto">
					<div class="row boleto">
						<div class="col">
							<span class="payment-label">{$value.name|upper} </span></br> <span
								class="poweredby">{l s='Powered by' mod='mercadopago'}</span> <img
								class="logo"
								src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png">
						</div>
						<form action="{$custom_action_url|escape:'htmlall'}" method="post">
							<input name="payment_method_id" type="hidden"
								value="{$offline_payment|escape:'htmlall'}" /> <input
								type="submit" class="create-boleto"
								id="id-create-{$offline_payment|escape:'htmlall'}">
						</form>
					</div>
				</div>
			</a>
		</div>
	</div>
	{/if} {/if} {/foreach} {/if} {if $standard_active eq 'true' &&
	$preferences_url != null} {if $version == 5} {if $window_type !=
	'iframe'}
	<div class="payment_module mp-form">
		<img
			src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo_120_31.png"
			id="id-standard-logo"> <a class="standard"
			href="{$preferences_url|escape:'htmlall'}"
			mp-mode="{$window_type|escape:'htmlall'}" id="id-standard"
			name="MP-Checkout">{l s='Pay via MercadoPago and split into '
			mod='mercadopago'}</br>{l s=' up to 24 times' mod='mercadopago'}
		</a> <img src="{$standard_banner|escape:'htmlall'}"
			class="mp-standard-banner" />
	</div>
	{else}
	<div class="mp-form">
		<iframe src="{$preferences_url|escape:'htmlall'}" name="MP-Checkout"
			width="{$iframe_width|escape:'htmlall'}"
			height="{$iframe_height|escape:'htmlall'}" frameborder="0">
		</iframe>
	</div>
	{/if} {elseif $version == 6}
	<div class="row">
		<div class="col-xs-12 col-md-6">
			{if $window_type != 'iframe'} <a
				href="{$preferences_url|escape:'htmlall'}" id="id-standard"
				mp-mode="{$window_type|escape:'htmlall'}" name="MP-Checkout">
				<div class="mp-form hover">
					<div class="row">
						<div class="col">
							<img
								src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo_120_31.png"
								id="id-standard-logo"> <img
								src="{$standard_banner|escape:'htmlall'}"
								class="mp-standard-banner" /> <span
								class="payment-label standard">{l s='Pay via MercadoPago
								and split into up to 24 times' mod='mercadopago'}</span>
						</div>
					</div>
				</div>
			</a> {else}
			<div class="mp-form">
				<iframe src="{$preferences_url|escape:'htmlall'}" name="MP-Checkout"
					width="{$iframe_width|escape:'htmlall'}"
					height="{$iframe_height|escape:'htmlall'}" frameborder="0">
				</iframe>
			</div>
			{/if}
		</div>
	</div>
	{/if} {/if}
</div>
<script type="text/javascript">
	var country = "{$country|escape:'javascript'}";
	// first load force to clear all fields
	$("#id-card-number").val("");
	$("#id-security-code").val("");
	$("#id-card-holder-name").val("");
	$("#id-doc-number").val("");

	$("input[data-checkout='cardNumber'], input[name='card-types']").bind(
			"keyup focusout", function() {
				var bin = getBin()
				if (bin.length == 6) {
					var json = {}
					json.bin = bin;
					Mercadopago.getPaymentMethod(json, setPaymentMethodInfo);
					Mercadopago.getIdentificationTypes();
				} else if (bin.length < 6) {
					$("#id-card-number").css('background-image', '');
					$("#id-installments").html('');
				}
			});

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
			var amount = $("#amount").val();
			var bin = getBin();
			var json = {}
			json.amount = amount;
			json.bin = bin;
			Mercadopago.getInstallments(json, setInstallmentInfo);

			if (country === "MLM" || country === "MLA") {
				// check if the issuer is necessary to pay
				var issuerMandatory = false, additionalInfo = result[0].additional_info_needed;

				for (var i = 0; i < additionalInfo.length; i++) {
					if (additionalInfo[i] == "issuer_id") {
						issuerMandatory = true;
					}
				}

				if (issuerMandatory) {
					Mercadopago.getIssuers(result[0].id, showCardIssuers);
					$("#id-issuers-options").bind("change", function() {
						setInstallmentsByIssuerId(status, result)
					});
				} else {
					document.querySelector("#id-issuers-options").options.length = 0;
					document.querySelector("#id-issuers-options").style.display = 'none';
					document.querySelector(".issuers-options").style.display = 'none';
				}
			}
			console.info(payment_method);
			$("#id-card-number").css(
					"background",
					"url(" + payment_method.secure_thumbnail
							+ ") 98% 50% no-repeat");
			$("#payment_method_id").val(
					$("input[name=card-types]:checked").val() ? $(
							"input[name=card-types]:checked").val()
							+ payment_method.id : payment_method.id);
		} else {
			$("#id-card-number").css('background-image', '');
			$("#id-installments").html('');
		}
	};

	function setInstallmentsByIssuerId(status, response) {
		var issuerId = document.querySelector('#id-issuers-options').value, amount = document
				.querySelector('#amount').value;

		if (issuerId === '-1') {
			return;
		}

		Mercadopago.getInstallments({
			"bin" : getBin(),
			"amount" : amount,
			"issuer_id" : issuerId
		}, setInstallmentInfo);
	};

	//Mostre as parcelas disponíveis no div 'installmentsOption'
	function setInstallmentInfo(status, installments) {
		console.info(status);
		console.info(installments);
		var html_options = "";
		if (status != 404 && status != 400) {
			html_options += "<option value='' selected>{l s='Choice' mod='mercadopago'}...</option>";
			var installments = installments[0].payer_costs;
			$.each(installments, function(key, value) {
				html_options += "<option value='"+ value.installments + "'>"
						+ value.recommended_message + "</option>";
			});
		} else {
			console.info(installments);
		}
		$("#id-installments").html(html_options);
	};

	function showCardIssuers(status, issuers) {
		var issuersSelector = document.querySelector("#id-issuers-options"), fragment = document
				.createDocumentFragment();

		issuersSelector.options.length = 0;
		var option = new Option("Choose...", '-1');
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
		document.querySelector(".issuers-options").removeAttribute('style');
		document.querySelector("#id-issuers-options").removeAttribute('style');
	};

	if (country === "MLM" || country === "MLA") {
		$("#id-issuers-options").change(function() {
			var issuerId = $('#id-issuers-options').val();
			var amount = $("#amount").val()
			var bin = getBin();
			var json = {}
			json.issuer_id = issuerId;
			json.amount = amount;
			json.bin = bin;
			Mercadopago.getInstallments(json, setInstallmentInfo);
		});
	}

	$("#form-pagar-mp")
			.submit(
					function(event) {
						clearErrorStatus();

						var $form = $(this);
						var cpf = $("#id-doc-number").val();

						if (country == "MLB") {
							if ($("#id-card-number").val().length == 0) {
								$("#id-card-number-status")
										.html(
												"{l s='Card invalid' mod='mercadopago'}");
								$("#id-card-number").addClass("form-error");
							}

							if ($("#id-card-holder-name").val().length == 0) {
								$("#id-card-holder-name-status")
										.html(
												"{l s='Name invalid' mod='mercadopago'}");
								$("#id-card-holder-name")
										.addClass("form-error");
							}

							if ($("#id-security-code").val().length == 0) {
								$("#id-security-code-status")
										.html(
												"{l s='CVV invalid' mod='mercadopago'}");
								$("#id-security-code").addClass("form-error");
							}

							if ($("#id-doc-number").val().length == 0) {
								$("#id-doc-number-status")
										.html(
												"{l s='CPF invalid' mod='mercadopago'}");
								$("#id-doc-number").addClass("form-error");
							}

							if ($("#id-installments").val() == null
									|| $("#id-installments").val().length == 0) {
								$("#id-installments-status")
										.html(
												"{l s='Installments invalid' mod='mercadopago'}");
								$("#id-installments").addClass("form-error");
							}

							if ($("#id-installments").val() == null
									|| $("#id-installments").val().length == 0
									|| $("#id-security-code").val().length == 0
									|| $("#id-card-holder-name").val().length == 0
									|| $("#id-card-number").val().length == 0
									|| $("#id-doc-number").val().length == 0) {

								event.preventDefault();
								return false;
							} else {
								if (validateCpf(cpf)) {
									Mercadopago.createToken($form,
											mpResponseHandler);
								} else {
									$("#id-doc-number-status")
											.html(
													"{l s='CPF invalid' mod='mercadopago'}");
									$("#id-doc-number").addClass("form-error");
								}
							}
						} else {
							Mercadopago.createToken($form, mpResponseHandler);
						}

						event.preventDefault();
						return false;
					});

	var mpResponseHandler = function(status, response) {
		clearErrorStatus();

		console.info(response.error);

		var $form = $('#form-pagar-mp');

		if (response.error) {
			$.each(response.cause, function(p, e) {
				switch (e.code) {
				case "E301":
					$("#id-card-number-status").html(
							"{l s='Card invalid' mod='mercadopago'}");
					$("#id-card-number").addClass("form-error");
					break;
				case "E302":
					$("#id-security-code-status").html(
							"{l s='CVV invalid' mod='mercadopago'}");
					$("#id-security-code").addClass("form-error");
					break;
				case "325":
				case "326":
					$("#id-card-expiration-year-status").html(
							"{l s='Date invalid' mod='mercadopago'}");
					$("#id-card-expiration-month").addClass("boxshadow-error");
					$("#id-card-expiration-year").addClass("boxshadow-error");
					break;
				case "316":
				case "221":
					$("#id-card-holder-name-status").html(
							"{l s='Name invalid' mod='mercadopago'}");
					$("#id-card-holder-name").addClass("form-error");
					break;
				case "324":
					$("#id-doc-number-status").html(
							"{l s='Document invalid' mod='mercadopago'}");
					$("#id-doc-number").addClass("form-error");
					break;
					
				}
			});
		} else {
			var card_token_id = response.id;
			$form
					.append($(
							'<input type="hidden" id="card_token_id" name="card_token_id"/>')
							.val(card_token_id));

			var cardNumber = $("#id-card-number").val();

			var lastFourDigits = cardNumber.substring(cardNumber.length - 4);
			$form
					.append($('<input name="lastFourDigits" type="hidden" value="' + lastFourDigits + '"/>'));

			$form.get(0).submit();

			$(".lightbox").show();
		}
	}

	function clearErrorStatus() {
		$("#id-card-number-status").html("");
		$("#id-security-code-status").html("");
		$("#id-card-expiration-month-status").html("");
		$("#id-card-expiration-year-status").html("");
		$("#id-card-holder-name-status").html("");
		$("#id-doc-number-status").html("");
		$("#id-installments-status").html("");

		$("#id-card-number").removeClass("form-error");
		$("#id-security-code").removeClass("form-error");
		$("#id-card-expiration-month").removeClass("boxshadow-error");
		$("#id-card-expiration-year").removeClass("boxshadow-error");
		$("#id-card-holder-name").removeClass("form-error");
		$("#id-doc-number").removeClass("form-error");
		$("#id-installments").removeClass("form-error");
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

	$(".offline-payment").click(function(e) {
		$(".create-boleto", this).click();
	});

	$(".create-boleto").click(function(e) {
		$(".lightbox").show();
		e.stopImmediatePropagation();
	});

	function createModal() {
		$("body").append($(".lightbox"));
	}

	createModal();

	// need to set 0 so modal checkout can work
	$("#header").css("z-index", 0);
	if ("{$standard_active|escape:'javascript'}" == "true"
			&& "{$window_type|escape:'javascript'}" == "iframe") {
		$(".mp-form")
				.css(
						"width",
						parseInt("{$iframe_width|escape:'javascript'}", 10)
								+ 20 + "px");
		$(".mp-form").css(
				"height",
				parseInt("{$iframe_height|escape:'javascript'}", 10) + 20
						+ "px");
	}
</script>
