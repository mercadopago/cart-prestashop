# MercadoPago – PrestaShop (1.3.6 to 1.4.7.3x)
---

* [Installation instructions](#usage)
* [Set up MercadoPago PrestaShop plugin](#prestaplugin)
* [Sync your backoffice with MercadoPago (IPN)](#IPN)

<a name="usage"></a>
## Installation:
1. Copy **mercadopago** folder to modules folder

---

<a name="prestaplugin"></a>
## Set up MercadoPago PrestaShop plugin
1. On your store administration,  go to **Modules > Payment & Gateways*** <br />
***Depending on PrestaShop Version, the module can be inside the <b>Other Modules</b>***<br />
![module](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/module.png)<br />

2. Again in **Modules > Click** on MercadoPago Install and then Click on **MercadoPago Configure**<br />
![configure](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/configure.png)<br />

3. Set your Country where your account was created and Update.
4. Set your **Client id** and **Client Secret**. You could get in<br />
Argentina: https://www.mercadopago.com/mla/herramientas/aplicaciones<br />
Brazil: https://www.mercadopago.com/mlb/ferramentas/aplicacoes<br />

5. Note: If you change the Country where your account was created you need update  first to refresh the excluded payment methods.

---

<a name="IPN"></a>
##Sync your backoffice with MercadoPago (IPN) 
1. Go to **MercadoPago IPN admin page**:<br />
Argentina: https://www.mercadopago.com/mla/herramientas/notificaciones<br />
Brazil: https://www.mercadopago.com/mlb/ferramentas/notificacoes
2. Enter the URL as follow **[yourstoreadress.com]**/modules/mercadopago/includes/retorno.php
