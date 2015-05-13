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
	{if $creditcard_active == 'true' && $public_key != ''}
		{if $version == 5}
			<div class="payment_module mp-form"> 
				<div class="row">
					<span class="payment-label">{l s='CREDIT CARD' mod='mercadopago'} </span>
					</br>
					<span class="poweredby">{l s='Powered by' mod='mercadopago'}</span>
					<img class="logo" src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png"/>
					{if !empty($creditcard_banner)}
					<img src="{$creditcard_banner|escape:'htmlall'}" class="mp-creditcard-banner"/>
					{/if}
				</div>
				<form action="{$custom_action_url|escape:'htmlall'}" method="post" id="form-pagar-mp">
			    	<div class="row">
				    	<div class="col">
					    	<label for="id-card-number">{l s='Card number: ' mod='mercadopago'}</label>
					    	<input id="id-card-number" data-checkout="cardNumber" type="text" name="cardNumber"/>
					    	<div id="id-card-number-status" class="status"></div>
				    	</div>
				    	<div class="col col-expiration">
					    	<label for="id-card-expiration-month">{l s='Month Exp: ' mod='mercadopago'}</label>
					    	<select id="id-card-expiration-month" class="small-select" data-checkout="cardExpirationMonth" type="text" name="cardExpirationMonth"></select>
					    </div>
					    <div class="col col-expiration">
					    	<label for="id-card-expiration-month">{l s='Year Exp: ' mod='mercadopago'}</label>
					    	<select id="id-card-expiration-year" class="small-select"  data-checkout="cardExpirationYear" type="text" name="cardExpirationYear"></select>
				    		<div id="id-card-expiration-year-status" class="status"></div>
				    	</div>
				    	<div class="col">
					    	<label for="id-card-holder-name">{l s='Card Holder Name: ' mod='mercadopago'}</label>
					    	<input id="id-card-holder-name" name="cardholderName" data-checkout="cardholderName" type="text"/>
				    		<div id="id-card-holder-name-status" class="status"></div>
				    	</div>
				    </div>
				    <div class="row">
				    	<div class="col col-security">
					    	<label for="id-security-code">{l s='Security Code: ' mod='mercadopago'}</label>
					    	<input id="id-security-code" data-checkout="securityCode" type="text" maxlength="4"//>
					    	<img src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/cvv.png" class="cvv"/>
					    	<div id="id-security-code-status" class="status"></div>
				    	</div>
				    	{if $country == 'MLB'}
						    <div class="col col-cpf">
						    	<label for="id-doc-number">{l s='CPF: ' mod='mercadopago'}</label>
						    	<input id="id-doc-number" name="docNumber" data-checkout="docNumber" type="text" maxlength="11"/>
						    	<div id="id-doc-number-status" class="status"></div>
						    	<input name="docType"  data-checkout="docType" type="hidden" value="CPF"/>
						    </div>
					    {else}
					    	<div class="col col-bank">
						    	<label for="id-issuers-options">{l s='Bank: ' mod='mercadopago'}</label>
								<select id="id-issuers-options" name="issuersOptions" type="text>"></select>
				    		</div>
					    {/if}
				    	<div class="col">
					    	<label for="id-installments">{l s='Installments: ' mod='mercadopago'}</label>
					    	<select id="id-installments" name="installments" type="text>"></select>
				    	</div>
				    </div>
				    <input id="amount" type="hidden" value="{$amount|escape:'htmlall'}"/>
				    <input id="payment_method_id" type="hidden" name="payment_method_id"/>
				    <div class="row">
				   		<div class="col-bottom">
				   			{if $country != 'MLB'}
					   			<div id="div-card-type">
							    	<label for="card-types">{l s='Card Type: ' mod='mercadopago'}</label>
							    	<input id="id-credit-card" name="card-types" type="radio" value="" checked>{l s='Credit' mod='mercadopago'}</input>
							    	<input id="id-debit-card" name="card-types" type="radio" value="deb">{l s='Debit' mod='mercadopago'}</input>
							    </div>
					    	{/if}
			    			<input type="submit" value="{l s='Confirm payment' mod='mercadopago'}" class="ch-btn ch-btn-big" />
						</div>
			    	</div>
				</form>
			</p>
			</div>
		{elseif $version == 6}
			<div class="row">
				<div class="col-xs-12 col-md-6">
						<div class="mp-form"> 
							<div class="row">
								<div class="col title">
									<span class="payment-label">{l s='CREDIT CARD' mod='mercadopago'} </span>
									</br>
									<span class="poweredby">{l s='Powered by' mod='mercadopago'}</span>
									<img class="logo" src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png">
								</div>
								{if !empty($creditcard_banner)}
								<div class="col title">
									<img src="{$creditcard_banner|escape:'htmlall'}" class="mp-creditcard-banner"/>
								</div>
								{/if}
							</div>
							<form action="{$custom_action_url|escape:'htmlall'}" method="post" id="form-pagar-mp">
							    <div class="row">
							    	<div class="col">
								    	<label for="id-card-number">{l s='Card number: ' mod='mercadopago'}</label>
								    	<input id="id-card-number" data-checkout="cardNumber" type="text"/>
								    	<div id="id-card-number-status" class="status"></div>
								    </div>
							    </div>
							   	 <div class="row">
								   	<div class="col">
								    	<label for="id-card-expiration-month">{l s='Expiration: ' mod='mercadopago'}</label>
								    	<select id="id-card-expiration-month" class="small-select" data-checkout="cardExpirationMonth" type="text"></select>
								    </div>
								    <div class="col">
								    	<select id="id-card-expiration-year" class="small-select"  data-checkout="cardExpirationYear" type="text"></select>
								    	<div id="id-card-expiration-year-status" class="status"></div>
								    </div>
								</div>
								<div class="row">
									<div class="col">
								    	<label for="id-card-holder-name">{l s='Card Holder Name: ' mod='mercadopago'}</label>
								    	<input id="id-card-holder-name" name="cardholderName" data-checkout="cardholderName" type="text"/>
								    	<div id="id-card-holder-name-status" class="status"></div>
							    	</div>
							    </div>
								<div class="row">
							    	<div class="col">
								    	<label for="id-security-code">{l s='Security Code: ' mod='mercadopago'}</label>
								    	<input id="id-security-code" data-checkout="securityCode" type="text" maxlength="4"//>
								    	<img src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/cvv.png" class="cvv"/>
								    	<div id="id-security-code-status" class="status"></div>
								    </div> 
						    	</div>
						    	{if $country == 'MLB'}
								 	<div class="row">
								    	<div class="col">
									    	<label for="id-doc-number">{l s='CPF: ' mod='mercadopago'}</label>
									    	<input id="id-doc-number" name="docNumber" data-checkout="docNumber" type="text" maxlength="11"/>
									    	<div id="id-doc-number-status" class="status"></div>
									    	<input name="docType"  data-checkout="docType" type="hidden" value="CPF"/>
									    </div>
								    </div>
							    {else}
						    	<div class="row">
								    <div class="col">
								    	<label for="id-issuers-options">{l s='Bank: ' mod='mercadopago'}</label>
								    	<select id="id-issuers-options" name="issuersOptions" type="text>"></select>
								    </div>
							 	</div>
							 	<div class="row">
								    <div class="col">
								    	<label for="card-types">{l s='Card Type: ' mod='mercadopago'}</label>
								    	<input id="id-credit-card" name="card-types" type="radio" value="" checked>{l s='Credit' mod='mercadopago'}</input>
								    	<input id="id-debit-card" name="card-types" type="radio" value="deb">{l s='Debit' mod='mercadopago'}</input>
								    </div>
							 	</div>
							 	{/if}
								<div class="row">
								    <div class="col">
								    	<label for="id-installments">{l s='Installments: ' mod='mercadopago'}</label>
								    	<select id="id-installments" name="installments" type="text>"></select>
								    </div>
							 	</div>
							    <input id="amount" type="hidden" value="{$amount|escape:'htmlall'}"/>
							    <input id="payment_method_id" type="hidden" name="payment_method_id"/>
							    <div class="row">
						    		<div class="col-bottom">
						    			{if $country != "MLB"}
								    		<input type="submit" value="{l s=' Confirm payment' mod='mercadopago'}" class="ch-btn ch-btn-big es-button" />
						    			{else}
						    				<input type="submit" value="{l s=' Confirm payment' mod='mercadopago'}" class="ch-btn ch-btn-big" />
						    			{/if}
							    	</div>
							    </div>
							</form>
						</div>
					</p>
				</div>
			</div>
		{/if}
	{/if}
	{if $boleto_active eq 'true'}
		{if $version == 5}
			<div class="payment_module mp-form"> 
				<div class="row">
					<div class="row boleto">
						<div class="col">
							<span class="payment-label">{l s='TICKET' mod='mercadopago'}</span></br>
							<span class="poweredby">{l s=' Powered by ' mod='mercadopago'}</span>
							<img class="logo" src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png">
						</div>
						<a href="javascript:void(0);" id="id-boleto">{l s='Pay through ticket via MercadoPago' mod='mercadopago'}
							<form action="{$custom_action_url|escape:'htmlall'}" method="post" id="form-boleto-mp">
						    	{if $country == 'MLB'}
							    	<input name="payment_method_id" type="hidden" value="bolbradesco"/>
							    {elseif $country == 'MLM'}
							    	<input name="payment_method_id" type="hidden" value="oxxo"/>
							    {/if}
						    	<input type="submit" id="id-create-boleto">
							</form>	
						</a>
					</div>
				</div>
			</div>
		{elseif $version == 6}
			<div class="row">
				<div class="col-xs-12 col-md-6">
						<a href="javascript:void(0);" id="id-boleto">
							<div class="mp-form hover"> 
								<div class="row boleto">
									<div class="col">
										<span class="payment-label">{l s='TICKET' mod='mercadopago'} </span></br>
										<span class="poweredby">{l s='Powered by' mod='mercadopago'}</span>
										<img class="logo" src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo.png">
									</div>
									<form action="{$custom_action_url|escape:'htmlall'}" method="post" id="form-boleto-mp">
							    	{if $country == 'MLB'}
								    	<input name="payment_method_id" type="hidden" value="bolbradesco"/>
								    {elseif $country == 'MLM'}
								    	<input name="payment_method_id" type="hidden" value="oxxo"/>
								    {/if}
								    <input type="submit" id="id-create-boleto">
									</form>	
								</div>
							</div>
						</a>
				</div>
			</div>
		{/if}
	{/if}
	{if $standard_active eq 'true' && $preferences_url != null}
		{if $version == 5}
			{if $window_type != 'iframe'}
			<div class="payment_module mp-form"> 
					<img src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo_120_31.png" id="id-standard-logo">
					<a class="standard" href="{$preferences_url|escape:'htmlall'}" mp-mode="{$window_type|escape:'htmlall'}" id="id-standard" name="MP-Checkout">{l s='Pay via MercadoPago and split into ' mod='mercadopago'}</br>{l s=' up to 24 times' mod='mercadopago'}</a>
					<img src="{$standard_banner|escape:'htmlall'}" class="mp-standard-banner"/>
			</div>
			{else}
				<div class="mp-form"> 
					<iframe src="{$preferences_url|escape:'htmlall'}" name="MP-Checkout" width="{$iframe_width|escape:'htmlall'}" height="{$iframe_height|escape:'htmlall'}" frameborder="0">
					</iframe>
				</div>
			{/if}
		{elseif $version == 6}
			<div class="row">
				<div class="col-xs-12 col-md-6">
						{if $window_type != 'iframe'}
							<a href="{$preferences_url|escape:'htmlall'}" id="id-standard" mp-mode="{$window_type|escape:'htmlall'}" name="MP-Checkout">
								<div class="mp-form hover"> 
									<div class="row">
										<div class="col">
										<img src="{$this_path_ssl|escape:'htmlall'}modules/mercadopago/views/img/payment_method_logo_120_31.png" id="id-standard-logo">
										<img src="{$standard_banner|escape:'htmlall'}" class="mp-standard-banner"/>
										<span class="payment-label standard">{l s='Pay via MercadoPago and split into up to 24 times' mod='mercadopago'}</span>
										</div>
									</div>
								</div>
							</a>
						{else}
							<div class="mp-form"> 
								<iframe src="{$preferences_url|escape:'htmlall'}" name="MP-Checkout" width="{$iframe_width|escape:'htmlall'}" height="{$iframe_height|escape:'htmlall'}" frameborder="0">
								</iframe>
							</div>
						{/if}
				</div>
			</div>
		{/if}
	{/if}
