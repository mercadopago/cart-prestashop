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
	*  @author    henriqueleite
	*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
	*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
	*  International Registered Trademark & Property of MercadoPago
	*}
{if $statusOrder == "Pendente"} 
	<div class="panel">
		<form action="{$cancel_action_url|escape:'htmlall':'UTF-8'}" method="post" id="frmCancelOrder">	   <div class="row">
				<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">	
			</div>	
			<br>
			<br>
			<input type="hidden" name="id_order" id="id_order"/>
			<div>
				<button class="btn btn-primary"
					value="{l s='Cancel the Order' mod='mercadopago'}"
					type="submit"
					id="btoCancelOrder">
						{l s='Cancel the Order' mod='mercadopago'}
					</button>
			</div>
		</form>
	</div>

	<br>
{/if}
{if isset($status)} 
	<div id="formAddPaymentPanel" class="panel">
		<div class="panel-heading">
			<i class="icon-truck"></i>
			MercadoEnvios - {l s='Track your delivery' mod='mercadopago'}
		</div>

		{if $substatus == "ready_to_print"} 
			<p class="alert alert-warning">
				{l s='Warning' mod='mercadopago'}
				<strong>{l s='Tag ready to print' mod='mercadopago'}</strong><br>
				<a href="#" onClick="window.open('{$tag_shipment|escape:'htmlall':'UTF-8'}', '_blank')" class="button btn btn-info button-medium">
				<span><i class="icon-ticket"></i>&nbsp;{l s='Open Tag PDF' mod='mercadopago'}</span></a>	
				&nbsp;
				<a href="#" onClick="window.open('{$tag_shipment_zebra|escape:'htmlall':'UTF-8'}', '_blank')" class="button btn btn-info button-medium">
				<span><i class="icon-ticket"></i>&nbsp;{l s='Open Tag for printer' mod='mercadopago'}</span></a>
				
			</p>		
		{else if $substatus == "printed"} 
			<p class="alert alert-success">
				{l s='Warning' mod='mercadopago'}
				<strong>{l s='Tag printed' mod='mercadopago'}</strong><br>
				<a href="#" onClick="window.open('{$tag_shipment|escape:'htmlall':'UTF-8'}', '_blank')" class="button btn btn-info button-medium">
				<span><i class="icon-ticket"></i>&nbsp;{l s='Open Tag PDF' mod='mercadopago'}</span></a>	
				&nbsp;
				<a href="#" onClick="window.open('{$tag_shipment_zebra|escape:'htmlall':'UTF-8'}', '_blank')" class="button btn btn-info button-medium">
				<span><i class="icon-ticket"></i>&nbsp;{l s='Open Tag for printer' mod='mercadopago'}</span></a>
				
			</p>
		{else}
			<p class="alert alert-danger">
				{l s='Warning' mod='mercadopago'}
				<strong>{$substatus_description|escape:'htmlall':'UTF-8'}</strong><br>				
			</p>		
		{/if}
		 <ul>
			<li>
				<span><strong class="dark">{l s='Status of delivery' mod='mercadopago'}:</strong>&nbsp;</span>{$status|escape:'htmlall':'UTF-8'}
			</li>
			<li>
				<span><strong class="dark">{l s='Status of tag' mod='mercadopago'}:</strong>&nbsp;</span>{$substatus_description|escape:'htmlall':'UTF-8'}
			</li>					 	
			<li>
				<span><strong class="dark">{l s='Type of shipment' mod='mercadopago'}:</strong>&nbsp;</span>{$name|escape:'htmlall':'UTF-8'}
			</li>	
			<li>
				<span><strong class="dark">{l s='Estimated handling limit' mod='mercadopago'}:</strong>&nbsp;</span>{$estimated_handling_limit|escape:'htmlall':'UTF-8'}
			</li>			
			<li>
				<span><strong class="dark">{l s='Estimated delivery' mod='mercadopago'}:</strong>	&nbsp;</span>{$estimated_delivery|escape:'htmlall':'UTF-8'}
			</li>	
			<li>
				<span><strong class="dark">{l s='Estimated delivery final' mod='mercadopago'}:</strong>	&nbsp;</span>{$estimated_delivery_final|escape:'htmlall':'UTF-8'}
			</li>						
		</ul>		

	</div>
{/if}

<script type="text/javascript">
	function getParameterByName(name, url) {
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, "\\$&");
	    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
	        results = regex.exec(url);
	    if (!results) return null;
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, " "));
	}

	var id_order = getParameterByName('id_order');
	document.getElementById("id_order").value = id_order;
</script>
