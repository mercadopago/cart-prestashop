{** * 2007-2015 PrestaShop * * NOTICE OF LICENSE * * This source file is
subject to the Open Software License (OSL 3.0) * that is bundled with
this package in the file LICENSE.txt. * It is also available through the
world-wide-web at this URL: * http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to *
obtain it through the world-wide-web, please send an email * to
license@prestashop.com so we can send you a copy immediately. * *
DISCLAIMER * * Do not edit or add to this file if you wish to upgrade
PrestaShop to newer * versions in the future. If you wish to customize
PrestaShop for your * needs please refer to http://www.prestashop.com
for more information. * * @author MercadoPago * @copyright Copyright
(c) MercadoPago [http://www.mercadopago.com] * @license
http://opensource.org/licenses/osl-3.0.php Open Software License (OSL
3.0) * International Registered Trademark & Property of MercadoPago *}
<html class="" data-ember-extension="1">
  <head>
    <link rel="stylesheet prefetch" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/css/credit_card_form.css" media="screen" />
  </head>

  <div class="mp-box-inputs mp-line" id="mercadopago-form-coupon">
  </div>
  <div id="mercadopago-form-customer-and-card">
  </div>

 <div class="mp-box-inputs mp-col-100 mp-issuer" style="visibility: hidden;">
    <label for="issuer"><?php echo $form_labels['form']['issuer']; ?> <em>*</em></label>
    <select id="issuer" data-checkout="issuer" name="mercadopago_custom[issuer]"></select>

    <span class="mp-error" id="mp-error-220" data-main="#issuer"> <?php echo $form_labels['error']['220']; ?> </span>
  </div>

  <div class="lightbox" id="text">
    <div class="box">
      <div class="content">
        <div class="processing">
          <span>{l s='Processing...' mod='mercadopago'}</span>
        </div>
      </div>

    </div>
  </div>

  <div class="CCBackground">
      <div class="outercontainer">
        <div class="card-wrapper"></div>
      </div>
      <div class="outercontainer_card">
        <div class="formcontainer" id="mercadopago-form">
          <div class="alert alert-danger fade in">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <strong>Error!</strong> A problem has been occurred while submitting your data.
          </div>
          <form action="" method="post" id="form-pagar-mp">

            <input id="opcaoPagamentoCreditCard" type="hidden" name="opcaoPagamentoCreditCard" value="" />
            <input id="amount" type="hidden" value="{$amount|escape:'htmlall':'UTF-8'}" />
            <input id="payment_method_id" type="hidden" name="payment_method_id" />
            <input id="payment_type_id" type="hidden" name="payment_type_id" />
            <input type="hidden" id="card_token_id" name="card_token_id"/>

            <div class="col-xs-12 form-group">
                <label for="cardNumber">Número do cartão: *</label>
                <input class="form-control" type="text" class="form-control" id="cardNumber" name="cardNumber" data-checkout="cardNumber">
            </div>

            <div class="col-xs-12 form-group">
                <label for="inputEmail">Email</label>
                <input type="email" class="form-control" id="inputEmail" placeholder="Email">
            </div>

            <div class="col-xs-12 form-group">
                <label for="cardholderName">Nome e sobrenome: *</label>
                <input class="form-control" type="text" class="form-control" id="cardholderName" name="cardholderName" data-checkout="cardholderName">
                <span class="mp-form__hint">Tal como está impreso en la tarjeta.</span>
            </div>

            <div class="col-xs-6 form-group">
                <label for="expiry">Data de Vencimento: *</label>
                <input class="form-control" placeholder="MM/YY" type="text" id="expiry" name="expiry" data-checkout="expiry"/>
            </div>

            <div class="col-xs-6 form-group">
                <label for="securityCode">Código de segurança: *</label>
                <input class="form-control" placeholder="CVC" type="text" id="securityCode" name="securityCode" data-checkout="securityCode" autocomplete="off"/>
            </div>

            <div class="col-xs-12 form-group">
                <label for="installments">Installments: *</label>
                <select class="mp-form__select" id="installments" class="form-control" placeholder="Installments"  data-checkout="installments" name="installments">
                  <option value="-1">::Selecione::</option>
                </select>
            </div>

            <div class="col-xs-6 form-group mp-docType">
                <label for="docType">Doc. Type: *</label>
                <input class="form-control" type="text" id="docType" placeholder="Doc. Type" data-checkout="docType" name="docType" autocomplete="off"/>
            </div>

            <div class="col-xs-12 form-group">
                <label for="docNumber">Document CPF: *</label>
                <input class="form-control" type="text" id="docNumber" data-checkout="docNumber" name="docNumber"/>
            </div>
            </br>

            <div class="col-xs-6 text-left">
              <img class="img-responsive" src="https://www.mercadopago.com/org-img/MLB/design/2015/m_pago/logos/mp_processado_02.png" alt="Mercado Pago">
            </div>
            </br>
            <div class="col-xs-6">
              <button class="btn btn-primary" value="{l s=' Confirm payment' mod='mercadopago'}" type="submit" id="btnSubmit">
                {l s=' Confirm payment' mod='mercadopago'}
              </button>
            </div>

            <div class="col-xs-12">
              <input type="hidden" id="amount" value="5249.99" name="amount"/>
              <input type="hidden" id="cardToken" value="" name="cardToken"/>
              <input type="hidden" id="discount" name="discount"/>
              <input type="hidden" id="cardExpirationMonth" value="" name="cardExpirationMonth"/>
              <input type="hidden" id="cardExpirationYear" value="" name="cardExpirationYear"/>
              <input type="hidden" id="site_id"  name="site_id"/>
              <input type="hidden" id="paymentMethodId" name="paymentMethodId"/>
              <input type="hidden" id="token" name="token"/>
              <input type="hidden" id="cardTruncated" name="cardTruncated"/>
              <input type="hidden" id="CustomerAndCard" name="CustomerAndCard"/>
            </div>

          </form>
        </div>
      </div>
  </div>

