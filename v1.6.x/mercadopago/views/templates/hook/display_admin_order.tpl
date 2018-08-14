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
			{if $statusOrder == "Pendente" || (isset($showPoint) && $showPoint) || isset($status)}
			<div class="row">
				<img class="logo_cupom" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">
			</div>
			<br>
			<br>
			{/if}
			{if $pos_active == "true"}
				<div class="row">
					<div class="col-md-12"> <span id="show_message" style="display: none;"> </span> </div>
					<div class="col-md-12" style="display: none;" id="show_message_waiting">
						<span class="alert alert-warning">Please waiting...</span>
					</div>
				</div>
				<br>
				<br>
			{/if}
			{if $statusOrder == "Pendente"}
			<div class="row">
				<h3>{l s='You can cancel the order and the payment in Mercado Pago.' mod='mercadopago'}</h3>
				<br>
				<form action="{$cancel_action_url|escape:'htmlall':'UTF-8'}" method="post" id="frmCancelOrder">
					<input type="hidden" name="token_form" id="token_form" value="{$token_form|escape:'htmlall':'UTF-8'}"/>
					<input type="hidden" name="id_order" id="id_order"/>
					<div class="col-md-4">
						<button class="btn btn-primary"
							value="{l s='Cancel the Order' mod='mercadopago'}"
							type="button"
							id="btoCancelOrder">
								{l s='Cancel the Order' mod='mercadopago'}
						</button>
					</div>
				</form>
			</div>
			{/if}
			{if isset($showPoint) && $showPoint}
			<div class="col-sm-2 form-group">
			    <label for="exampleInputEmail1">Point Mercado Pago</label>
				<select name="pos_id" id="pos_id" class="form-control">
					{html_options options=$pos_options}
				</select>
				<br>
			</div>
			<div class="form-group">
				<img src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadopago_point_2.jpg" alt="{l s='Pay with Mercado Pago' mod='mercadopago'}" alt="Point" width="200" height="150" class="img-responsive">
			</div>
			<div class="row">
				<button type="button" name="payment_pos_action" id="payment_pos_action" class="btn btn-success">{l s='Send payment' mod='mercadopago'}</button>
				<button type="button" name="payment_pos_get_action" id="payment_pos_get_action" class="btn btn-info">{l s='Get payment' mod='mercadopago'}</button>
				<button type="button" name="payment_pos_cancel_action" id="payment_pos_cancel_action" class="btn btn-danger">{l s='Cancel payment' mod='mercadopago'}</button>
			</div>
			{/if}
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
		{else if $status == "pending"}
			<p class="alert alert-warning">
				{l s='Warning' mod='mercadopago'}
				<strong>{l s='The tag is pending' mod='mercadopago'}</strong><br>
			</p>
		{else}
			<strong>{$substatus_description|escape:'htmlall':'UTF-8'}</strong><br>
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


<!-- 			$.ajax({
				type : "GET",
				url : "{$payment_pos_action_url|escape:'htmlall':'UTF-8'}" + "?id_order={$id_order|escape:'htmlall':'UTF-8'}" +
				"&id_point="+ $('#pos_id').val(),
					success : function(r) {
						alert("entrou aqui");
						if (r.status == 200) {
							console.info("Sucesso");
						} else {
							alert(r.message);
						}
					},
					error : function(r) {
						console.info(r);
						alert(r.message);
					}
				}); -->

<script type="text/javascript">

	// function cancelOrder() {
	// 	location.reload();
	// }
	// 

	{if $statusOrder == "Pendente"}
		$('#btoCancelOrder').click(function() {

			if (window.confirm("{l s='Do you have sure that want to cancel this order?' mod='mercadopago'}")) {
				$.get( "{$cancel_action_url|escape:'htmlall':'UTF-8'}" + "?id_order={$id_order|escape:'htmlall':'UTF-8'}" +
					"&action=get&token_form={$token_form|escape:'htmlall':'UTF-8'}", function(data) {
				if (data.status == "200") {
					alert(data.message);
					location.reload();
				} else {
					alert(data.message);
				}
			},  "json");
			}

		});
	{/if}

	{if $pos_active == "true"}
	$( document ).ready(function() {
		$( "#show_message" ).hide();

    	$('#payment_pos_get_action').click(function(){
    		$( "#show_message" ).hide();
    		$( "#show_message_waiting" ).show();
			$.get( "{$payment_pos_action_url|escape:'htmlall':'UTF-8'}" + "?id_order={$id_order|escape:'htmlall':'UTF-8'}" +
					"&action=get", function(data) {
				showMessage(data);
			},  "json")
			  .fail(function(data) {
			  	alert("Ocurred a error, send a email to modulos@mercadopago.com.br");
			    console.info("Error:  " +  data );
			  }).complete(function(data) {
			  	$( "#show_message_waiting" ).hide();
			  });
		});

    	$('#payment_pos_cancel_action').click(function(){
    		$( "#show_message" ).hide();
    		$( "#show_message_waiting" ).show();
			$.get( "{$payment_pos_action_url|escape:'htmlall':'UTF-8'}" + "?id_order={$id_order|escape:'htmlall':'UTF-8'}" +
					"&id_point="+ $('#pos_id').val() + '&action=delete', function(data) {

			  	showMessage(data);

			},  "json")
			  .fail(function(data) {
			    alert("Error:  " +  data );
			  }).complete(function(data) {
			  	$( "#show_message_waiting" ).hide();
			  });
		});

    	$('#payment_pos_action').click(function(){
			$( "#show_message" ).hide();
			$( "#show_message_waiting" ).show();
			$.get( "{$payment_pos_action_url|escape:'htmlall':'UTF-8'}" + "?id_order={$id_order|escape:'htmlall':'UTF-8'}" +
					"&id_point="+ $('#pos_id').val() + '&action=post', function(data) {
				showMessage(data);
			},  "json")
			  .fail(function(data) {
			    alert("Error:  " +  data );
			  }).complete(function(data) {
			  	$( "#show_message_waiting" ).hide();
			  });
		});

		$( "#payment_pos_get_action" ).click();

	});
	{/if}
	function showMessage(data) {
		if (data.status == 200 || data.status  == 201) {
	  		$("#show_message").removeClass("alert alert-danger");
	  		$("#show_message").addClass("alert alert-success");
	  	} else {
	  		$("#show_message").removeClass("alert alert-success");
	  		$("#show_message").addClass("alert alert-danger");
	  	}
	  	$("#show_message").html(data.message);
	  	$("#show_message").show();
	}

	{if $statusOrder == "Pendente"}
		document.getElementById("id_order").value = "{$id_order|escape:'htmlall':'UTF-8'}";
	{/if}

</script>
