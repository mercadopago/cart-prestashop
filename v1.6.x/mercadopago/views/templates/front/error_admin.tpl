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
	{if $version == 5}
		<div class="error">
	{elseif $version == 6}
		<div class="bootstrap">
			<div class="alert alert-danger">
	{/if}

	{if $message_error != null}
		<span class="error">{l s='Fatal Error' mod='mercadopago'}: </span>
		{$message_error|escape:'htmlall':'UTF-8'}</br>
	{/if}	
	{if $version == 6}
		</div>
	{/if}
	</div>
	<div>
		<span>
			<a href="#">{l s='Please visit https://groups.google.com/forum/#!category-topic/mercadopago-developers-brasil/payments-modules/t_aMeOyZSuY for more details about this error message.' mod='mercadopago'}</a>
		</span>
	</div>
</div>

<script type="text/javascript">
	$("#go-back").click(function () {
			window.history.go(-1);
	});

</script>