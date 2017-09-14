{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Mercado Pago and DJTAL
*  @copyright 2015 DJTAL
*  @version   1.0.0
*  @link      http://www.mercadopago.com.br/
*  @license
*}

<div class="tab-pane" id="2viaBoleto">
	<h4 class="visible-print">{l s='2° Via Boleto' mod='mercadopago' } </h4>
	<div>
		<a id="linkShowBoleto" class="btn btn-primary" href="#boletoGen">{l s='Show ticket' mod='mercadopago'}</a><br />
		<br />
		<a href="{$boleto_url|escape:'htmlall':'UTF-8'}" class="btn btn-primary" target="_blank">{l s='Print ticket' mod='mercadopago'}</a>
	</div>
	<div style="display: none;">
		<iframe width="700" height="400" id="boletoGen" name="boletoGen" src="{$boleto_url|escape:'htmlall':'UTF-8'}" ></iframe>
	</div>
</div>
<script>
{literal}
$(document).ready(function() {
	$('a#linkShowBoleto').fancybox({autoDimensions: false, autoSize: false, height: 400, width: 700});
});
{/literal}
</script>