{capture name=path}{l s='Shipping'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='mercadopago'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Pagamento via MercadoPago' mod='mercadopago'}</h3>


<form action="{$this_path_ssl}validation.php" method="post">
<p style="margin-top:20px;">
    
    {l s='Valor total do pedido:' mod='mercadopago'}
    {$total}
</p>
<p>
	<b>{l s='Por favor confira as formas de pagamento aceitas pelo MercadoPago e 
	confirme sua compra clicando em \'Confirmar Compra\'' mod='mercadopago'}.</b>
</p>

<p>
	<center>{$imgBanner}</center>
</p>

<p class="cart_navigation">
	<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Outras formas de pagamento' mod='mercadopago'}</a>
	<input type="submit" name="submit" value="{l s='Confirmar Compra' mod='mercadopago'}" class="exclusive_large" />
</p>
</form>