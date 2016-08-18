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
 *  @author    MERCADOPAGO.COM REPRESENTA&Ccedil;&Otilde;ES LTDA.
 *  @copyright Copyright (c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

include_once dirname(__FILE__) . '/../../mercadopago.php';
include_once dirname(__FILE__) . '/../../includes/MPApi.php';

class MercadoPagoNotificationModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();
        $this->displayAjax();
    }

    public function displayAjax()
    {

        if (Configuration::get('MERCADOPAGO_LOG') == 'true') {
             UtilMercadoPago::logMensagem('Debug Mode :: displayAjax - topic = ' . Tools::getValue('topic'), MPApi::INFO);
             UtilMercadoPago::logMensagem('Debug Mode :: displayAjax - id = ' . Tools::getValue('id'), MPApi::INFO);
             UtilMercadoPago::logMensagem('Debug Mode :: displayAjax - checkout = ' . Tools::getValue('checkout'), MPApi::INFO);
        }

        if (Tools::getValue('checkout') && Tools::getValue('data_id') || Tools::getValue('id')) {
            $mercadopago = $this->module;
            if (Tools::getValue('checkout') == "custom") {
                $mercadopago->listenIPN(
                    Tools::getValue('checkout'),
                    Tools::getValue('type'),
                    Tools::getValue('data_id')
                );
            } else {
                $mercadopago->listenIPN(Tools::getValue('checkout'), Tools::getValue('topic'), Tools::getValue('id'));
            }
        }
    }
}
