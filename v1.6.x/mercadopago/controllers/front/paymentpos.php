<?php
/**
 * 2007-2015 PrestaShop.
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
 */

include_once dirname(__FILE__) . '/../../includes/MPApi.php';

class MercadoPagoPaymentPOSModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->paymentPOS();
    }

    public function paymentPOS()
    {
        $id_order = Tools::getValue("id_order");
        $order = new Order($id_order);

        $data = array(
            'transaction_amount' => (double) number_format($order->total_paid, 2, '.', ''),
            'payment_type' => 'credit_card',
            'external_reference' => $order->id_cart,
            'poi' => Tools::getValue("id_point"),
            'poi_type' => 'ABECS_PAX_D200_GPRS'
        );

        // populate all payments accoring to country
        $this->mercadopago = new MPApi(
            Configuration::get('MERCADOPAGO_CLIENT_ID'),
            Configuration::get('MERCADOPAGO_CLIENT_SECRET')
        );
        $result = $this->mercadopago->sendPaymentPoint($data);
        Tools::redirectAdmin($result['message']);
    }


    protected function redirectOrderDetail($orderId)
    {
        $getAdminLink = $this->context->link->getAdminLink('AdminOrders');
        $getViewOrder = $getAdminLink.'&vieworder&id_order='.$orderId;
        Tools::redirectAdmin($getViewOrder);
    }
}
