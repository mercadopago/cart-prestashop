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
 *  @author    MercadoPago
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

include_once dirname(__FILE__).'/../../mercadopago.php';
include_once dirname(__FILE__).'/../../includes/MPApi.php';
class MercadoPagoStandardReturnModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (Tools::getIsset('collection_id') && Tools::getValue('collection_id') != 'null') {
            PrestaShopLogger::addLog(
                'MercadoPago :: standard - topic = '.Tools::getValue('collection_id'),
                MPApi::INFO,
                0
            );
            // payment variables
            $payment_statuses = array();
            $payment_ids = array();
            $payment_types = array();
            $payment_method_ids = array();
            $card_holder_names = array();
            $four_digits_arr = array();
            $statement_descriptors = array();
            $status_details = array();
            $transaction_amounts = 0;
            $collection_ids = explode(',', Tools::getValue('collection_id'));

            $merchant_order_id = Tools::getValue('merchant_order_id');

            $mercadopago = $this->module;
            $mercadopago_sdk = $mercadopago->mercadopago;

            foreach ($collection_ids as $collection_id) {
                PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::entrou no for===", MPApi::INFO, 0);
                $result = $mercadopago_sdk->getPaymentStandard($collection_id);
                error_log(print_r($result, true));
                $payment_info = $result['response']['collection'];
                PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::for==external_reference===".$payment_info['external_reference'], MPApi::INFO, 0);
                $id_cart = $payment_info['external_reference'];
                $cart = new Cart($id_cart);
                $payment_statuses[] = $payment_info['status'];
                $payment_ids[] = $payment_info['id'];
                $payment_types[] = $payment_info['payment_type'];

                if (isset($payment_info['payment_method_id'])) {
                    $payment_method_ids[] = $payment_info['payment_method_id'];
                }

                $transaction_amounts += $payment_info['transaction_amount'];

                if (isset($payment_info['payment_type']) &&
                    $payment_info['payment_type'] == 'credit_card' ||
                    $payment_info['payment_type'] == 'account_money'
                    ) {
                    $card_holder_names[] = isset($payment_info['card']['cardholder']['name'])
                    ? $payment_info['card']['cardholder']['name'] : '';
                    if (isset($payment_info['card']['last_four_digits'])) {
                        $four_digits_arr[] = '**** **** **** '.$payment_info['card']['last_four_digits'];
                    }
                    $statement_descriptors[] = isset($payment_info['statement_descriptor']) ?
                    $payment_info['statement_descriptor'] : ''  ;
                    $status_details[] = $payment_info['status_detail'];
                }
            }

