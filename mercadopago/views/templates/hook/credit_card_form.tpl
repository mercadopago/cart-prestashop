<html class="" data-ember-extension="1">
  <head>
    <link rel="stylesheet prefetch" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/css/credit_card_form.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/css/custom_checkout_mercadopago.css" media="screen" />
  </head>

  <div class="mp-box-inputs mp-line" id="mercadopago-form-coupon">
  </div>
  <div id="mercadopago-form-customer-and-card">
  </div>

 <div class="mp-box-inputs mp-col-100 mp-issuer" style="visibility: hidden;">
    <label for="issuer">{$form_labels.form.issuer|escape:'quotes':'UTF-8'}<em>*</em></label>
    <select id="issuer" data-checkout="issuer" name="mercadopago_custom[issuer]"></select>

    <span class="mp-error" id="mp-error-220" data-main="#issuer"> {$form_labels.error.220|escape:'quotes':'UTF-8'} </span>
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
<div class="mp-module">
  <div class="row">
    <div class="card col-xs-12 col-md-6">
      <div class="col-xs-12 col-md-6">
        <div class="outercontainer">
          <div class="card-wrapper"></div>
        </div>
        <form action="post.php" method="post" id="mercadopago-form-general" name="mercadopago-form-general">
          <div class="outercontainer_card">
          <!--action="{$custom_action_url|escape:'quotes':'UTF-8'}"-->
            <div class="formcontainer" id="mercadopago-form">
              <input id="opcaoPagamentoCreditCard" type="hidden" name="opcaoPagamentoCreditCard" value="" />
              <input id="payment_method_id" type="hidden" name="payment_method_id" />
              <input id="payment_type_id" type="hidden" name="payment_type_id" />
              <input type="hidden" id="card_token_id" name="card_token_id"/>

              <div class="row">
                <div class="col-xs-12 form-group">
                    <label for="cardNumber">{$form_labels.form.label_number_cart|escape:'quotes':'UTF-8'}: *</label>
                    <input class="form-control" type="text" class="form-control" id="cardNumber" name="cardNumber" data-checkout="cardNumber">
                    <span class="mp-error" id="mp-error-205" data-main="#cardNumber"> {$form_labels.error.205|escape:'quotes':'UTF-8'}  </span>
                    <span class="mp-error" id="mp-error-E301" data-main="#cardNumber"> {$form_labels.error.E301|escape:'quotes':'UTF-8'} </span>
                </div>
              </div>

              <div class="row">
                <div class="col-xs-12 form-group">
                    <label for="cardholderName">{$form_labels.form.label_name_surname|escape:'quotes':'UTF-8'}: *</label>
                    <input class="form-control" type="text" class="form-control" id="cardholderName" name="cardholderName" data-checkout="cardholderName">
                    <span class="mp-form__hint">{$form_labels.form.label_alt_name_surname|escape:'quotes':'UTF-8'}.</span>
                    <span class="mp-error" id="mp-error-221" data-main="#cardholderName"> {$form_labels.error.221|escape:'quotes':'UTF-8'}</span>
                    <span class="mp-error" id="mp-error-316" data-main="#cardholderName"> {$form_labels.error.316|escape:'quotes':'UTF-8'} </span>
                </div>
              </div>

              <div class="row">
                <div class="col-xs-4 form-group">
                    <label for="expiry">{$form_labels.form.label_expiration_date|escape:'quotes':'UTF-8'}: *</label>
                    <input class="form-control" placeholder="MM/YY" type="text" id="expiry" name="expiry" data-checkout="expiry"/>
                    <span class="mp-error" id="mp-error-208" data-main="#cardExpirationMonth"> {$form_labels.error.208|escape:'quotes':'UTF-8'} </span>
                    <span class="mp-error" id="mp-error-209" data-main="#cardExpirationYear"> </span>
                    <span class="mp-error" id="mp-error-325" data-main="#cardExpirationMonth"> {$form_labels.error.325|escape:'quotes':'UTF-8'} </span>
                    <span class="mp-error" id="mp-error-326" data-main="#cardExpirationYear"> </span>
                </div>
                <div class="col-xs-4 form-group">
                    <label for="securityCode">{$form_labels.form.label_security_code|escape:'quotes':'UTF-8'}: *</label>
                    <input class="form-control" placeholder="CVC" type="text" id="securityCode" name="securityCode" data-checkout="securityCode" autocomplete="off"/>
                    <span class="mp-error" id="mp-error-224" data-main="#securityCode"> {$form_labels.error.224|escape:'quotes':'UTF-8'} </span>
                    <span class="mp-error" id="mp-error-E302" data-main="#securityCode"> {$form_labels.error.E302|escape:'quotes':'UTF-8'} </span>
                </div>
              </div>

              <div class="row">
                <div class="col-xs-12 form-group">
                    <label for="installments">{$form_labels.form.label_installments|escape:'quotes':'UTF-8'}: *</label>
                    <select class="mp-form__select" id="installments" class="form-control" placeholder="Installments"  data-checkout="installments" name="mercadopago_custom[installments]">
                      <option value="-1">::{$form_labels.form.label_choose|escape:'quotes':'UTF-8'}::</option>
                    </select>
                </div>
                  <div class="mp-box-inputs mp-col-30" id="mp-box-input-tax-cft">
                    <div id="mp-tax-cft-text"></div>
                  </div>

                  <div class="mp-box-inputs mp-col-100" id="mp-box-input-tax-tea">
                    <div id="mp-tax-tea-text"></div>
                  </div>
              </div>

              <div class="col-xs-6 form-group mp-docType">
                <label for="docType">{$form_labels.form.document_type|escape:'quotes':'UTF-8'} <em>*</em></label>
                <select id="docType" data-checkout="docType" name="mercadopago_custom[docType]"></select>

                <span class="mp-error" id="mp-error-212" data-main="#docType"> {$form_labels.form.212|escape:'quotes':'UTF-8'} </span>
                <span class="mp-error" id="mp-error-322" data-main="#docType"> {$form_labels.form.322|escape:'quotes':'UTF-8'} </span>
              </div>
              <div class="row">
                <div class="col-xs-12 form-group">
                  <label for="docNumber">{$form_labels.form.label_cpf|escape:'quotes':'UTF-8'} <em>*</em></label>
                  <input type="text" class="form-control" id="docNumber" data-checkout="docNumber" name="mercadopago_custom[docNumber]" autocomplete="off"/>

                  <span class="mp-error" id="mp-error-214" data-main="#docNumber"> {$form_labels.form.214|escape:'quotes':'UTF-8'} </span>
                  <span class="mp-error" id="mp-error-324" data-main="#docNumber"> {$form_labels.form.324|escape:'quotes':'UTF-8'} </span>
                </div>
              </div>
              <!--<div class="row">
                <div class="col-xs-6 form-group mp-docType">
                    <label for="docType">Doc. Type: *</label>
                    <select id="docType" data-checkout="docType" name="mercadopago_custom[docType]"></select>
                    <span class="mp-error" id="mp-error-212" data-main="#docType"> <?php echo $form_labels['error']['212']; ?> </span>
                    <span class="mp-error" id="mp-error-322" data-main="#docType"> <?php echo $form_labels['error']['322']; ?> </span>
                </div>
              </div>

              <div class="row mp-docNumber">
                <div class="col-xs-12 form-group">
                    <label for="docNumber">Document CPF: *</label>
                    <input class="form-control" type="text" id="docNumber" data-checkout="docNumber" name="mercadopago_custom[docNumber]" autocomplete="off">
                    <span class="mp-error" id="mp-error-214" data-main="#docNumber"> <?php echo $form_labels['error']['214']; ?> </span>
                    <span class="mp-error" id="mp-error-324" data-main="#docNumber"> <?php echo $form_labels['error']['324']; ?> </span>
                </div>
              </div>-->

              <div class="row">
                <div class="col-xs-12 text-right">
                  <input type="submit" class="btn btn-primary" id="btnSubmit" value="Pay" name="btnSubmit">
                  <!-- NOT DELETE LOADING-->
                  <div class="mp-box-inputs mp-col-25">
                    <div id="mp-box-loading">
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-xs-12 text-left">
                  <img class="img-responsive" src="https://www.mercadopago.com/org-img/MLB/design/2015/m_pago/logos/mp_processado_02.png" alt="Mercado Pago">
                </div>
              </div>
            </div>
          </div>
          <div class="mp-box-inputs mp-col-100" id="mercadopago-utilities" style="visibility: hidden;">
            <input type="hidden" id="cardExpirationMonth" data-checkout="cardExpirationMonth"/>
            <input type="hidden" id="cardExpirationYear" data-checkout="cardExpirationYear"/>
            <input type="text" id="site_id"  name="mercadopago_custom[site_id]"/>
            <input type="text" id="amount" value="{$amount|escape:'htmlall':'UTF-8'}" name="mercadopago_custom[amount]"/>
            <input type="hidden" id="campaign_id" name="mercadopago_custom[campaign_id]"/>
            <input type="hidden" id="campaign" name="mercadopago_custom[campaign]"/>
            <input type="hidden" id="discount" name="mercadopago_custom[discount]"/>
            <input type="text" id="paymentMethodId" name="mercadopago_custom[paymentMethodId]"/>
            <input type="text" id="token" name="mercadopago_custom[token]"/>
            <input type="text" id="cardTruncated" name="mercadopago_custom[cardTruncated]"/>
            <input type="text" id="CustomerAndCard" name="mercadopago_custom[CustomerAndCard]"/>
          </div>
        </form>
      </div>
    </div>
    <div class="col-xs-12 col-md-6">
    {if $standard_active eq 'true' &&
    $preferences_url != null}
      <div class="row">
        {if $window_type != 'iframe'} <a
          href="{$preferences_url|escape:'htmlall':'UTF-8'}" id="id-standard"
          mp-mode="{$window_type|escape:'htmlall':'UTF-8'}" name="MP-Checkout">
          <div class="mp-form hover">
            <div class="row">
              <div class="col">
                <img
                  src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo_120_31.png"
                  id="id-standard-logo"> <img
                  src="{$standard_banner|escape:'htmlall':'UTF-8'}"
                  class="mp-standard-banner" /> <span
                  class="payment-label standard">{$custom_text|escape:'htmlall':'UTF-8'}</span>
              </div>
            </div>
          </div>
        </a> {else}
        <div class="mp-form">
          <iframe src="{$preferences_url|escape:'htmlall':'UTF-8'}" name="MP-Checkout"
            width="{$iframe_width|escape:'htmlall':'UTF-8'}"
            height="{$iframe_height|escape:'htmlall':'UTF-8'}" frameborder="0">
          </iframe>
        </div>
        {/if}
      </div>
    {/if}
    {if $country == 'MLB' || $country == 'MLM' || $country == 'MPE' || $country ==
    'MLA' || $country == 'MLC' || $country == 'MCO' || $country == 'MLV'}
    {foreach from=$offline_payment_settings key=offline_payment item=value}
    {if $value.active == "true" && $mercadoenvios_activate == 'false'}
    
      <div class="row">
        <a href="javascript:void(0);"
          id="id-{$offline_payment|escape:'htmlall':'UTF-8'}" class="offline-payment">
          <div class="mp-form-boleto">
            <div class="row boleto">
              <div class="col">
                <img src="{$value.thumbnail|escape:'htmlall':'UTF-8'}">

                <span class="payment-label">{$value.name|upper|escape:'htmlall':'UTF-8'} </span><br> <span
                  class="poweredby">{l s='Powered by' mod='mercadopago'}</span>
                  <img
                  class="logo"
                  src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/img/payment_method_logo.png">
              </div>
              <form action="{$custom_action_url|escape:'htmlall':'UTF-8'}" method="post"
                id="form-{$offline_payment|escape:'htmlall':'UTF-8'}" class="formTicket">

                <input name="mercadopago_coupon" type="hidden"
                  class="mercadopago_coupon_ticket" /> <input
                  name="payment_method_id" type="hidden"
                  value="{$offline_payment|escape:'htmlall':'UTF-8'}" /> <input
                  type="submit" class="create-boleto"
                  id="id-create-{$offline_payment|escape:'htmlall':'UTF-8'}">

              </form>
            </div>
          </div>
        </a>
      </div>
    {/if}
    {/foreach}
    {/if}
    </div>
  </div>
</div>

</html>
<!--<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>-->

<script type="text/javascript" src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script type="text/javascript" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/MPv1.js?no_cache={$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"></script>

<script type="text/javascript">

  $('input[data-checkout=expiry]').change(function() {
    if (this.value.length == 9) {
      var month = this.value.split('/')[0].trim();
      var year = this.value.split('/')[1].trim();
      $('#cardExpirationMonth').val(month);
      $('#cardExpirationYear').val(year);
    }

  });

  var mercadopago_site_id = "{$site_id|escape:'javascript':'UTF-8'}";
  var mercadopago_public_key = "{$public_key|escape:'javascript':'UTF-8'}";
  var mercadopago_payer_email = "{$payer_email|escape:'javascript':'UTF-8'}";

  MPv1.debug = false;
  MPv1.removeIconCard = true;

  MPv1.selectors.form = "#mercadopago-form-general"
  MPv1.create_token_on.event = false;
  MPv1.Initialize(mercadopago_site_id, mercadopago_public_key, false, '', mercadopago_payer_email);

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

  createModal();

</script>
<script type="text/javascript" src="{$this_path_ssl|escape:'htmlall':'UTF-8'}modules/mercadopago/views/js/credit_card_form.js"></script>