</html>
<!--<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>-->

<script type="text/javascript" src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script type="text/javascript" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/MPv1.js?no_cache=<?php echo time(); ?>"></script>

<script type="text/javascript">

  $('input[data-checkout=expiry]').change(function() {
    if (this.value.length == 9) {
      var month = this.value.split('/')[0].trim();
      var year = this.value.split('/')[1].trim();
      $('#cardExpirationMonth').val(month);
      $('#cardExpirationYear').val(year);
    }

  });
  MPv1.debug = false;
  MPv1.Initialize('MLB', "{$public_key|escape:'javascript':'UTF-8'}", false, '', '');

  function createModal() {
    $("body").append($(".lightbox"));
  }


  function disabledSubmit(disabled) {
    if (disabled) {
      $(".submit").attr("disabled", "true");
    } else {
      $(".submit").removeAttr("disabled");
    }

  }

  disabledSubmit(false);

  var submit = false;
  $("#form-pagar-mp")
      .submit(
          function(event) {
            console.info("entro no submit");
            disabledSubmit(true);
            event.preventDefault();
              var $form = $('#form-pagar-mp');
              var $cardDiv = $('#mercadopago-form');

              Mercadopago
              .createToken(
              $cardDiv,
              function(status, response) {
                if (response.error) {
                  disabledSubmit(false);
                  submit = false;
                  event.preventDefault();
                  $.each(response.cause, function(p,e) {
                    console.info(response);
                    console.info(e.code);
                    switch (e.code) {

                    }
                  });
                } else {
                  $(".lightbox").show();
                  submit = true;
                  var card_token_id = response.id;

                  var jsonPaymentMethod = getPaymentMethods();

                  document.getElementById("payment_method_id").value = jsonPaymentMethod.payment_method_id;
                  document.getElementById("payment_type_id").value = jsonPaymentMethod.payment_type_id;

                  $form
                      .append($(
                          '<input type="hidden" id="card_token_id" name="card_token_id"/>')
                          .val(
                              card_token_id));

                  var cardNumber = $("#id-card-number").val();

                  var lastFourDigits = cardNumber.substring(cardNumber.length - 4);
                  $form.append($('<input name="lastFourDigits" type="hidden" value="' + lastFourDigits + '"/>'));
                  document.getElementById("form-pagar-mp").action = "{$custom_action_url|escape:'quotes':'UTF-8'}";
                  document.getElementById("form-pagar-mp").submit();
                }

              });
        });

  var submit = false;



  createModal();

</script>
<script type="text/javascript" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/credit_card_form.js"></script>

