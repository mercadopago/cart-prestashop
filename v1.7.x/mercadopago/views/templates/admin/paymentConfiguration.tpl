
<form id="module_form" class="defaultForm form-horizontal" action="{$currentIndex|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
<div class="panel">
	{if $show}
	<div class="form-group border-none">
		<div class="col-lg-2 logo-wrapper">
			<img src="{$thisPath|escape:'htmlall':'UTF-8'}views/img/mercadopago_125X125.jpg" class="payment-config-logo">
		</div>
		<label class="payment-label col-lg-3">
			Checkout Standard
		</label>
		<div class="col-lg-3">
			<div class="col-lg-4 control-label switch-label">{$label.active|escape:'htmlall':'UTF-8'}</div>
			<div class="col-lg-6 switch prestashop-switch fixed-width-lg">
				<input type="radio" name="MERCADOPAGO_STARDAND_ACTIVE" id="MERCADOPAGO_STARDAND_ACTIVE_on" value="1"  {if ($mercadoPagoActive == 1)}checked="checked"{/if}>
				<label for="MERCADOPAGO_STARDAND_ACTIVE_on">{$button.yes|escape:'htmlall':'UTF-8'}</label>

				<input type="radio" name="MERCADOPAGO_STARDAND_ACTIVE" id="MERCADOPAGO_STARDAND_ACTIVE_off" value="0" {if empty($mercadoPagoActive)}checked="checked"{/if}>
				<label for="MERCADOPAGO_STARDAND_ACTIVE_off">{$button.no|escape:'htmlall':'UTF-8'}</label>
				<a class="slide-button btn"></a>
			</div>
		</div>
		<div class="col-lg-4">
			<label class="general-tooltip">
				{l s='When enabled, all single payment methods will be disabled.' d='Modules.MercadoPago.Admin'}
			</label>
		</div>
		<div style="clear: both"></div>
	</div>
	<div style="clear: both"></div>
	{else}
		<div class="alert alert-danger">
	  		<strong>{l s='Danger!' d='Modules.MercadoPago.Admin'}</strong> {l s='Please, fill your credentials to enable the module.' d='Modules.MercadoPago.Admin'}
		</div>
	{/if}
</div>
{if $show}
<div class="panel panel-default">
	<div class="panel-heading">{l s='Payment Methods' d='Modules.MercadoPago.Admin'}</div>
	<div class="alert alert-info">
	  {l s='Enable and disable your payment methods.' d='Modules.MercadoPago.Admin'}
	</div>
	{foreach from=$payments key=sort item=payment}
		<div class="form-group">
			<div class="col-lg-2 logo-wrapper">
				<img src="{$payment.brand|escape:'htmlall':'UTF-8'}" alt="{$payment.title|escape:'htmlall':'UTF-8'}">
			</div>
<!-- 				<label class="payment-label col-lg-3">
				{$payment.title|escape:'htmlall':'UTF-8'}
				{if !empty($payment.thumbnail)}
					<img src="{$thisPath|escape:'htmlall':'UTF-8'}views/img/questionmark.png" alt="{$payment.type|escape:'htmlall':'UTF-8'}" data-toggle="tooltip" title="{$payment.tooltips|escape:'htmlall':'UTF-8'}" class="payment-config-tooltip">
				{/if}
			</label> -->
			<div class="col-lg-3">
				<div class="col-lg-4 control-label switch-label">
					{if ($payment.active == 1)}
						{$label.active|escape:'htmlall':'UTF-8'}
					{else}
						{$label.disable|escape:'htmlall':'UTF-8'}
					{/if}
				</div>
				<div class="col-lg-6 switch prestashop-switch fixed-width-lg">
					<input type="radio" name="MERCADOPAGO_{$payment.id|escape:'htmlall':'UTF-8'}_ACTIVE" id="MERCADOPAGO_{$payment.id|escape:'htmlall':'UTF-8'}_ACTIVE_on" value="1" {if ($payment.active == 1)}checked="checked"{/if}>
					<label for="MERCADOPAGO_{$payment.id|escape:'htmlall':'UTF-8'}_ACTIVE_on">{$button.yes|escape:'htmlall':'UTF-8'}</label>

					<input type="radio" name="MERCADOPAGO_{$payment.id|escape:'htmlall':'UTF-8'}_ACTIVE" id="MERCADOPAGO_{$payment.id|escape:'htmlall':'UTF-8'}_ACTIVE_off" value="0" {if empty($payment.active)}checked="checked"{/if}>
					<label for="MERCADOPAGO_{$payment.id|escape:'htmlall':'UTF-8'}_ACTIVE_off">{$button.no|escape:'htmlall':'UTF-8'}</label>
					<a class="slide-button btn"></a>
				</div>
			</div>
			<div style="clear: both"></div>
		</div>
		<div style="clear: both"></div>
	{/foreach}
</div>
<div class="panel">
	<div class="panel-heading">{l s='Mercado Envios' d='Modules.MercadoPago.Admin'}</div>
	<div class="panel-body">
		<div class="form-group">
			<label class="control-label col-lg-3 required"> {l s='Custom text to use with delivery.' mod='mercadopago'} </label>
			<div class="col-lg-5">
				<input type="text" name="MERCADOPAGO_DEFAULT_SHIPMENT" id="MERCADOPAGO_DEFAULT_SHIPMENT" value="{$MERCADOPAGO_DEFAULT_SHIPMENT|escape:'htmlall':'UTF-8'}" class="">
				<p class="help-block">{l s='Ex: The product will be send in 3 a 8 days.' mod='mercadopago'}.</p>
			</div>
		</div>

		<div class="form-group">
			<div id="conf_MERCADOENVIOS_ACTIVATE">
				<label class="control-label col-lg-3">{l s='Enable Mercado envios.' mod='mercadopago'}</label>
				<div class="col-lg-9">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="MERCADOENVIOS_ACTIVATE" id="MERCADOENVIOS_ACTIVATE_on" value="1" checked="checked" {if ($mercadoEnviosActivate == 1)}checked="checked"{/if}>
						<label for="MERCADOENVIOS_ACTIVATE_on" class="radioCheck">{$button.yes|escape:'htmlall':'UTF-8'}</label>
						<input type="radio" name="MERCADOENVIOS_ACTIVATE" id="MERCADOENVIOS_ACTIVATE_off" value="0" {if empty($mercadoEnviosActivate)}checked="checked"{/if}>
						<label for="MERCADOENVIOS_ACTIVATE_off" class="radioCheck">{$button.no|escape:'htmlall':'UTF-8'}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
				<div class="col-lg-9 col-lg-offset-3">
					<div class="help-block">{l s='Use Mercado Envios with Mercado Pago standard' mod='mercadopago'}</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12 text-center"> 
		<button type="submit" value="1" name="btnSubmitPaymentConfig" class="btn btn-primary btn-lg">
			{l s='Save' mod='mercadopago'}
		</button>
	</div>
</div>

{/if}
</form>

<script type="text/javascript">
	console.info({$mercadoPagoActive});
	{if $mercadoPagoActive}
		$("#MERCADOPAGO_STARDAND_ACTIVE_on").attr("checked", true);
	{/if}
</script>
