{if $status == 'ok'}
	<center>
	<img src="{$imgBnr}" alt="{l s='Pague com MercadoPago' mod='mercadopago'}" />
	</center>
	<br />
	<h3>{l s='Parabéns! Seu pedido foi gerado com sucesso.' mod='mercadopago'}</h3>
	<p>{l s='O valor da sua compra é de:' mod='mercadopago'} <span class="price">{$totalApagar}</span></p>
	<p>{l s='Para efetuar o pagamento utilize o botão abaixo' mod='mercadopago'}</p>
	<p>{l s='Em caso de dúvidas favor utilizar o' mod='mercadopago'}	<a href="{$base_dir}contact-form.php">{l s='formulário de contato' mod='cheque'}</a>.</p>
	<br />
	{$formmercadopago}
	{else}
	<p class="warning">
	{l s='Houve alguma falha no envio do seu pedido. Por Favor entre em contato com o nosso Suporte' mod='mercadopago'} 
	<a href="{$base_dir}contact-form.php">{l s='customer support' mod='mercadopago'}</a>.
	</p>
{/if}
