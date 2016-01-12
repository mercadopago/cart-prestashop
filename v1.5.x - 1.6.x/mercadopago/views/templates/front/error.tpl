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
	{capture name=path}{l s='Payment error' mod='mercadopago'}</a>{/capture}
	{if $version == 5}
		<div class="error">
	{elseif $version == 6}
		<div class="bootstrap">
			<div class="alert alert-danger">
	{/if}
	{l s='An error occurred during your payment process. Please review your data or choose another payment method.' mod='mercadopago'}</br>
	{if $valid_user eq false}
		{l s='Invalid users involved.' mod='mercadopago'}
	{elseif $status_detail eq 'cc_rejected_bad_filled_card_number'}
		{l s='Check the card number.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_bad_filled_date'}
		{l s='Check the expiration date.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_bad_filled_other'}
		{l s='Check the data.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_bad_filled_security_code'}
		{l s='Check the security code.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_blacklist'}
		{l s='We could not process your payment.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_call_for_authorize'}
		{l s='You must authorize to ' mod='mercadopago'}
		{$payment_method_id|escape:'htmlall'}
		{l s=' the payment to MercadoPago' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_card_disabled'}
		{l s='Call ' mod='mercadopago'}
		{$payment_method_id|escape:'htmlall'}
		{l s=' to activate your card. The phone is on the back of your card.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_card_error'}
		{l s='We could not process your payment.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_duplicated_payment'}
		{l s='You already made a payment by that value. If you need to repay, use another card or other payment method.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_high_risk'}
		{l s='Your payment was rejected. Choose another payment method, we recommend cash methods.' mod='mercadopago'} 

	{elseif $status_detail eq 'cc_rejected_insufficient_amount'}
		{l s='Your ' mod='mercadopago'}
		{$payment_method_id|escape:'htmlall'}
		{l s=' do not have sufficient funds.' mod='mercadopago'} 

	{elseif $status_detail eq 'cc_rejected_invalid_installments'}
		{$payment_method_id|escape:'htmlall'}
		{l s=' does not process payments in ' mod='mercadopago'}
		{$installments|escape:'htmlall'}
		{l s=' installments.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_max_attempts'}
		{l s='You have got to the limit of allowed attempts. Choose another card or another payment method.' mod='mercadopago'}

	{elseif $status_detail eq 'cc_rejected_other_reason'}
		{$payment_method_id|escape:'htmlall'}
		{l s=' did not process the payment.' mod='mercadopago'}
	{/if}
	{if $valid_user}
	</br>
		{l s='Card holder name: ' mod='mercadopago'}
		{$card_holder_name|escape:'htmlall'}</br>
		{l s='Credit card: ' mod='mercadopago'}
		**** **** **** {$four_digits|escape:'htmlall'}</br>
		{l s='Payment method: ' mod='mercadopago'}
		{$payment_method_id|escape:'htmlall'}</br>
		{if $expiration_date != null}
			{l s='Expiration date: ' mod='mercadopago'}
			{$expiration_date|escape:'htmlall'}</br>
		{/if}
		{l s='Amount: ' mod='mercadopago'}
		{$amount|escape:'htmlall'}</br>
		{if $installments != null}
			{l s='Installments: ' mod='mercadopago'}
			{$installments|escape:'htmlall'}</br>
		{/if}
		{l s='Payment id (MercadoPago): ' mod='mercadopago'}
		{$payment_id|escape:'htmlall'}</br>

		{if $message != null}
			<span>{l s='Technical Error: ' mod='mercadopago'}</span>
			{$message|escape:'htmlall'}</br>
		{/if}	

		{if $version == 6}
			</div>
		{/if}
	{/if}
	</div>
	<span class="footer-logo"></span>
	<div class="cart_navigation">
		{if $version == 5}
		<a id="go-back" class="button_large">
			{l s='Return to payment methods' mod='mercadopago'}
		</a>
		{elseif $version == 6}
		<a id="go-back" class="button-exclusive btn btn-default">
			<i class="icon-chevron-left"></i>
			{l s='Return to payment methods' mod='mercadopago'}
		</a>
		{/if}
	</div>
</div>

<script type="text/javascript">
	$("#go-back").click(function () {
		if ("{$one_step|escape:'htmlall'}" == 1)
			window.location = document.referrer;
		else
			window.history.go(-1);
	});

	$(document).ready(function(){ 
		if ("{$version|escape:'htmlall'}" == 5) {
			$(".error").css("width", "739px");
			$("#center_column").css("width", "758px");
		}
	}); 
</script>
