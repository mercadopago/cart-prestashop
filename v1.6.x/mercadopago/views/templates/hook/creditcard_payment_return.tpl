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
	<div class="return-div">
		<h3>
			<strong>
				{if $status_detail eq 'accredited'}
				{l s='Thank for your purchase!' mod='mercadopago'}</br>
				{l s='Your payment was accredited.' mod='mercadopago'}
			</strong>
			</br>
			</br>

				{if $card_holder_name != null}
					<p><strong>{l s='Card holder name: ' mod='mercadopago'}</strong>
					{$card_holder_name|escape:'htmlall':'UTF-8'}</p>
				{/if}

				{if $four_digits != null}
					<p class="text"><strong>{l s='Credit card: ' mod='mercadopago'}</strong>
					{$four_digits|escape:'htmlall':'UTF-8'}</p>
				{/if}

				{if $payment_method_id != null}
					<p><strong>{l s='Payment method: ' mod='mercadopago'}</strong>
					{$payment_method_id|escape:'htmlall':'UTF-8'}</p>
				{/if}

				<p><strong>{l s='Amount: ' mod='mercadopago'}</strong>
				{$amount|escape:'htmlall':'UTF-8'}
				<p/>
				{if $installments != null}
					<p><strong>{l s='Installments: ' mod='mercadopago'}</strong>
					{$installments|escape:'htmlall':'UTF-8'}</p>
				{/if}

				{if $statement_descriptor != null && ! $statement_descriptor eq ''}
					<p><strong>{l s='Statement descriptor: ' mod='mercadopago'}</strong>
					{$statement_descriptor|escape:'htmlall':'UTF-8'}</p>
				{/if}
				<p><strong>{l s='Payment id (MercadoPago):' mod='mercadopago'}</strong>
				{$payment_id|escape:'htmlall':'UTF-8'}</p>				
			{elseif $status_detail eq 'pending_review_manual' || $status_detail eq 'pending_review'}
				{l s='We are processing the payment. In less than 2 business days we will tell you by e-mail if it is accredited or if we need more information.' mod='mercadopago'}
			{elseif $status_detail eq 'pending_contingency'}
				{l s='We are processing the payment. In less than an hour we will send you by e-mail the result.' mod='mercadopago'}
			{elseif status_detail eq 'expired'}
				{l s='Payment expired.' mod='mercadopago'}
			{/if}
		</h3>
		</br>
		<span class="footer-logo"></span>
	</div>
</div>

<!-- <script type="text/javascript">
  ModuleAnalytics.setPublicKey("TEST-a603f517-310f-4956-a00d-93519fc17647")
  ModuleAnalytics.setPaymentId("123456")
  ModuleAnalytics.setPaymentType("credit_card")
  ModuleAnalytics.setCheckoutType("basic")
  ModuleAnalytics.put()
</script> -->