{** * 2007-2015 PrestaShop * * NOTICE OF LICENSE * * This source file is
subject to the Open Software License (OSL 3.0) * that is bundled with
this package in the file LICENSE.txt. * It is also available through the
world-wide-web at this URL: * http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to *
obtain it through the world-wide-web, please send an email * to
license@prestashop.com so we can send you a copy immediately. * *
DISCLAIMER * * Do not edit or add to this file if you wish to upgrade
PrestaShop to newer * versions in the future. If you wish to customize
PrestaShop for your * needs please refer to http://www.prestashop.com
for more information. * * @author MercadoPago * @copyright Copyright
(c) MercadoPago [http://www.mercadopago.com] * @license
http://opensource.org/licenses/osl-3.0.php Open Software License (OSL
3.0) * International Registered Trademark & Property of MercadoPago *}

<div class="mp-module">
	<div class="row">
		<div class="col-xs-12 col-md-6">
			{if $window_type != 'iframe'} <a
				href="{$preferences_url|escape:'htmlall':'UTF-8'}" id="id-standard"
				mp-mode="{$window_type|escape:'htmlall':'UTF-8'}" name="MP-Checkout">
				<div class="mp-form hover">
					<div class="row">
						<div class="col">
							<img
								src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo_120_31.png"
								id="id-standard-logo"> <img
								src="{$standard_banner|escape:'htmlall':'UTF-8'}"
								class="mp-standard-banner" /> <span
								class="payment-label standard">{l s='Pay via MercadoPago
								and split into up to 24 times' mod='mercadopago'}</span>
						</div>
					</div>
				</div>
			</a> {else}
			<div class="mp-form">
				<iframe src="{$preferences_url|escape:'htmlall':'UTF-8'}" name="MP-Checkout"
					width="{$iframe_width|escape:'htmlall':'UTF-8'}"
					height="{$iframe_height|escape:'htmlall':'UTF-8'}" frameborder="0">
				</iframe>
			</div>
			{/if}
		</div>
	</div>
</div>
