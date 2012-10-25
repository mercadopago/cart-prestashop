# MercadoPago – PrestaShop (1.3.6 to 1.4.7.3x)
---

## Installation:

1. Copy **mercadopago** folder to modules folder

---

## Setup Mercadopago

1. On your store administration,  go to **Modules > Payment & Gateways** <br />
**NOTE:** <i>Depending on PrestaShop Version, the module can be inside the **Other Modules**</i><br />
![module](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/module.png)<br />

2. Again in **Modules > Click** on MercadoPago Install and then Click on **MercadoPago Configure**<br />
![configure](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/configure.png)<br />

3. Set your Country where your account was created and Update.<br />
***Note:*** *If you change the Country where your account was created you need update first to refresh the excluded payment methods.*

4. Set your **Client id** and **Client Secret**. You could get in<br />
Argentina: https://www.mercadopago.com/mla/herramientas/aplicaciones<br />
Brazil: https://www.mercadopago.com/mlb/ferramentas/aplicacoes<br />

---

## Sync your backoffice with MercadoPago (IPN)

1. Go to **MercadoPago IPN admin page**:<br />
Argentina: https://www.mercadopago.com/mla/herramientas/notificaciones<br />
Brazil: https://www.mercadopago.com/mlb/ferramentas/notificacoes
2. Enter the URL as follow **[yourstoreaddress.com]**/modules/mercadopago/includes/retorno.php
