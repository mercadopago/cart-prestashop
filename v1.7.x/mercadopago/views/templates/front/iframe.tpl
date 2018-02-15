{extends file='page.tpl'}

{block name="content"}
	<section>
		<iframe style = "background: white; border:none; width:100%; height:1000px;" src="{$preferences_url|escape:'htmlall':'UTF-8'}" ></iframe>
	</section>
{/block}
