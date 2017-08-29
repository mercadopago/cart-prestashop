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
	<div class="return-div">
		<h4 id="id-confirmation-boleto">
			<strong> {l s='Thank you for your purchase! We are awaiting the payment.' mod='mercadopago'} <br> <br>
			</strong>
			<p><strong>
				{l s='Payment Id (MercadoPago): ' mod='mercadopago'}</strong>
				{$payment_id|escape:'htmlall':'UTF-8'}<br>
			</p>
	</div>
	<br>
	<div class="row">
		{if $boleto_url != null}
		<iframe src="{$boleto_url|escape:'htmlall':'UTF-8'}"
			class="boleto-frame" id="boletoframe" name="boletoframe">
			<div class="lightbox" id="text">
				<div class="box">
					<div class="content">
						<div class="processing">
							<span>{l s='Processing...' mod='mercadopago'}</span>
						</div>
					</div>
				</div>
			</div>
		</iframe>

		{/if} <span class="footer-logo"></span>
		</h4>
	</div>
</div>

<!-- <script type="text/javascript">
  ModuleAnalytics.setPublicKey("TEST-a603f517-310f-4956-a00d-93519fc17647")
  ModuleAnalytics.setPaymentId("123456")
  ModuleAnalytics.setPaymentType("credit_card")
  ModuleAnalytics.setCheckoutType("basic")
  ModuleAnalytics.put()
</script> -->

<script type="text/javascript">
	$( document ).ready(function() {
		printFrame("boletoframe");
	});

	function printFrame(id) {
        var frm = document.getElementById(id).contentWindow;
        frm.focus();// focus on contentWindow is needed on some ie versions
        frm.print();
        return false;
	}
</script>