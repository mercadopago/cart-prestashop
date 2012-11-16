# PrestaShop - Mercadopago Module (v1.3.6 - 1.4.7.3x)
---
*Available for Argentina and Brazil*


## Installation:

1. Copy mercadopago folder to modules folder

---
## Setup MercadoPago

1. On your store administration,  go to **Modules > Payment & Gateways**

	***NOTE:*** *Depending on PrestaShop Version, the module can be inside **Other Modules***

	![module](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/module.png)

2. Again in **Modules**, click on **MercadoPago Install** and then click on **MercadoPago Configure**

	![configure](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/configure.png)<br />

3. Set your Country to the same where your account was created on, and **Update**.

	***Note:*** *If you change the Country where your account was created you need update  first to refresh the excluded payment methods.*

4. Set your **CLIENT_ID** and **CLIENT_SECRET**.

	Get your **CLIENT_ID** and **CLIENT_SECRET** in the following address:
	* Argentina: [https://www.mercadopago.com/mla/herramientas/aplicaciones](https://www.mercadopago.com/mla/herramientas/aplicaciones)
	* Brazil: [https://www.mercadopago.com/mlb/ferramentas/aplicacoes](https://www.mercadopago.com/mlb/ferramentas/aplicacoes)

---
## Sync your backoffice with Mercadopago (IPN) 

1. Go to **Mercadopago IPN configuration**:
    * Argentina: [https://www.mercadopago.com/mla/herramientas/notificaciones](https://www.mercadopago.com/mla/herramientas/notificaciones)
    * Brazil: [https://www.mercadopago.com/mlb/ferramentas/notificacoes](https://www.mercadopago.com/mlb/ferramentas/notificacoes)

2. Enter the URL as follow: ***[yourstoreaddress.com]***/modules/mercadopago/includes/retorno.php
