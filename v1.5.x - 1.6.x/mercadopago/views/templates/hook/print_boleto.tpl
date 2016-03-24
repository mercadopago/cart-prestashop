{**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author    henriqueleite
*  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of MercadoPago
*}

  <div class="row">
		<div class="col-xs-12 col-sm-6">
			<!--  <ul class="address alternate_item box">
				<li><h3 class="page-subheading">Dados do pagamento</h3></li>
					<li><span class="address_firstname">Teste</span> <span class="address_lastname">Teste</span></li>
					<li class="address_company">Teste</li>
					<li><span class="address_vat_number">123</span></li>
					<li><span class="address_address1">Testes</span></li>
					<li class="address_address2">Teste</li>
					<li><span class="address_postcode">06542-089</span> <span class="address_city">Osasco</span></li>
					<li><span class="address_Country:name">Brazil</span></li>
					<li><span class="address_phone">12345678</span></li>
					<li class="address_phone_mobile">12345678</li>
			</ul>-->
			
			<div class="box box-small clearfix">
			
				<label>{$boleto_url|escape}</label>
				<a target="_parent" href="{$boleto_url|escape:'htmlall'}">link</a>
				{if $boleto_url != null}
					<a href="#" onClick="window.open('{$boleto_url|escape:'htmlall'}', '_parent')" class="button btn btn-info button-medium pull-right">
					<span>{l s='Open Ticket' mod='mercadopago'}<i class="icon-chevron-right right"></i></span></a>
				{/if}
			</div>
			
		</div>
	</div>