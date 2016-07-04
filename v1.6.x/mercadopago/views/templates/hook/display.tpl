{if isset($mensagem)}

<script type="text/javascript">
	alert("{$mensagem|escape:'htmlall':'UTF-8'}");
</script>

{/if}


{if $errors|@count > 0}
	{foreach from=$errors item=error}
	<script type="text/javascript">
		alert("{$error|escape:'htmlall':'UTF-8'}");
	</script>	
	{/foreach}
{/if}