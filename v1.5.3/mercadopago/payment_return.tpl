{if $status == 'ok'}
	<center>
	<img src="{$imgBnr}" alt="{l s='Pague con MercadoPago' mod='mercadopago'}" />
	</center>
	<br />
	<h3>{l s='¡Enhorabuena! Su pedido se ha generado con éxito.' mod='mercadopago'}</h3>
	<p>{l s='El importe de su compra es de:' mod='mercadopago'} <span class="price">{$totalApagar}</span></p>
	<p>{l s='Para efectuar el pago utilice el botón de abajo' mod='mercadopago'}</p>
	<p>{l s='En caso de dudas utilizar el' mod='mercadopago'}	<a href="{$base_dir}contact-form.php">{l s='formulario de contacto' mod='cheque'}</a>.</p>
	<br />
	{$formmercadopago}
	{else}
	<p class="warning">
	{l s='Hubo alguna falla en el proceso de su solicitud. Por favor, póngase en contacto con nuestro Servicio de Soporte' mod='mercadopago'} 
	<a href="{$base_dir}contact-form.php">{l s='customer support' mod='mercadopago'}</a>.
	</p>
{/if}
