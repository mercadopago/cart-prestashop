<link href="{$backOfficeCssUrl|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">
<link href="{$marketingCssUrl|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">

{if $message}
	{if $message.success}
		{assign var="alert" value="alert-success"}
	{else}
		{assign var="alert" value="alert-danger"}
	{/if}
	<div class="bootstrap">
		<div class="module_confirmation conf confirm alert {$alert|escape:'htmlall':'UTF-8'}">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{$message.text|escape:'htmlall':'UTF-8'}
		</div>
	</div>
{/if}

<div class="mercadopago-tabs">
	{if $tabs}
		<nav>
		{foreach $tabs as $tab}
			<a class="tab-title {if isset($selectedTab) && $tab.id==$selectedTab}active{/if}" href="#" id="{$tab.id|escape:'htmlall':'UTF-8'}" data-target="#mercadopago-tabs-{$tab.id|escape:'htmlall':'UTF-8'}">{$tab.title|escape:'htmlall':'UTF-8'}</a>
		{/foreach}
		</nav>
		<div class="content">
		{foreach $tabs as $tab}
			<div class="tab-content" id="mercadopago-tabs-{$tab.id|escape:'htmlall':'UTF-8'}" style="display:{if isset($selectedTab) && $tab.id==$selectedTab}block{else}none{/if}">
                {html_entity_decode($tab.content|escape:'htmlall':'UTF-8')}
			</div>
		{/foreach}
		</div>
	{/if}
</div>
<script type='text/javascript' src="{$backOfficeJsUrl|escape:'htmlall':'UTF-8'}"></script>