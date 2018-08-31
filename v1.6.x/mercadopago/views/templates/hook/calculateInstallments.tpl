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

<style type="text/css">

.calculateMP {
    border: 1px solid #d6d4d4;
    border-radius: 0;
    padding: 0 0 5px 0;
    margin: 0 0 20px 0;
    padding-left: 10px;
    padding-right: 10px;

    {if $isCart == 'true'}
    width: 50%;
    {/if}

}

.calculateMP img{
	padding-top: 10px;
	padding-bottom: 20px;
	padding-left: 10px;
}

.calculateMP col-md-12{
	padding-bottom: 20px;
}

.list-installments {
    position: relative;
    display: block;
    padding: 10px 15px;
    margin-bottom: -1px;
    border: 1px solid #ddd;
}


</style>
<input id="amount" type="hidden" value="{$totalAmount|escape:'htmlall':'UTF-8'}" />

<div class="calculateMP">
	<img width="150px" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadopago.png">
	<div class="form-group">
    	<label for="txtCreditCard">"{l s='6 first digits of your card' mod='mercadopago'}"</label>
    	<input type="email" class="form-control" id="txtCreditCard" maxlength="6" size="8" aria-describedby="cardHelp" placeholder="*******">
    	<small id="cardHelp" class="form-text text-muted">"{l s='We use this number to calculate the installments.' mod= 'mercadopago'}"</small>
  	</div>
	<div class="row">
		<div class="col-md-12">
			<input type="button" name="btnCalculate" value="{l s='Calculate' mod='mercadopago'}" class="btn btn-primary" onclick="loadInstallments();" />
		</div>
	</div>
	<br/>
	{if $country == 'MLM' || $country == 'MLA' || $country == 'MPE'}
	<div style="display: none;" id="issue">
		<div class="form-group">
	    	<label for="issuersOptions">{l s='Select your bank' mod='mercadopago'}</label>
	    	<select id="issuersOptions" onchange="loadInstallmentsByPaymentMethod()">
	    	</select>

	    	<!-- <small id="issueHelp" class="form-text text-muted">{l s='It is necessary select your bank to calculate the installments.' mod='mercadopago'}</small> -->
	  	</div>
  	</div>
	{/if}
	<br/>
	<div class="row">
		<div class="col-md-12">
			<ul class="lblInstallments list-group">
			</ul>
		</div>
	</div>
</div>

<br/>

<script type="text/javascript">

	if (window.Mercadopago === undefined) {
		$.getScript("https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js")
			.done(function( script, textStatus ) {
				Mercadopago.setPublishableKey("{$public_key|escape:'javascript':'UTF-8'}");
			});
	}

	var country = "{$country|escape:'javascript':'UTF-8'}";


	function loadInstallments() {
		var txtCreditCard = $('#txtCreditCard').val();
		if (txtCreditCard.trim().length > 0 ) {
			var cList = $('ul.lblInstallments');
			cList.empty();
			$("#issue").hide();
			var json = {}
			json.bin = txtCreditCard;
			Mercadopago.getPaymentMethod(json, setPaymentMethodInfo);
		}
	}

	function setPaymentMethodInfo(status, result) {
		if (status != 404 && status != 400 && result != undefined) {
			//adiciona a imagem do meio de pagamento
			var payment_method = result[0];
			var amount = $('#amount').val();
			var bin = $('#txtCreditCard').val();
			if (country === "MLM" || country === "MLA" || country === "MPE") {
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
					$("#issue").show()
				} else {
					$("#issue").hide();
				}
			}

			$("#payment_method_id").val(payment_method.id);
			$("#payment_type_id").val(payment_method.payment_type_id);

			loadInstallmentsByPaymentMethod();

		}
	}

	function loadInstallmentsByPaymentMethod() {

		//load Installment
		var bin = $('#txtCreditCard').val();
		var json = {}
		json.amount = $('#amount').val();
		json.bin = bin;
		console.info(json);
		if (country === "MLM" || country === "MLA") {
			var issuerId = $('#issuersOptions').value;
			if (issuerId != undefined && issuerId != "-1") {
				json.issuer_id = issuerId;
			}
		}
		try{
			Mercadopago.getInstallments(json, setInstallmentInfo);
		}catch(e){
			console.info(e);
		}

	}

	//Mostre as parcelas disponíveis no div 'installmentsOption'
	function setInstallmentInfo(status, installments) {
		console.info(status);
		console.info(installments);
		var html_options = "";
		var cList = $('ul.lblInstallments');
		cList.empty();
		if (status != 404 && status != 400 && installments.length > 0) {
			cList.append('<li class="list-installments"><img src="'+installments[0].issuer.thumbnail+'"/></li>');
			var installments = installments[0].payer_costs;
			$.each(installments, function(key, value) {
				if (value.installment_rate == 0) {
					cList.append('<li class="list-installments alert alert-success"><span>'+value.recommended_message+'</span></li>');
				} else {
					cList.append('<li class="list-installments"><span>'+value.recommended_message+'</span></li>');
				}
			});
		} else {
			console.error("Installments Not Found.");
		}
	}

	function showCardIssuers(status, issuers) {
		var issuersOptions = $('#issuersOptions');

		if (issuers.length > 0) {
			issuersOptions.empty();
 			issuersOptions.append($("<option></option>")
            .attr("value", "-1")
            .text("{l s='Choose' mod='mercadopago'}..."));
            issuersOptions.prop('selectedIndex', "-1");
			for (var i = 0; i < issuers.length; i++) {
				if (issuers[i].name != "default") {
 					issuersOptions.append($("<option></option>")
            		.attr("value", issuers[i].id)
            		.text(issuers[i].name));
				} else {
 					issuersOptions.append($("<option></option>")
            		.attr("value", issuers[i].id)
            		.text("Otro"));
				}
			}
		}

	}

</script>