</div>
<script type="text/javascript">
	var country = "{$country|escape:'javascript'}";
	// first load force to clear all fields
	$("#id-card-number").val("");
	$("#id-security-code").val("");
	$("#id-card-holder-name").val("");
	$("#id-doc-number").val("");

 	$("input[data-checkout='cardNumber'], input[name='card-types']").bind("keyup focusout",function(){
      var bin = getBin()
      if (bin.length == 6){
      		var json = {}
            json.bin = bin;
      		Mercadopago.getPaymentMethod(json,setPaymentMethodInfo);
      } else if (bin.length < 6) {
			$("#id-card-number").css('background: none;');
      }
    });

    function getBin() {
    	var card = $("#id-card-number").val().replace(/ /g, '').replace(/-/g, '').replace(/\./g, '');
        var bin = card.substr(0,6);
        return bin;
    }


    // Estabeleça a informação do meio de pagamento obtido
    function setPaymentMethodInfo(status, result){
          
          //adiciona a imagem do meio de pagamento
          var payment_method = result[0];
          var amount = $("#amount").val();
          var bin = getBin();
          var json = {}
          json.amount = amount;
          json.bin = bin;
		  Mercadopago.getInstallments(json, setInstallmentInfo);
		  Mercadopago.getIssuers(payment_method.id, showIssuers);

		  $("#id-card-number").css("background", "url(" + payment_method.secure_thumbnail + ") 98% 50% no-repeat");
		  $("#payment_method_id").val($("input[name=card-types]:checked").val() + payment_method.id);
    };

    //Mostre as parcelas disponíveis no div 'installmentsOption'
    function setInstallmentInfo(status, installments){
        var html_options = "";
        var installments = installments[0].payer_costs;
        $.each(installments, function(key, value) {
            html_options += "<option value='"+ value.installments + "'>"+ value.recommended_message + "</option>";
        });
        $("#id-installments").html(html_options);
	};

	function showIssuers(status, issuers){
		var html_options = "";
		if (issuers.length > 0) {
			if(issuers.length == 1){
				html_options += "<option value='"+issuers[0].id+"'>"+issuers[0].name +" </option>";
				$("#id-issuers-options").hide();
			} else {
				for (i=0; issuers && i<issuers.length;i++){
					html_options += "<option value='"+issuers[i].id+"'>"+issuers[i].name +" </option>";
				}
			}
			$("#id-issuers-options").html(html_options);
		}
	};

	if (country === "MLM") {
		$("#id-issuers-options").change(function(){
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
	
	$("#form-pagar-mp").submit(function( event ) {
    	var $form = $(this);
    	var cpf = $("#id-doc-number").val();

    	if (country !== "MLB" || (cpf && validateCpf(cpf))) { 
    		Mercadopago.createToken($form, mpResponseHandler);
    	} else {
    		$("#id-doc-number-status").html("{l s='CPF invalid' mod='mercadopago'}");
    		$("#id-doc-number").addClass("form-error");
    	}
	    event.preventDefault();
	    return false;
	});

	var mpResponseHandler = function(status, response) {
		clearErrorStatus();

		var $form = $('#form-pagar-mp');
		if (response.error) {
			$.each(response.cause, function (p, e){
				switch (e.code) {
					case "E301": 
						$("#id-card-number-status").html("{l s='Card invalid' mod='mercadopago'}");
						$("#id-card-number").addClass("form-error");
					break;
					case "E302": 
						$("#id-security-code-status").html("{l s='CVV invalid' mod='mercadopago'}");
						$("#id-security-code").addClass("form-error");
					break;
					case "325":
					case "326": 
						$("#id-card-expiration-year-status").html("{l s='Date invalid' mod='mercadopago'}");
						$("#id-card-expiration-month").addClass("boxshadow-error");
						$("#id-card-expiration-year").addClass("boxshadow-error");
					break;
					case "316":
					case "221": 
						$("#id-card-holder-name-status").html("{l s='Name invalid' mod='mercadopago'}");
						$("#id-card-holder-name").addClass("form-error");
					break;
				}
			});
		} else {
			var card_token_id = response.id;
			$form.append($('<input type="hidden" id="card_token_id" name="card_token_id"/>').val(card_token_id));
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

		$("#id-card-number").removeClass("form-error");
		$("#id-security-code").removeClass("form-error");
		$("#id-card-expiration-month").removeClass("boxshadow-error");
		$("#id-card-expiration-year").removeClass("boxshadow-error");
		$("#id-card-holder-name").removeClass("form-error");
		$("#id-doc-number").removeClass("form-error");
	}

 	function validateCpf(cpf){
        var soma;
        var resto;
        soma = 0;
        if (cpf == "00000000000")
            return false;
            
        for (i=1; i<=9; i++){
            soma = soma + parseInt(cpf.substring(i-1, i)) * (11 - i);
            resto = (soma * 10) % 11;
        }
        
        if ((resto == 10) || (resto == 11))
            resto = 0;
            
        if (resto != parseInt(cpf.substring(9, 10)) )
            return false;
            
        soma = 0;
        
        for (i = 1; i <= 10; i++){
            soma = soma + parseInt(cpf.substring(i-1, i)) * (12 - i);
            resto = (soma * 10) % 11;
        }
        
        if ((resto == 10) || (resto == 11))
            resto = 0;
        
        if (resto != parseInt(cpf.substring(10, 11))){
            return false;
        }else{
            return true;    
        }
    }

    function setExpirationYear(){
        var html_options = "";
        var currentYear = new Date().getFullYear();

        for(i=0; i <= 20; i++){
            html_options += "<option value='" + (currentYear + i).toString().substr(2,2) + "'>" + (currentYear + i) + "</option>";
        };
        $("#id-card-expiration-year").html(html_options);
  	}

  	function setExpirationMonth() {
  		var html_options = "";
        var currentMonth = new Date().getMonth();
        var months = ["{l s='January' mod='mercadopago'}", "{l s='Febuary' mod='mercadopago'}", "{l s='March' mod='mercadopago'}", "{l s='April' mod='mercadopago'}", "{l s='May' mod='mercadopago'}", "{l s='June' mod='mercadopago'}", "{l s='July' mod='mercadopago'}", "{l s='August' mod='mercadopago'}", "{l s='September' mod='mercadopago'}", "{l s='October' mod='mercadopago'}", "{l s='November' mod='mercadopago'}", "{l s='December' mod='mercadopago'}"];

        for (i = 0; i < 12; i++) {
        	if(currentMonth == i)
            	html_options += "<option value='" + (i + 1) + "' selected>" + months[i] + "</option>";
            else
            	html_options += "<option value='" + (i + 1) + "'>" + months[i] + "</option>";
        };

        $("#id-card-expiration-month").html(html_options);
  	}

  	setExpirationYear();
  	setExpirationMonth();

  	$("#id-boleto").click(function (e) {
  		$('#id-create-boleto', this).click();
  	});

  	$("#id-create-boleto").click(function (e) {
  		$(".lightbox").show();
  		e.stopImmediatePropagation();
  	});
  
  	function createModal() {
  		$("body").append($(".lightbox"));
  	}
 	
 	createModal();

 	// need to set 0 so modal checkout can work
	$("#header").css("z-index", 0);
	if("{$standard_active|escape:'javascript'}" == "true" && "{$window_type|escape:'javascript'}" == "iframe"){
		$(".mp-form").css("width", parseInt("{$iframe_width|escape:'javascript'}", 10) + 20 + "px");
		$(".mp-form").css("height", parseInt("{$iframe_height|escape:'javascript'}", 10) + 20 + "px");
	}
</script>