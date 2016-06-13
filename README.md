# Prestashop - Mercado Pago Module (v1.3.6 - 1.4.7.3x, 1.5.x, 1.6.x)
---

* [Features](#features)
* [Available versions](#available_versions)
* [Installation](#installation)
* [Setup](#setup)
* [Notifications](#notifications)
* [Example features](#pictures_features)
* [Feedback](#feedback)

<a name="features"></a>
##Features##

**Standard checkout**

This feature allows merchants to have a standard checkout. It includes all
payment methods (i.e. all credit cards, bar code payment, account money) and all window types (i.e. redirect, iframe, modal, blank and popup). Customization is not allowed.

**Credit Card Customized Checkout**

This feature will allow merchants to have a customized checkout for credit card
payment. Thus, it will be possible to customize its look and feel, customers won’t be redirected away to complete the payment, and it will also reduce the checkout steps, improving conversion rates.

**Ticket Checkout**

This feature allows merchants to have a customized ticket checkout, according to each country's ticket method (i.e Boleto in Brazil, RapiPago in Argentina, etc).  Thus, it will be possible to customize its look and feel, customers won’t be redirected away to complete the payment, and it will also reduce the checkout steps, improving conversion rates. The ticket link will be shown and when the customer click on it, another tab with the ticket will be opened.

**Print Ticket**

This functionality allows the user, if necessary go to the ticket on purchase history.

**Customer Card** <strong>Prestashop v1.6.x</strong>

This functionality allowed that their customers cards are stored in our API, allowing for future purchases his card to be loaded without the need to fill all values. This facilitates the payment and improves the shopping experience of your customer.

**Discount Coupon**<strong>Prestashop v1.6.x</strong>

This feature allows the customer to have discounts applying the <strong>Mercado Pago</strong> discount coupons, the store should contact the <strong>Mercado Pago</strong> to participate in a campaign and generated coupons.

***Important information***

**SSL certificate**

If you're using Ticket Checkout or Custom Checkout, it is a requirement that you have a SSL certificate, and the payment form to be provided under an HTTPS page.
During the sandbox mode tests, you can operate over HTTP, but for homologation you'll need to acquire the certificate in case you don't have it.

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
      <td><a href="https://github.com/mercadopago/cart-prestashop/tree/master/v1.3.6%20-%201.4.7.3x">v1.3.6 - 1.4.7.3x</a></td>
      <td>Deprecated (Old Version)</td>
      <td>Prestashop v1.3.6 - 1.4.7.3x</td>
    </tr>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-prestashop/tree/master/v1.5.x/mercadopago">v1.5.x</a></td>
      <td>Deprecated (Old Version)</td>
      <td>Prestashop v1.5.x</td>
    </tr>
    <tr>
      <td><a href="https://github.com/mercadopago/cart-prestashop/tree/master/v1.6.x">v1.6.x</a></td>
      <td>Stable (Current Version)</td>
      <td>Prestashop v1.6.x</td>
     </tr>
  </tbody>
</table>

<a name="installation"></a>
##Installation##

1. Download Prestashop: https://www.prestashop.com/es/versiones-para-programadores#previous-version
    * Prestashop v1.3.6 - 1.4.7.3x
    * Prestashop v1.5.x
    * Prestashop v1.6.x

<a name="setup"></a>
##Setup##

1. Copy **mercadopago** folder to **modules** folder.

2. On your store administration, go to **Modules > Modules**.

3. Search by **MercadoPago** and click install. <br />
You will receive the following message: " Module(s) installed successfully."
  ![Installation](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Installation.JPG)<br />

4. Set your **CLIENT_ID** and **CLIENT_SECRET**, or **PUBLIC_KEY** and **ACCESS_TOKEN** (depending on which module you're using). 

	Get your credentials in the following address:
	* Argentina: [https://www.mercadopago.com/mla/account/credentials](https://www.mercadopago.com/mla/account/credentials)
	* Brazil: [https://www.mercadopago.com/mlb/account/credentials](https://www.mercadopago.com/mlb/account/credentials)
	* Chile: [https://www.mercadopago.com/mlc/account/credentials](https://www.mercadopago.com/mlc/account/credentials)
	* Colombia: [https://www.mercadopago.com/mco/account/credentials](https://www.mercadopago.com/mco/account/credentials)
	* Mexico: [https://www.mercadopago.com/mlm/account/credentials](https://www.mercadopago.com/mlm/account/credentials)
	* Venezuela: [https://www.mercadopago.com/mlv/account/credentials](https://www.mercadopago.com/mlv/account/credentials)

***IMPORTANT:*** *This module will only work with the following currencies:*

* Argentina:
	* **ARS** (Peso Argentino)
* Brazil:
	* **BRL** (Real)
* Chile:
	* **CLP** (Peso Chileno)
* Colombia:
	* **COP** (Peso Colombiano)
* Mexico:
	* **MXN** (Peso Mexicano)
* Venezuela:
	* **VEF** (Bolivar fuerte)

<a name="notifications"></a>
## Sync your backoffice with MercadoPago (IPN) 
Your notification URL will be automatically send with your payment to our API.

<a name="pictures_features"></a>
## Example features

**Credit Card Customized Checkout**
<br/>
![pictures_features](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Checkout.jpg)

**Print Ticket**
<br/>
![pictures_features](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Ticket.jpg)

**Customer Card** <strong>Prestashop v1.6.x</strong>
<br/>
![pictures_features](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/CustomerCard.jpg)

**Discount Coupon** <strong>Prestashop v1.6.x</strong>
<br/>
![pictures_features](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Coupon.jpg)

<a name="feedback"></a>
##Feedback##

We want to know your opinion, please answer the following form.
<ul>
	<li><a href="http://goo.gl/forms/2n5jWHaQbfEtdy0E2" target="_blank">Portuguese</a></li>
	<li><a href="http://goo.gl/forms/A9bm8WuqTIZ89MI22" target="_blank">Spanish</a></li>
</ul>
