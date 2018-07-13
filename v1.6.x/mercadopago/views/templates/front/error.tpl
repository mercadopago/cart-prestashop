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
	{capture name=path}{l s='Payment error' mod='mercadopago'}</a>{/capture}

	{if $standard == 'true'}
			{if $typeReturn == 'failure'}

				<div>
					<h4>{l s='You not finished your payment!' mod='mercadopago'}</h4>
				</div>
				<br/>

				{if $init_point}
					<div class="cart_navigation __web-inspector-hide-shortcut__">
						<a id="go-back" href="{$init_point|escape:'htmlall':'UTF-8'}" class="button-exclusive btn btn-default">
							{l s='Click here to try pay again.' mod='mercadopago'}
						</a>
					</div>
				{/if}
			{/if}
			{if $show_QRCode == 'true'}
				<div>
					<h4>{l s='Another options is you to use this QR Code to finish the payment in your Mercado Pago App.' mod='mercadopago'}</h4>
				</div>
				<div id="output"></div>
			{/if}

	{else}
	<div class="bootstrap">
		<div>
			<h4>{l s='An error occurred during your payment process. Please review your data or choose another payment method.' mod='mercadopago'}</h4>
			</br>
			<h4>{if $status_detail eq 'cc_rejected_bad_filled_card_number'}
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
				{$payment_method_id|escape:'htmlall':'UTF-8'}
				{l s=' the payment to MercadoPago' mod='mercadopago'}

			{elseif $status_detail eq 'cc_rejected_card_disabled'}
				{l s='Call ' mod='mercadopago'}
				{$payment_method_id|escape:'htmlall':'UTF-8'}
				{l s=' to activate your card. The phone is on the back of your card.' mod='mercadopago'}

			{elseif $status_detail eq 'cc_rejected_card_error'}
				{l s='We could not process your payment.' mod='mercadopago'}

			{elseif $status_detail eq 'cc_rejected_duplicated_payment'}
				{l s='You already made a payment by that value. If you need to repay, use another card or other payment method.' mod='mercadopago'}

			{elseif $status_detail eq 'cc_rejected_high_risk'}
				{l s='Your payment was rejected. Choose another payment method, we recommend cash methods.' mod='mercadopago'} 

			{elseif $status_detail eq 'cc_rejected_insufficient_amount'}
				{l s='Your ' mod='mercadopago'}
				{$payment_method_id|escape:'htmlall':'UTF-8'}
				{l s=' do not have sufficient funds.' mod='mercadopago'} 

			{elseif $status_detail eq 'cc_rejected_invalid_installments'}
				{$payment_method_id|escape:'htmlall':'UTF-8'}
				{l s=' does not process payments in ' mod='mercadopago'}
				{$installments|escape:'htmlall':'UTF-8'}
				{l s=' installments.' mod='mercadopago'}

			{elseif $status_detail eq 'cc_rejected_max_attempts'}
				{l s='You have got to the limit of allowed attempts. Choose another card or another payment method.' mod='mercadopago'}

			{elseif $status_detail eq 'cc_rejected_other_reason'}
				{$payment_method_id|escape:'htmlall':'UTF-8'}
				{l s=' did not process the payment.' mod='mercadopago'}
			{/if}</h4>
		</div>
	</div>
	{/if}
	<span class="footer-logo"></span>
	{if $standard == 'false'}
		<div class="cart_navigation">
			<a id="go-back" class="button-exclusive btn btn-default">
				<i class="icon-chevron-left"></i>
				{l s='Return to payment methods' mod='mercadopago'}
			</a>
		</div>
	{else}
		<div class="cart_navigation">
			<a href="{$link->getPageLink('history.php', true)|escape:'htmlall':'UTF-8'}" title="{l s='Orders' mod='mercadopago'}" class="button-exclusive btn btn-default">
				<i class="icon-chevron-left"></i>
				{l s='Return to orders history methods' mod='mercadopago'}
			</a>
		</div>
	{/if}

</div>

<script type="text/javascript" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/jquery.qrcode.min.js"></script>

<script type="text/javascript">
	$("#go-back").click(function () {
		if ("{$one_step|escape:'htmlall':'UTF-8'}" == 1)
			window.location = document.referrer;
		else
			window.history.go(-1);
	});

	$(document).ready(function(){ 
		{if $show_QRCode == 'true'}
			$('#output').qrcode("{$init_point|escape:'htmlall':'UTF-8'}");
		{/if}
		
	});

</script>
