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
	<script defer type="text/javascript"
	src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/jquery.dd.js"></script>	
	<div class="panel">
			<input type="hidden" name="token_form" id="token_form" value="{$token_form|escape:'htmlall':'UTF-8'}"/>

			<div class="row">
				<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">
			</div>
			<br>
			<div class="row">
				{if $statusOrder == "Pendente"}
					<form action="{$cancel_action_url|escape:'htmlall':'UTF-8'}" method="post" id="frmCancelOrder">
						<input type="hidden" name="id_order" id="id_order"/>
						<div class="col-md-4">
							<button class="btn btn-primary"
								value="{l s='Cancel the Order' mod='mercadopago'}"
								type="submit"
								id="btoCancelOrder">
									{l s='Cancel the Order' mod='mercadopago'}
							</button>
						</div>
					</form>
				{/if}
			</div>
			<div class="col-sm-2 form-group">
			    <label for="exampleInputEmail1">Point H</label>
				<select name="pos_id" id="pos_id" class="form-control">
					{html_options options=$pos_options}
				</select>
				<br>
				<button type="button" name="payment_pos_action" id="payment_pos_action" class="btn btn-primary">{l s='Send payment' mod='mercadopago'}</button>
			  </div>
			  <div class="form-group">
				<img src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadopago_point_2.jpg" alt="{l s='Pay with Mercado Pago' mod='mercadopago'}" alt="Point" width="200" height="150" class="img-responsive">
			  </div>
			</div>
	</div>

	<br>
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
	$( document ).ready(function() {
    	console.log( "ready!" );
    	alert("teste teste");
    	$('#payment_pos_action').click(function(){
    		alert("teste teste");

    		alert($('#pos_id').val());

			$.ajax({
				type : "GET",
				url : "{$payment_pos_action_url|escape:'htmlall':'UTF-8'}" + "?id_order={$id_order|escape:'htmlall':'UTF-8'}" +
				"&id_point="+ $('#pos_id').val(),
					success : function(r) {
						if (r.status == 200) {
							console.info("Sucesso");
						}
					},
					error : function() {
						console.error("Ocorreu um erro");
					}
				});
    	});
		{if $statusOrder == "Pendente"}
			document.getElementById("id_order").value = "{$id_order|escape:'htmlall':'UTF-8'}";
		{/if}
	});

</script>
