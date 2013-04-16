# PrestaShop - Mercadopago Module (v1.3.6 - 1.4.7.3x - 1.5.3)

---
*Available for Argentina, Brazil, Mexico and Venezuela*

---

## Installation:

1. Copy mails to the Prestashop root installation. Make sure to keep the Prestashop folders structure.
2. Copy mercadopago folder to modules folder.
3. On your store administration, go to **Modules > Modules**.
4. Search by **MercadoPago** and click install.
You will receive the following message: " Module(s) installed successfully."
	![Installation-1](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Installation-1.png)

---	

## Configuration:
1. On your store administration, go to Modules > Modules
2. Search by **MercadoPago**
3. Click in **Configure**. Will open the page following:
4. Set your **CLIENT_ID**, **CLIENT_SECRET** and **COUNTRY**. 
Get your **CLIENT_ID** and **CLIENT_SECRET** of according to his country:

	* Argentina: [https://www.mercadopago.com/mla/herramientas/aplicaciones](https://www.mercadopago.com/mla/herramientas/aplicaciones)
	* Brazil: [https://www.mercadopago.com/mlb/ferramentas/aplicacoes](https://www.mercadopago.com/mlb/ferramentas/aplicacoes)
	* Mexico: [https://www.mercadopago.com/mlm/herramientas/aplicaciones](https://www.mercadopago.com/mlm/herramientas/aplicaciones)
	* Venezuela: [https://www.mercadopago.com/mlv/herramientas/aplicaciones](https://www.mercadopago.com/mlv/herramientas/aplicaciones)

**Note**:  If you change the Country where your account was created you need update first to refresh the excluded payment methods.

---

## Sync your backoffice with Mercadopago (IPN) 

1. Go to **Mercadopago IPN configuration**:

    * Argentina: [https://www.mercadopago.com/mla/herramientas/notificaciones](https://www.mercadopago.com/mla/herramientas/notificaciones)
    * Brazil: [https://www.mercadopago.com/mlb/ferramentas/notificacoes](https://www.mercadopago.com/mlb/ferramentas/notificacoes)
    * Mexico: [https://www.mercadopago.com/mlm/herramientas/notificaciones](https://www.mercadopago.com/mlm/herramientas/notificaciones)
    * Venezuela: [https://www.mercadopago.com/mlv/ferramentas/notificacoes](https://www.mercadopago.com/mlv/ferramentas/notificacoes)

2. Enter the URL as follow: ***[yourstoreaddress.com]***/modules/mercadopago/includes/retorno.php











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
	* Mexico: [https://www.mercadopago.com/mlm/herramientas/aplicaciones](https://www.mercadopago.com/mlm/herramientas/aplicaciones)
	* Venezuela: [https://www.mercadopago.com/mlv/herramientas/aplicaciones](https://www.mercadopago.com/mlv/herramientas/aplicaciones)