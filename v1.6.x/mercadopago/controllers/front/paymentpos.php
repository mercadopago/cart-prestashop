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

    public static function createMP()
    {
        $mp = new MPApi();
        $mp->setCredentialsStandard(
            Configuration::get('MERCADOPAGO_CLIENT_ID'),
            Configuration::get('MERCADOPAGO_CLIENT_SECRET')
        );
    }

    public function paymentPOS()
    {
        $id_order = Tools::getValue("id_order");
        $order = new Order($id_order);
        $poi = (int)Tools::getValue("id_point");
        $typePOS = $this->getTypePOS($poi);
        $action = Tools::getValue("action");
        $response = null;
        if ($action == "post") {
            $response = $this->postTransactionPayment($order, $poi, $typePOS);
        } elseif ($action == "get") {
            $response = $this->getTransactionPayment($id_order);
        } elseif ($action == "delete") {
            $response = $this->deleteTransactionPayment($poi);
        }

        header('Content-Type: application/json');
        echo Tools::jsonEncode($response);
        exit;
    }

    private function getPOIAndTypePOS($poi)
    {
        $json = $this->getJSONPOS();
        $return = null;
        foreach ($json['points'] as $field) {
            if ($field['poi']  == $poi) {
                $return = array('poi' => $field['poi'], 'poi_type' => $field['poi_type']);
                return $return;
            }
        }
    }

    private function getTypePOS($poi)
    {
        $json = $this->getJSONPOS();

        foreach ($json['points'] as $field) {
            if ($field['poi']  == $poi) {
                return $field['model'];
            }
        }
    }

    public function getJSONPOS()
    {
        if ($str = Tools::file_get_contents(dirname(__FILE__) . '/../../pos.json')) {
            return Tools::jsonDecode($str, true);
        }
        return null;
    }

    private function deleteTransactionPayment($poi)
    {
        if ($poi_and_type = $this->getPOIAndTypePOS($poi)) {
            $data = array(
                'poi' => $poi_and_type['poi'],
                'poi_type' => $poi_and_type['poi_type']
            );
            $this->mercadopago = MercadoPagoPaymentPOSModuleFrontController::createMP();
            $result = $this->mercadopago->deletePaymentPoint($data);
            if ($result['status'] == '200') {
                $response = array(
                'status' => '200',
                'message' => "The transaction was cancelled."
                );
                return $response;
            }
        }
        $response = array(
        'status' => '404',
        'message' => "There isn't transaction for that device."
        );

        return $response;
    }



    private function getTransactionPayment($id_order)
    {
        $exist_transaction = false;
        $id_transaction = $this->getIdTransactionPOS($id_order);
        if ($id_transaction) {
            $this->mercadopago = MercadoPagoPaymentPOSModuleFrontController::createMP();

            $result = $this->mercadopago->getPaymentPoint($id_transaction);
            if ($result['status'] == '200' &&
                $result['response']['status'] == 'created') {
                $response = array(
                'status' => '200',
                'message' => "There is a pending transaction for that device."
                );
                $exist_transaction = true;
            }
        }

        if (! $exist_transaction) {
            $response = array(
            'status' => '404',
            'message' => "There isn't transaction for that device."
            );
        }
        return $response;
    }

    private function postTransactionPayment($order, $poi, $typePOS)
    {
        if ($typePOS == "I") {
            return $this->postD200($order, $poi);
        }
        return null;
    }


    private function postD200($order, $poi)
    {
        $data = array(
            'transaction_amount' => (double) number_format($order->total_paid, 2, '.', ''),
            'payment_type' => 'credit_card',
            'external_reference' => $order->id_cart,
            'poi' => $poi,
            "installments" => 1,
            'poi_type' => 'ABECS_PAX_D200_GPRS'
        );

        // populate all payments accoring to country
        $this->mercadopago = MercadoPagoPaymentPOSModuleFrontController::createMP();
        $result = $this->mercadopago->sendPaymentPoint($data);

        $response = array(
        'status' => $result['status'],
        'message' => $this->getMessageAndSave($result, $order->id)
        );

        return $response;
    }

    protected function getMessageAndSave($result, $id_order)
    {
        $message = "";
        switch ($result['status']) {
            case 201:
                $message = 'Payment created successfully, waiting payment.';
                $this->saveTransactionPOS($id_order, $result['response']['id']);
                break;
            case 400:
                $message = 'There is another payment attempt pending for that device.';
                break;
            case 403:
                $message = 'Invalid access_token, please use a production Access token.';
                break;
            default:
                $message = "";
                break;
        }
        return $message;
    }

    private function getIdTransactionPOS($id_order)
    {
        $sql = 'SELECT MAX(`id_transaction`) AS `id_transaction`
            FROM `'._DB_PREFIX_.'mercadopago_point_order`
            WHERE `id_order` = '.(int) $id_order;

        $result = Db::getInstance()->getRow($sql);
        return isset($result['id_transaction']) ? $result['id_transaction'] : false;
    }

    private function saveTransactionPOS($id_order, $id_transaction)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mercadopago_point_order` (`id_transaction`, `id_order`)
        VALUES (\'' . pSQL($id_transaction) . '\', \'' . (int) $id_order . '\')';

        if (! Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql)) {
            die(Tools::displayError('Error when save the id_transaction in database'));
        }
    }

    protected function redirectOrderDetail($orderId)
    {
        $getAdminLink = $this->context->link->getAdminLink('AdminOrders');
        $getViewOrder = $getAdminLink.'&vieworder&id_order='.$orderId;
        Tools::redirectAdmin($getViewOrder);
    }
}
