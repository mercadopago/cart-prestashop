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

{if isset($boleto_url)}
<div class="col-xs-12 col-sm-6 box">
	<div class="row">
		<div class="col">
			<img style="text-align: right;" width="40%" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/mercadopago.png">	
		</div>
	</div>
<br>
	<p><strong class="dark">{l s='Before printing check the expiration date.' mod='mercadopago'}</strong></p>
	 <ul>
	 	<li class="page-subheading">
	 	</li>
		<li>
			<a href="#" onClick="window.open('{$boleto_url|escape:'htmlall':'UTF-8'}', '_blank')" class="button btn btn-default button-medium pull-right">
			<span>{l s='Open Ticket' mod='mercadopago'}<i class="icon-chevron-right right"></i></span></a>
		</li>				
	</ul>
</div>
{/if}