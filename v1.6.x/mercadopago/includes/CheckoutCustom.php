<?php
/**
 * 2007-2015 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License(OSL 3.0)
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
 *  @copyright Copyright(c) MercadoPago [http://www.mercadopago.com]
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License(OSL 3.0)
 *  International Registered Trademark & Property of MercadoPago
 */

if (!defined('_PS_VERSION_')) {
    exit();
}
class CheckoutCustom
{
    public function getFormLabels()
    {
        $form_labels = array(
          "form" => array(
            "label_number_cart" => "Number of card",
            "label_name_surname" => "Name and Surname",
            "label_alt_name_surname" => "Like is in your card",
            "label_expiration_date" => "Expiration Date",
            "label_security_code" => "Security Code",
            "label_installments" => "Installments",
            "label_choose" => "Choose",
            "label_cpf" => "CPF",
            "coupon_empty" => "Please, inform your coupon code",
            'apply' => "Apply",
            'remove' => "Remove",
            'discount_info1' => "You will save",
            'discount_info2' => "with discount from",
            'discount_info3' => "Total of your purchase:",
            'discount_info4' => "Total of your purchase with discount:",
            'discount_info5' => "*Uppon payment approval",
            'discount_info6' => "Terms and Conditions of Use",
            'coupon_of_discounts' => "Discount Coupon",
            'label_other_bank' => "Other Bank",
            'label_choose' => "Choose",
            "payment_method" => "Payment Method",
            "credit_card_number" => "Credit card number",
            "expiration_month" => "Expiration month",
            "expiration_year" => "Expiration year",
            "year" => "Year",
            "month" => "Month",
            "card_holder_name" => "Card holder name",
            "security_code" => "Security code",
            "document_type" => "Document Type",
            "document_number" => "Document number",
            "issuer" => "Issuer",
            "installments" => "Installments",
            "your_card" => "Your Card",
            "other_cards" => "Other Cards",
            "other_card" => "Other Card",
            "ended_in" => "ended in"
          ),
          "error" => array(

            //card number
            "205" => "Parameter cardNumber can not be null/empty",
            "E301" => "Invalid Card Number",
            //expiration date
            "208" => "Invalid Expiration Date",
            "209" => "Invalid Expiration Date",
            "325" => "Invalid Expiration Date",
            "326" => "Invalid Expiration Date",
            //card holder name
            "221" => "Parameter cardholderName can not be null/empty",
            "316" => "Invalid Card Holder Name",

            //security code
            "224" => "Parameter securityCode can not be null/empty",
            "E302" => "Invalid Security Code",
            "E203" => "Invalid Security Code",

            //doc type
            "212" => "Parameter docType can not be null/empty",
            "322" => "Invalid Document Type",
            //doc number
            "214" => "Parameter docNumber can not be null/empty",
            "324" => "Invalid Document Number",
            //doc sub type
            "213" => "The parameter cardholder.document.subtype can not be null or empty",
            "323" => "Invalid Document Sub Type",
            //issuer
            "220" => "Parameter cardIssuerId can not be null/empty",
          ),
          "coupon_error" => array(
            "EMPTY" => "Please, inform your coupon code"
          )
        );
        return $form_labels;
    }
}
