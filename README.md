# PrestaShop - Mercadopago Module (v1.3.6 - 1.4.7.3x | 1.5.3 -  1.6.x)

---
*Available for Argentina, Brazil, Mexico and Venezuela*

## Installation:

1. Copy mails to the Prestashop root installation. Make sure to keep the Prestashop folders structure.

2. Copy mercadopago folder to modules folder.

3. On your store administration, go to **Modules > Modules**.

4. Search by **MercadoPago** and click install. <br />
You will receive the following message: " Module(s) installed successfully."
	![Installation](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Installation.JPG)<br />


**Note**:  For modules in the previous version is not necessary to make the first step. Besides the installation path be different: **Modules > Payment Gateways**.

## Configuration:

1. On your store administration, go to Modules > Modules
	![Configuration-1](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Configuration-1.JPG)<br />

2. Search by **MercadoPago**
	![Configuration-2](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Configuration-2.JPG)<br />

3. Click in **Configure**. Will open the page following:
	![Configuration-3](https://raw.github.com/mercadopago/cart-prestashop/master/README.img/Configuration-3.JPG)<br />

4. Set your **CLIENT_ID**, **CLIENT_SECRET** and **COUNTRY**. 
Get your **CLIENT_ID** and **CLIENT_SECRET** of according to his country:

	* Argentina: https://www.mercadopago.com/mla/herramientas/aplicaciones
	* Brazil: https://www.mercadopago.com/mlb/ferramentas/aplicacoes
	* Mexico: https://www.mercadopago.com/mlm/herramientas/aplicaciones
	* Venezuela: https://www.mercadopago.com/mlv/herramientas/aplicaciones

**Note**:  If you change the Country where your account was created you need update first to refresh the excluded payment methods.

## Sync your backoffice with Mercadopago (IPN) 

Go to **Mercadopago IPN configuration**:

    * Argentina: https://www.mercadopago.com/mla/herramientas/notificaciones
    * Brazil: https://www.mercadopago.com/mlb/ferramentas/notificacoes
    * Mexico: https://www.mercadopago.com/mlm/herramientas/notificaciones
    * Venezuela: https://www.mercadopago.com/mlv/herramientas/notificaciones

Enter the URL as follow: ***[yourstoreaddress.com]***/modules/mercadopago/includes/retorno.php

## FAQ

The moment finalizing the purchase to make payment is displayed the error: "invalid_items: currency_id invalid"?
		
During the installation of Prestashop always use the regional settings of your country.
If at the time of installation is selected settings of USA, later to correct it, failures may occur during the upgrade, causing the error referred above.
	
How to customize the text displayed in the checkout?

Open the following files with notepad and change the information you want:

	Prestashop folder ... \ modules \ MercadoPago
		> confirm.tpl
		> payment_return.tpl
