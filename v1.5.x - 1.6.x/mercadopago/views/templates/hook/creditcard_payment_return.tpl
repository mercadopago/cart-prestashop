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

<div class="return-div">
	<h4>
		<strong>
			{if $status_detail eq 'accredited'}
			{l s='Thank for your purchase!' mod='mercadopago'}</br>
			{l s='Your payment was accredited.' mod='mercadopago'}
		</strong>
		</br>
		</br>
		<h5>
			{l s='Card holder name: ' mod='mercadopago'}
			{$card_holder_name|escape:'htmlall'}</br>
			{l s='Credit card: ' mod='mercadopago'}
			{$four_digits|escape:'htmlall'}</br>
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
			{l s='Statement descriptor: ' mod='mercadopago'}
			{$statement_descriptor|escape:'htmlall'}</br>
			{l s='Payment id (MercadoPago): ' mod='mercadopago'}
			{$payment_id|escape:'htmlall'}</br>
		</h5>
		{elseif $status_detail eq 'pending_review_manual' || $status_detail eq 'pending_review'}
			{l s='We are processing the payment. In less than 2 business days we will tell you by e-mail if it is accredited or if we need more information.' mod='mercadopago'}
		{elseif $status_detail eq 'pending_contingency'}
			{l s='We are processing the payment. In less than an hour we will send you by e-mail the result.' mod='mercadopago'}
		{elseif status_detail eq 'expired'}
			{l s='Payment expired.' mod='mercadopago'}
		{/if}
	</h4>
	</br>
	<span class="footer-logo"></span>
</div>