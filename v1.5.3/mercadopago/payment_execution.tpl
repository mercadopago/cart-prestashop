{capture name=path}{l s='Shipping'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Order summary' mod='mercadopago'}</h2>

{assign var='current_step' value='payment'}
{include file=$tpl_dir./order-steps.tpl}

<h3>{l s='Pagamento via MercadoPago' mod='mercadopago'}</h3>


<p>
	<img src="{$imgBtn}" alt="{l s='mercadopago' mod='mercadopago'}" style="float:left; margin: 0px 10px 5px 0px;" />
	<br /><br />
	<br /><br />
</p>

<form action="{$this_path_ssl}validation.php" method="post">
<p style="margin-top:20px;">
	{l s='Valor total do pedido:' mod='mercadopago'}
	{if $currencies|@count > 1}
		{foreach from=$currencies item=currency}
			<span id="amount_{$currency.id_currency}" class="price" style="display:none;">R$ {$total}</span>
		{/foreach}
	{else}
			<span id="amount_{$currencies.0.id_currency}" class="price">R$ {$total}</span>
	{/if}
</p>
<p>
	<b>{l s='Por favor, compruebe los métodos de pago aceptados por MercadoPago y Por favor haciendo clic \'Confirmar Compra\'' mod='mercadopago'}.</b>
</p>

<p>
	<center><img src="{$imgBnr}" alt="{l s='Formas de Pago MercadoPago' mod='mercadopago'}"></center>
</p>

<p class="cart_navigation">
	<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Otras formas de pago' mod='mercadopago'}</a>
	<input type="submit" name="submit" value="{l s='Confirmar Compra' mod='mercadopago'}" class="exclusive_large" />
</p>
</form>