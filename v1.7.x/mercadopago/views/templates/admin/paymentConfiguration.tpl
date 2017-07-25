
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
				{l s='When enabled, all single payment methods will be disabled.' mod='mercadopago'}
			</label>
		</div>
		<div style="clear: both"></div>
	</div>
	<div style="clear: both"></div>
	{else}
		<div class="alert alert-danger">
	  		<strong>{l s='Danger!' mod='mercadopago'}</strong> {l s='Please, fill your credentials to enable the module.' mod='mercadopago'}
		</div>
	{/if}
</div>
{if $show}
<div class="panel panel-default">
	<div class="panel-heading">{l s='Payment Methods' mod='mercadopago'}</div>
	<div class="alert alert-info">
	  {l s='Enable and disable your payment methods.' mod='mercadopago'}
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
	<div class="panel-heading">{l s='Mercado Envios' mod='mercadopago'}</div>
	<div class="form-group border-none">
		<label class="payment-label col-lg-3">
		Enable Mercado Envios
		</label>
		<div class="col-lg-3">
			<div class="col-lg-4 control-label switch-label">{$label.active|escape:'htmlall':'UTF-8'}</div>
			<div class="col-lg-6 switch prestashop-switch fixed-width-lg">
				<input type="radio" name="MERCADOENVIOS_ACTIVATE" id="MERCADOENVIOS_ACTIVATE_on" value="1" {if ($mercadoEnviosActivate==1 )}checked="checked" {/if}>
				<label for="MERCADOENVIOS_ACTIVATE_on">{$button.yes|escape:'htmlall':'UTF-8'}</label>

				<input type="radio" name="MERCADOENVIOS_ACTIVATE" id="MERCADOENVIOS_ACTIVATE_off" value="0" {if empty($mercadoEnviosActivate)}checked="checked" {/if}>
				<label for="MERCADOENVIOS_off">{$button.no|escape:'htmlall':'UTF-8'}</label>
				<a class="slide-button btn"></a>
			</div>
		</div>
		<div class="col-lg-4">
			<label class="general-tooltip">
				{l s='If you enable this, the others payment type will be disable.' mod='mercadopago'}
			</label>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class="panel-footer">
		<button type="submit" value="1" name="btnSubmitPaymentConfig" class="btn btn-default pull-right">
			<i class="process-icon-save"></i> {$button.save|escape:'htmlall':'UTF-8'}
		</button>
	</div>

</div>
{/if}
</form>
