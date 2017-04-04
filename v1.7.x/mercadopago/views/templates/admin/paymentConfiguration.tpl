<form id="module_form" class="defaultForm form-horizontal" action="{$currentIndex|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
<div class="panel">
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
				<input type="radio" name="MERCADOPAGO_STARDAND_ACTIVE" id="MERCADOPAGO_STARDAND_ACTIVE_on" value="1"  {if ($mercadoPagoActive == 1)}checked="checked"{/if}">
				<label for="MERCADOPAGO_STARDAND_ACTIVE_on">{$button.yes|escape:'htmlall':'UTF-8'}</label>

				<input type="radio" name="MERCADOPAGO_STARDAND_ACTIVE" id="MERCADOPAGO_STARDAND_ACTIVE_off" value="0" {if empty($mercadoPagoActive)}checked="checked"{/if}>
				<label for="MERCADOPAGO_STARDAND_ACTIVE_off">{$button.no|escape:'htmlall':'UTF-8'}</label>
				<a class="slide-button btn"></a>
			</div>
		</div>
		<div class="col-lg-4">
			<label class="general-tooltip">
				When enabled, all single payment methods will be disabled
			</label>
		</div>
		<div style="clear: both"></div>
	</div>
	<div style="clear: both"></div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">Payment Methods</div>
	<div class="alert alert-info">
	  Enable and disable your payment methods.
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
	<div class="panel-footer">
		<button type="submit" value="1" name="btnSubmitPaymentConfig" class="btn btn-default pull-right">
			<i class="process-icon-save"></i> {$button.save|escape:'htmlall':'UTF-8'}
		</button>
	</div>

</div>
</form>
