# PrestaShop - MercadoPago Module (v1.3.6 to 1.6.x)
---

* [Features](#features)
* [Available versions](#available_versions)
* [Installation](#installation)

<a name="features"></a>
##Features##
**Credit Card Customized Checkout**

This feature will allow merchants to have a customized checkout for credit card
payment. Thus, it will be possible to customize its look and feel, customers won’t be
redirected away to complete the payment, and it will also reduce the checkout steps
improving conversion rates.

*Available for Argentina, Brazil, Colombia, Mexico and Venezuela*

**Standard checkout**

This feature allows merchants to have a standard checkout. It includes all
payment methods (i.e. all credit cards, bar code payment, account money) and all
window types (i.e. redirect, iframe, modal, blank and popup). Customization is not allowed.

*Available for Argentina, Brazil, Chile, Colombia, Mexico and Venezuela*

<a name="available_versions"></a>
##Available versions##
<table>
  <thead>
    <tr>
      <th>Plugin Version</th>
      <th>Status</th>
      <th>Compatible Versions</th>
    </tr>
  <thead>
  <tbody>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-prestashop/tree/master/v1.3.6%20-%201.4.7.3x/">v1.3.6 - 1.4.7.3x</a></td>
      <td>Deprecated (Old Version)</td>
      <td>Prestashop 1.3.6 - 1.4.7.3x</td>
    </tr>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-prestashop/tree/master/v1.5.x%20-%201.6.x/">v1.5.x - 1.6.x</a></td>
      <td>Stable (Current Version)</td>
      <td>Prestashop v1.5.x - 1.6.x</td>
    </tr>    
  </tbody>
</table>

<a name="installation"></a>
##Installation##

1. Copy **mercadopago** folder to **modules** folder.

2. On your store administration, go to **Modules > Modules**.

3. Search by **MercadoPago** and click install. <br />
You will receive the following message: " Module(s) installed successfully."
	![Installation](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Installation.JPG)<br />

4. Set your **CLIENT_ID**, **CLIENT_SECRET** accordingly to your country:

	* Argentina: https://www.mercadopago.com/mla/herramientas/aplicaciones
	* Brazil: https://www.mercadopago.com/mlb/ferramentas/aplicacoes
	* Chile: https://www.mercadopago.com/mlc/herramientas/aplicaciones
	* Colombia: https://www.mercadopago.com/mco/herramientas/aplicaciones
	* Mexico: https://www.mercadopago.com/mlm/herramientas/aplicaciones
	* Venezuela: https://www.mercadopago.com/mlv/herramientas/aplicaciones

5. If you choose to use Custom Checkout, set your **PUBLIC_KEY** accordingly to your country:

	* Argentina: https://www.mercadopago.com/mla/account/credentials
	* Brazil: https://www.mercadopago.com/mlb/account/credentials
	* Colombia: https://www.mercadopago.com/mco/account/credentials
	* Mexico: https://www.mercadopago.com/mlm/account/credentials
	* Venezuela: https://www.mercadopago.com/mlv/account/credentials