            if (Validate::isLoadedObject($cart)) {
                if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO' ||
                    Configuration::get('MERCADOPAGO_COUNTRY') == 'MLC') {
                    $total = (double) round($transaction_amounts);
                } else {
                    $total = (double) number_format($transaction_amounts, 2, '.', '');
                }
                $extra_vars = array(
                    '{bankwire_owner}' => $mercadopago->textshowemail,
                    '{bankwire_details}' => '',
                    '{bankwire_address}' => '',
                );
                $order_status = null;
                $payment_status = $payment_info['status'];
                switch ($payment_status) {
                    case 'in_process':
                        $order_status = 'MERCADOPAGO_STATUS_0';
                        break;
                    case 'approved':
                        $order_status = 'MERCADOPAGO_STATUS_1';
                        break;
                    case 'pending':
                        $order_status = 'MERCADOPAGO_STATUS_7';
                        break;
                }
                if ($order_status != null) {
                    $result_merchant = $mercadopago_sdk->getMerchantOrder($merchant_order_id);
                    $merchant_order_info = $result_merchant['response'];

                    if (isset($merchant_order_info['shipments'][0]) &&
                        $merchant_order_info['shipments'][0]['shipping_mode'] == 'me2') {
                        $cost_mercadoEnvios = $merchant_order_info['shipments'][0]['shipping_option']['cost'];

                        $total += $cost_mercadoEnvios;
                    }

                    if (Configuration::get('MERCADOPAGO_COUNTRY') == 'MCO' ||
                        Configuration::get('MERCADOPAGO_COUNTRY') == 'MLC') {
                        $total = $cart->getOrderTotal(true, Cart::BOTH);
                    }
                    $existOrderMercadoPago = $mercadopago->selectMercadoPagoOrder($id_cart);
                    PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::existOrderMercadoPago=====".$existOrderMercadoPago, MPApi::INFO, 0);
                    PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::OrderExists=====".$cart->OrderExists(), MPApi::INFO, 0);

                    $status = new OrderState((int)Configuration::get($order_status), (int)$this->context->language->id);
                    if (!Validate::isLoadedObject($status)) {
                        PrestaShopLogger::addLog("====ERRRORRR 1=====", MPApi::INFO, 0);
                        PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::ERROR order_status====", MPApi::INFO, 0);
                    } else {
                        PrestaShopLogger::addLog("====carregou o status 1=====", MPApi::INFO, 0);
                    }
                    PrestaShopLogger::addLog("====1=====", MPApi::INFO, 0);
                    if (!$existOrderMercadoPago) {
                        $mercadopago->insertMercadoPagoOrder($id_cart, 0, 0, $payment_status);
                        PrestaShopLogger::addLog("====insert =====".$id_cart, MPApi::INFO, 0);
                        PrestaShopLogger::addLog("====3=====", MPApi::INFO, 0);
                        UtilMercadoPago::logMensagem(
                            '=====MercadoPagoStandardReturnModuleFrontController::status===='.Configuration::get($order_status),
                            MPApi::INFO
                        );

                       UtilMercadoPago::logMensagem(
                            '=====MercadoPagoStandardReturnModuleFrontController::language===='.(int)$this->context->language->id,
                            MPApi::INFO
                        );

                        try {
                            $displayName = $mercadopago->setNamePaymentType($payment_types[0]);
                            $customer = new Customer($cart->id_customer);
                            $currentOrder = $mercadopago->validateOrder(
                                $cart->id,
                                Configuration::get($order_status),
                                $total,
                                $displayName,
                                null,
                                $extra_vars,
                                $cart->id_currency,
                                false,
                                $customer->secure_key
                            );
                            error_log("====standard validateOrder::currentOrder===".$currentOrder);
                            $mercadopago->insertMercadoPagoOrder($id_cart, $currentOrder, 1, $payment_status);
                            UtilMercadoPago::logMensagem(
                            '=====MercadoPagoStandardReturnModuleFrontController::validateOrder==currentOrder===='.$currentOrder,
                            MPApi::INFO
                        );
                            PrestaShopLogger::addLog("====4=====", MPApi::INFO, 0);
                        } catch (Exception $e) {
                            PrestaShopLogger::addLog("====ERRRORRR 6=====", MPApi::INFO, 0);
                            PrestaShopLogger::addLog("=====MERCADOPAGO_ERRO::validateOrder=====".$e->getMessage(), MPApi::ERROR, 0);
                        }
                    }
                    $order_id = $mercadopago->getOrderByCartId($cart->id);

                    PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::cart_id=====".$cart->id, MPApi::INFO, 0);
                    PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::order_id=====".$order_id, MPApi::INFO, 0);

                    $order = new Order($order_id);

                    $uri = __PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.
                         $mercadopago->id.'&id_order='.$order->id.'&key='.$order->secure_key;

                    $uri .= '&payment_status='.$payment_statuses[0];
                    $uri .= '&payment_id='.implode(' / ', $payment_ids);
                    $uri .= '&payment_type='.implode(' / ', $payment_types);
                    $uri .= '&payment_method_id='.implode(' / ', $payment_method_ids);
                    $uri .= '&amount='.$total;

                    if ($payment_info['payment_type'] == 'credit_card' ||
                        $payment_info['payment_type'] == 'account_money') {
                        $uri .= '&card_holder_name='.implode(' / ', $card_holder_names);
                        $uri .= '&four_digits='.implode(' / ', $four_digits_arr);
                        $uri .= '&statement_descriptor='.$statement_descriptors[0];
                        $uri .= '&status_detail='.$status_details[0];
                    }

                    $order_payments = $order->getOrderPayments();
                    if ($order_payments != null) {
                        $order_payments[0]->transaction_id = Tools::getValue('collection_id');
                        if ($payment_info['payment_type'] == 'credit_card' ||
                            $payment_info['payment_type'] == 'account_money') {
                            $uri .= '&card_holder_name='.implode(' / ', $card_holder_names);
                            $uri .= '&four_digits='.implode(' / ', $four_digits_arr);
                            $uri .= '&statement_descriptor='.$statement_descriptors[0];
                            $uri .= '&status_detail='.$status_details[0];

                            $order_payments[0]->transaction_id = implode(' / ', $payment_ids);
                            $order_payments[0]->card_number = empty($four_digits_arr) ? '' :
                                implode(' / ', $four_digits_arr);
                            $order_payments[0]->card_brand = empty($payment_method_ids) ? '' :
                                implode(' / ', $payment_method_ids);
                            $order_payments[0]->card_holder = implode(' / ', $card_holder_names);
                        }
                        $returnSave = $order_payments[0]->save();
                        PrestaShopLogger::addLog("=====MercadoPagoStandardReturnModuleFrontController::save=====".$returnSave, MPApi::INFO, 0);
                    } else {
                        PrestaShopLogger::addLog("=====MERCADOPAGO_ERRO::order_payments é nulo da order =====".$order->id, MPApi::ERROR, 0);
                    }

                    Tools::redirectLink($uri);
                }
            }
        } else {
            UtilMercadoPago::logMensagem(
                'MercadoPagoStandardReturnModuleFrontController::initContent = '.
                'External reference is not set. Order placement has failed.',
                MPApi::ERROR
            );
        }
    }
}
