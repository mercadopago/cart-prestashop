/**
*  @author    Mercado pago <modulos@mercadolivre.com>
*  @copyright modulos 2017
*  @license   GNU General Public License version 2
*  @version   1.1

*  www.mercadopago.com.br
*
* Languages: EN, PT
* PS version: 1.6
*
**/
(function (){

  var MPv1 = {
    debug: false,
    add_truncated_card: true,
    site_id: '',
    public_key: '',
    removeIconCard: '',
    coupon_of_discounts: {
      discount_action_url: '',
      payer_email: '',
      default: true,
      status: false
    },
    customer_and_card: {
      default: true,
      status: true
    },
    create_token_on: {
      event: true, //if true create token on event, if false create on click and ignore others events. eg: paste or keyup
      keyup: false,
      paste: true,
    },

    inputs_to_create_discount: [
      "couponCode",
      "applyCoupon"
    ],

    inputs_to_create_token: [
      "cardNumber",
      "cardExpirationMonth",
      "cardExpirationYear",
      "cardholderName",
      "securityCode",
      "docType",
      "docNumber"
    ],

    inputs_to_create_token_customer_and_card: [
      "paymentMethodSelector",
      "securityCode"
    ],

    selectors:{

      couponCode: "#couponCode",
      applyCoupon: "#applyCoupon",
      mpCouponApplyed: "#mpCouponApplyed",
      mpCouponError: "#mpCouponError",

      paymentMethodSelector: "#paymentMethodSelector",
      pmCustomerAndCards: "#payment-methods-for-customer-and-cards",
      pmListOtherCards: "#payment-methods-list-other-cards",
      mpSecurityCodeCustomerAndCard: "#mp-securityCode-customer-and-card",

      cardNumber: "#cardNumber",
      cardExpirationMonth: "#cardExpirationMonth",
      cardExpirationYear: "#cardExpirationYear",
      cardholderName: "#cardholderName",
      securityCode: "#securityCode",
      docType: "#docType",
      docNumber: "#docNumber",
      issuer: "#issuer",
      installments: "#installments",

      mpDoc: ".mp-doc",
      mpIssuer: ".mp-issuer",
      mpDocType: ".mp-docType",
      mpDocNumber: ".mp-docNumber",
      // mpPaymentMethodSelector: ".mp-paymentMethodsSelector",

      paymentMethodId: "#paymentMethodId",
      amount: "#amount",
      token: "#token",
      campaign_id: "#campaign_id",
      campaign: "#campaign",
      discount: "#discount",
      cardTruncated: "#cardTruncated",
      site_id: "#site_id",
      CustomerAndCard: '#CustomerAndCard',

      boxInstallments: '#mp-box-installments',
      boxInstallmentsSelector: '#mp-box-installments-selector',
      taxCFT: '#mp-box-input-tax-cft',
      taxTEA: '#mp-box-input-tax-tea',
      taxTextCFT: '#mp-tax-cft-text',
      taxTextTEA: '#mp-tax-tea-text',

      box_loading: "#mp-box-loading",
      submit: "#btnSubmit",
      form: '#mercadopago-form',
      formDiv: '#mercadopago-form',
      formCoupon: '#mercadopago-form-coupon',
      formCustomerAndCard: '#mercadopago-form-customer-and-card',
      utilities_fields: "#mercadopago-utilities"
    },
    text: {
      choose: "Choose",
      other_bank: "Other Bank",
      discount_info1: "You will save",
      discount_info2: "with discount from",
      discount_info3: "Total of your purchase:",
      discount_info4: "Total of your purchase with discount:",
      discount_info5: "*Uppon payment approval",
      discount_info6: "Terms and Conditions of Use",
      coupon_empty: "Please, inform your coupon code",
      apply: "Apply",
      remove: "Remove"
    },
    paths:{
      loading: "images/loading.gif",
      check: "images/check.png",
      error: "images/error.png"
    }
  }

  MPv1.currencyIdToCurrency = function (currency_id) {
    if ( currency_id == 'ARS' ) {
      return '$';
    } else if ( currency_id == 'BRL' ) {
      return 'R$';
    } else if ( currency_id == 'COP' ) {
      return '$';
    } else if ( currency_id == 'CLP' ) {
      return '$';
    } else if ( currency_id == 'MXN' ) {
      return '$';
    } else if ( currency_id == 'VEF' ) {
      return 'Bs';
    } else if ( currency_id == 'PEN' ) {
      return 'S/';
    } else {
      return '$';
    }
  }

  MPv1.checkCouponEligibility = function () {
    if ( document.querySelector(MPv1.selectors.couponCode).value == "" ) {
      // coupon code is empty
      document.querySelector(MPv1.selectors.mpCouponApplyed).style.display = 'none';
      document.querySelector(MPv1.selectors.mpCouponError).style.display = 'block';
      document.querySelector(MPv1.selectors.mpCouponError).innerHTML = MPv1.text.coupon_empty;
      MPv1.coupon_of_discounts.status = false;
      document.querySelector(MPv1.selectors.couponCode).style.background = null;
      document.querySelector(MPv1.selectors.applyCoupon).value = MPv1.text.apply;
      document.querySelector(MPv1.selectors.discount).value = 0;
      MPv1.cardsHandler();
    } else if ( MPv1.coupon_of_discounts.status ) {
      // we already have a coupon set, so we remove it
      document.querySelector(MPv1.selectors.mpCouponApplyed).style.display = 'none';
      document.querySelector(MPv1.selectors.mpCouponError).style.display = 'none';
      MPv1.coupon_of_discounts.status = false;
      document.querySelector(MPv1.selectors.applyCoupon).style.background = null;
      document.querySelector(MPv1.selectors.applyCoupon).value = MPv1.text.apply;
      document.querySelector(MPv1.selectors.couponCode).value = "";
      document.querySelector(MPv1.selectors.couponCode).style.background = null;
      document.querySelector(MPv1.selectors.discount).value = 0;
      MPv1.cardsHandler();
    } else {
      // set loading
      document.querySelector(MPv1.selectors.mpCouponApplyed).style.display = 'none';
      document.querySelector(MPv1.selectors.mpCouponError).style.display = 'none';
      document.querySelector(MPv1.selectors.couponCode).style.background = "url("+MPv1.paths.loading+") 98% 50% no-repeat #fff";
      document.querySelector(MPv1.selectors.applyCoupon).disabled = true;

      var url = MPv1.coupon_of_discounts.discount_action_url
      var sp = "?";

      //check if there are params in the url
      if (url.indexOf("?") >= 0){
        sp = "&"
      }

      console.info("amount == === == = =" + document.querySelector(MPv1.selectors.amount).value);

      url += sp + "site_id=" + MPv1.site_id
      url += "&coupon_id=" + document.querySelector(MPv1.selectors.couponCode).value
      url += "&amount=" + document.querySelector(MPv1.selectors.amount).value
      url += "&payer=" + MPv1.coupon_of_discounts.payer_email

      MPv1.AJAX({
        url: url,
        method : "GET",
        timeout : 5000,
        error: function(){
          // request failed
          document.querySelector(MPv1.selectors.mpCouponApplyed).style.display = 'none';
          document.querySelector(MPv1.selectors.mpCouponError).style.display = 'none';
          MPv1.coupon_of_discounts.status = false;
          document.querySelector(MPv1.selectors.applyCoupon).style.background = null;
          document.querySelector(MPv1.selectors.applyCoupon).value = MPv1.text.apply;
          document.querySelector(MPv1.selectors.couponCode).value = "";
          document.querySelector(MPv1.selectors.couponCode).style.background = null;
          document.querySelector(MPv1.selectors.discount).value = 0;
          MPv1.cardsHandler();
        },
        success : function (status, response){

          if (response.status == 200) {
            document.querySelector(MPv1.selectors.mpCouponApplyed).style.display = 'block';
            document.querySelector(MPv1.selectors.discount).value = response.response.coupon_amount;
            document.querySelector(MPv1.selectors.mpCouponApplyed).innerHTML =
              MPv1.text.discount_info1 + " <strong>" + MPv1.currencyIdToCurrency(response.response.currency_id) + " " +
              Math.round(response.response.coupon_amount*100)/100 + "</strong> " + MPv1.text.discount_info2 + " " + response.response.name + ".<br>" +
              MPv1.text.discount_info3 + " <strong>" + MPv1.currencyIdToCurrency(response.response.currency_id) +
              " " + Math.round(MPv1.getAmountWithoutDiscount()*100)/100 + "</strong><br>" +
              MPv1.text.discount_info4 + " <strong>" + MPv1.currencyIdToCurrency(response.response.currency_id) +
              " " + Math.round(MPv1.getAmount()*100)/100 + "*</strong><br>" +
              "<i>" + MPv1.text.discount_info5 + "</i><br>" +
              "<a href='https://api.mercadolibre.com/campaigns/" + response.response.id + "/terms_and_conditions?format_type=html' target='_blank'>" +
              MPv1.text.discount_info6 + "</a>";
            document.querySelector(MPv1.selectors.mpCouponError).style.display = 'none';
            MPv1.coupon_of_discounts.status = true;
            document.querySelector(MPv1.selectors.couponCode).style.background = null;
            document.querySelector(MPv1.selectors.couponCode).style.background = "url("+MPv1.paths.check+") 98% 50% no-repeat #fff";
            document.querySelector(MPv1.selectors.applyCoupon).value = MPv1.text.remove;
            MPv1.cardsHandler();
            document.querySelector(MPv1.selectors.campaign_id).value = response.response.id;
            document.querySelector(MPv1.selectors.campaign).value = response.response.name;
          } else if (response.status == 400 || response.status == 404) {
            document.querySelector(MPv1.selectors.mpCouponApplyed).style.display = 'none';
            document.querySelector(MPv1.selectors.mpCouponError).style.display = 'block';
            document.querySelector(MPv1.selectors.mpCouponError).innerHTML = response.response.message;
            MPv1.coupon_of_discounts.status = false;
            document.querySelector(MPv1.selectors.couponCode).style.background = null;
            document.querySelector(MPv1.selectors.couponCode).style.background = "url("+MPv1.paths.error+") 98% 50% no-repeat #fff";
            document.querySelector(MPv1.selectors.applyCoupon).value = MPv1.text.apply;
            document.querySelector(MPv1.selectors.discount).value = 0;
            MPv1.cardsHandler();
          }

          document.querySelector(MPv1.selectors.applyCoupon).disabled = false;

        }
      });

    }
  }


  MPv1.getBin = function () {
    var cardSelector = document.querySelector(MPv1.selectors.paymentMethodSelector);
    if (cardSelector && cardSelector[cardSelector.options.selectedIndex].value != "-1") {
      return cardSelector[cardSelector.options.selectedIndex].getAttribute('first_six_digits');
    }

    var ccNumber = document.querySelector(MPv1.selectors.cardNumber);
    return ccNumber.value.replace(/[ .-]/g, '').slice(0, 6);
  }

  MPv1.clearOptions = function () {
    var bin = MPv1.getBin();

    if (bin.length == 0) {
      MPv1.hideIssuer();

      var selectorInstallments = document.querySelector(MPv1.selectors.installments),
      fragment = document.createDocumentFragment(),
      option = new Option(MPv1.text.choose + "...", '-1');

      selectorInstallments.options.length = 0;
      fragment.appendChild(option);
      selectorInstallments.appendChild(fragment);
      selectorInstallments.setAttribute('disabled', 'disabled');
    }
  }

  MPv1.guessingPaymentMethod = function (event) {

    var bin = MPv1.getBin();
    var amount = MPv1.getAmount();

    if (event.type == "keyup") {
      if (bin != null && bin.length == 6 ) {
        Mercadopago.getPaymentMethod({
          "bin": bin
        }, MPv1.setPaymentMethodInfo);
      }
    } else {
      setTimeout(function() {
        if (bin.length >= 6) {
          Mercadopago.getPaymentMethod({
            "bin": bin
          }, MPv1.setPaymentMethodInfo);
        }
      }, 100);
    }
  };

  MPv1.setPaymentMethodInfo = function (status, response) {

    if (status == 200) {

      if(MPv1.site_id != "MLM"){
        //guessing
        document.querySelector(MPv1.selectors.paymentMethodId).value = response[0].id;

        if (! MPv1.removeIconCard) {
          if(MPv1.customer_and_card.status){
            document.querySelector(MPv1.selectors.paymentMethodSelector).style.background = "url(" + response[0].secure_thumbnail + ") 95% 50% no-repeat #fff";
          }else{
            document.querySelector(MPv1.selectors.cardNumber).style.background = "url(" + response[0].secure_thumbnail + ") 98% 50% no-repeat #fff";
          }
        }

      }

      // check if the security code (ex: Tarshop) is required
      var cardConfiguration = response[0].settings;
      var bin = MPv1.getBin();
      var amount = MPv1.getAmount();

      Mercadopago.getInstallments({
        "bin": bin,
        "amount": amount
      }, MPv1.setInstallmentInfo);

      // check if the issuer is necessary to pay
      var issuerMandatory = false,
      additionalInfo = response[0].additional_info_needed;

      for (var i = 0; i < additionalInfo.length; i++) {
        if (additionalInfo[i] == "issuer_id") {
          issuerMandatory = true;
        }
      };
      if (issuerMandatory && MPv1.site_id != "MLM") {
        var payment_method_id = response[0].id;
        MPv1.getIssuersPaymentMethod(payment_method_id);
      } else {
        MPv1.hideIssuer();
      }
    }
  }


  MPv1.changePaymetMethodSelector = function (){
    var payment_method_id = document.querySelector(MPv1.selectors.paymentMethodSelector).value;
    MPv1.getIssuersPaymentMethod(payment_method_id);

  }


  /*
  *
  *
  * Issuers
  *
  */

  MPv1.getIssuersPaymentMethod = function (payment_method_id){
    var amount = MPv1.getAmount();

    //flow: MLM mercadopagocard
    if(payment_method_id == 'mercadopagocard'){
      Mercadopago.getInstallments({
        "payment_method_id": payment_method_id,
        "amount": amount
      }, MPv1.setInstallmentInfo);
    }

    Mercadopago.getIssuers(payment_method_id, MPv1.showCardIssuers);
    MPv1.addListenerEvent(document.querySelector(MPv1.selectors.issuer), 'change', MPv1.setInstallmentsByIssuerId);
  }


  MPv1.showCardIssuers = function (status, issuers) {

    //if the API does not return any bank
    if(issuers.length > 0){
      var issuersSelector = document.querySelector(MPv1.selectors.issuer),
      fragment = document.createDocumentFragment();

      issuersSelector.options.length = 0;
      var option = new Option(MPv1.text.choose + "...", '-1');
      fragment.appendChild(option);

      for (var i = 0; i < issuers.length; i++) {
        if (issuers[i].name != "default") {
          option = new Option(issuers[i].name, issuers[i].id);
        } else {
          option = new Option("Otro", issuers[i].id);
        }
        fragment.appendChild(option);
      }
      issuersSelector.appendChild(fragment);
      issuersSelector.removeAttribute('disabled');
      //document.querySelector(MPv1.selectors.issuer).removeAttribute('style');
    }else{
      MPv1.hideIssuer();
    }
  }

  MPv1.setInstallmentsByIssuerId = function (status, response) {
    var issuerId = document.querySelector(MPv1.selectors.issuer).value;
    var amount = MPv1.getAmount();

    if (issuerId === '-1') {
      return;
    }

    var params_installments = {
      "bin": MPv1.getBin(),
      "amount": amount,
      "issuer_id": issuerId
    }

    if(MPv1.site_id == "MLM"){
      params_installments = {
        "payment_method_id": document.querySelector(MPv1.selectors.paymentMethodSelector).value,
        "amount": amount,
        "issuer_id": issuerId
      }
    }

    Mercadopago.getInstallments(params_installments, MPv1.setInstallmentInfo);
  }

  MPv1.hideIssuer = function (){
    var $issuer = document.querySelector(MPv1.selectors.issuer);
    var opt = document.createElement('option');
    opt.value = "-1";
    opt.innerHTML = MPv1.text.other_bank;

    $issuer.innerHTML = "";
    $issuer.appendChild(opt);
    $issuer.setAttribute('disabled', 'disabled');
  }

  /*
  *
  *
  * Installments
  *
  */

  MPv1.setInstallmentInfo = function(status, response) {
    var selectorInstallments = document.querySelector(MPv1.selectors.installments);

    if (response.length > 0) {

      var html_option = '<option value="-1">' + MPv1.text.choose + '...</option>';
      payerCosts = response[0].payer_costs;

      // fragment.appendChild(option);
      for (var i = 0; i < payerCosts.length; i++) {

        // Resolution 51/2017
        var dataInput = "";
        if(MPv1.site_id == 'MLA'){
          var tax = payerCosts[i].labels;
          if(tax.length > 0){
            for (var l = 0; l < tax.length; l++) {
              if (tax[l].indexOf('CFT_') !== -1){
                dataInput = 'data-tax="' + tax[l] + '"'
              }
            }
          }
        }

        html_option += '<option value="'+ payerCosts[i].installments +'" '+ dataInput +'>' + (payerCosts[i].recommended_message || payerCosts[i].installments) + '</option>';
      }

      // not take the user's selection if equal
      if(selectorInstallments.innerHTML != html_option){
        selectorInstallments.innerHTML = html_option;
      }

      selectorInstallments.removeAttribute('disabled');

      MPv1.showTaxes();
    }
  }


  /*
  *
  *
  * Customer & Cards
  *
  */

  MPv1.cardsHandler = function () {

    var cardSelector = document.querySelector(MPv1.selectors.paymentMethodSelector);
    var type_checkout = cardSelector[cardSelector.options.selectedIndex].getAttribute("type_checkout");
    var amount = MPv1.getAmount();


    if(MPv1.customer_and_card.default){

      if (cardSelector &&
          cardSelector[cardSelector.options.selectedIndex].value != "-1" &&
          type_checkout == "customer_and_card") {

          document.querySelector(MPv1.selectors.paymentMethodId).value = cardSelector[cardSelector.options.selectedIndex].getAttribute('payment_method_id');

          MPv1.clearOptions();

          MPv1.customer_and_card.status = true;

          var _bin = cardSelector[cardSelector.options.selectedIndex].getAttribute("first_six_digits");

          Mercadopago.getPaymentMethod({
            "bin": _bin
          }, MPv1.setPaymentMethodInfo);

        }else{
          document.querySelector(MPv1.selectors.paymentMethodId).value = cardSelector.value != -1 ? cardSelector.value : "";
          MPv1.customer_and_card.status = false;
          MPv1.resetBackgroundCard();
          MPv1.guessingPaymentMethod({type: "keyup"});
        }

        MPv1.setForm();
      }
    }

    /*
    * Payment Methods
    *
    */

    MPv1.getPaymentMethods = function(){
      var fragment = document.createDocumentFragment();
      var paymentMethodsSelector = document.querySelector(MPv1.selectors.paymentMethodSelector)
      var mainPaymentMethodSelector = document.querySelector(MPv1.selectors.paymentMethodSelector)

      //set loading
      mainPaymentMethodSelector.style.background = "url("+MPv1.paths.loading+") 95% 50% no-repeat #fff";

      //if customer and card
      if(MPv1.customer_and_card.status){
        paymentMethodsSelector = document.querySelector(MPv1.selectors.pmListOtherCards)

        //clean payment methods
        paymentMethodsSelector.innerHTML = "";
      }else{
        paymentMethodsSelector.innerHTML = "";
        option = new Option(MPv1.text.choose + "...", '-1');
        fragment.appendChild(option);
      }

      Mercadopago.getAllPaymentMethods(function(code, payment_methods){

        for(var x=0; x < payment_methods.length; x++){
          var pm = payment_methods[x];

          if((pm.payment_type_id == "credit_card" ||
          pm.payment_type_id == "debit_card" ||
          pm.payment_type_id == "prepaid_card") &&
          pm.status == "active"){

            option = new Option(pm.name, pm.id);
            option.setAttribute("type_checkout", "custom");
            fragment.appendChild(option);

          }//end if

        } //end for

        paymentMethodsSelector.appendChild(fragment);
        mainPaymentMethodSelector.style.background = "#fff";
      });
    }

    /*
    *
    * Functions related to Create Tokens
    *
    */


    MPv1.createTokenByEvent = function(){

      var $inputs = MPv1.getForm().querySelectorAll('[data-checkout]');
      var $inputs_to_create_token = MPv1.getInputsToCreateToken();

      for(var x = 0; x < $inputs.length; x++){
        var element = $inputs[x];

        //add events only in the required fields
        if($inputs_to_create_token.indexOf(element.getAttribute("data-checkout")) > -1){

          var event = "focusout";

          if(element.nodeName == "SELECT"){
            event = "change";
          }

          MPv1.addListenerEvent(element, event, MPv1.validateInputsCreateToken);

          //for firefox
          MPv1.addListenerEvent(element, "blur", MPv1.validateInputsCreateToken);

          if(MPv1.create_token_on.keyup){
            MPv1.addListenerEvent(element, "keyup", MPv1.validateInputsCreateToken);
          }

          if(MPv1.create_token_on.paste){
            MPv1.addListenerEvent(element, "paste", MPv1.validateInputsCreateToken);
          }

        }
      }
    }

    MPv1.createTokenBySubmit = function(){
      MPv1.addListenerEvent(document.querySelector(MPv1.selectors.form), 'submit', MPv1.doPay);
    }

    var doSubmit = false;

    MPv1.doPay = function(event){
      event.preventDefault();
      if(!doSubmit){
        MPv1.createToken();
        return false;
      }
    }


    MPv1.validateInputsCreateToken = function(){
      var valid_to_create_token = true;
      var $inputs = MPv1.getForm().querySelectorAll('[data-checkout]');
      var $inputs_to_create_token = MPv1.getInputsToCreateToken();

      for(var x = 0; x < $inputs.length; x++){
        var element = $inputs[x];

        //check is a input to create token
        if($inputs_to_create_token.indexOf(element.getAttribute("data-checkout")) > -1){
          if(element.value == -1 || element.value == ""){
            valid_to_create_token = false;
          } //end if check values
        } //end if check data-checkout
      }//end for

      if(valid_to_create_token){
        MPv1.createToken();
      }
    }

    MPv1.createToken = function(){
      MPv1.hideErrors();

      //show loading
      document.querySelector(MPv1.selectors.box_loading).style.background = "url("+MPv1.paths.loading+") 0 50% no-repeat #fff";

      //form
      var $form = MPv1.getForm();
      Mercadopago.createToken($form, MPv1.sdkResponseHandler);

      return false;
    }

    MPv1.sdkResponseHandler = function(status, response) {

      var $form = MPv1.getForm();

      document.querySelector(MPv1.selectors.box_loading).style.background = "";
      if (status != 200 && status != 201) {
        MPv1.showErrors(response);
      } else {
        var token = document.querySelector(MPv1.selectors.token);
        token.value = response.id;

        if(MPv1.add_truncated_card){
          var card = MPv1.truncateCard(response);
          document.querySelector(MPv1.selectors.cardTruncated).value=card;
        }

        if (!MPv1.create_token_on.event) {
          doSubmit=true;
          btn = document.querySelector(MPv1.selectors.form);
          btn.submit();
        }
      }
    }

    /*
    *
    *
    * useful functions
    *
    */


    MPv1.resetBackgroundCard = function () {
      document.querySelector(MPv1.selectors.paymentMethodSelector).style.background = "no-repeat #fff";
      document.querySelector(MPv1.selectors.cardNumber).style.background = "no-repeat #fff";
    }


    MPv1.setForm = function () {
      if(MPv1.customer_and_card.status){
        document.querySelector(MPv1.selectors.formDiv).style.display = 'none';
        document.querySelector(MPv1.selectors.mpSecurityCodeCustomerAndCard).removeAttribute('style');
      }else{
        document.querySelector(MPv1.selectors.mpSecurityCodeCustomerAndCard).style.display = 'none';
        document.querySelector(MPv1.selectors.formDiv).removeAttribute('style');
      }

      Mercadopago.clearSession();

      if(MPv1.create_token_on.event){
        MPv1.createTokenByEvent();
        MPv1.validateInputsCreateToken();
      }

      document.querySelector(MPv1.selectors.CustomerAndCard).value = MPv1.customer_and_card.status;
    }

    MPv1.getForm = function(){
      if(MPv1.customer_and_card.status){
        return document.querySelector(MPv1.selectors.formCustomerAndCard);
      }else{
        return document.querySelector(MPv1.selectors.form);
      }
    }

    MPv1.getInputsToCreateToken = function(){
      if(MPv1.customer_and_card.status){
        return MPv1.inputs_to_create_token_customer_and_card;
      }else{
        return MPv1.inputs_to_create_token;
      }
    }

    MPv1.truncateCard = function(response_card_token){
      var first_six_digits;
      var last_four_digits;

      if(MPv1.customer_and_card.status){
        var cardSelector = document.querySelector(MPv1.selectors.paymentMethodSelector);
        first_six_digits = cardSelector[cardSelector.options.selectedIndex].getAttribute("first_six_digits").match(/.{1,4}/g)
        last_four_digits = cardSelector[cardSelector.options.selectedIndex].getAttribute("last_four_digits")
      }else{
        first_six_digits = response_card_token.first_six_digits.match(/.{1,4}/g)
        last_four_digits = response_card_token.last_four_digits
      }

      var card = first_six_digits[0] + " " + first_six_digits[1] + "** **** " + last_four_digits;
      return card;

    }

    MPv1.getAmount = function() {
        console.info("==== entrou aqui getAmount ===== ");
        console.info("==== entrou aqui getAmount ===== " + document.querySelector(MPv1.selectors.amount).value);
        return document.querySelector(MPv1.selectors.amount).value - document.querySelector(MPv1.selectors.discount).value;
    }

    MPv1.getAmountWithoutDiscount = function() {
        console.info("==== entrou aqui getAmount ===== ");
        return document.querySelector(MPv1.selectors.amount).value;
    }

    /*
    *
    *
    * Show errors
    *
    */

    MPv1.showErrors = function(response){
      var $form = MPv1.getForm();

      for(var x = 0; x < response.cause.length; x++){
        var error = response.cause[x];
        var $span = $form.querySelector('#mp-error-' + error.code);
        var $input = $form.querySelector($span.getAttribute("data-main"));

        $span.style.display = 'inline-block';
        $input.classList.add("mp-error-input");

      }

      return;
    }

    MPv1.hideErrors = function(){
      console.info("===hideErrors===");
      for(var x = 0; x < document.querySelectorAll('[data-checkout]').length; x++){
        var $field = document.querySelectorAll('[data-checkout]')[x];
        $field.classList.remove("mp-error-input");

      } //end for

      for(var x = 0; x < document.querySelectorAll('.mp-error').length; x++){
        var $span = document.querySelectorAll('.mp-error')[x];
        $span.style.display = 'none';

      }

      return;
    }

    /*
    *
    * Add events to guessing
    *
    */


    MPv1.addListenerEvent = function(el, eventName, handler){
      if (el.addEventListener) {
        el.addEventListener(eventName, handler);
      } else {
        el.attachEvent('on' + eventName, function(){
          handler.call(el);
        });
      }
    };

    MPv1.addListenerEvent(document.querySelector(MPv1.selectors.cardNumber), 'keyup', MPv1.guessingPaymentMethod);
    MPv1.addListenerEvent(document.querySelector(MPv1.selectors.cardNumber), 'keyup', MPv1.clearOptions);
    MPv1.addListenerEvent(document.querySelector(MPv1.selectors.cardNumber), 'change', MPv1.guessingPaymentMethod);


    // MPv1.cardsHandler();

    MPv1.showTaxes = function(){
      var selectorIsntallments = document.querySelector(MPv1.selectors.installments);
      var tax = selectorIsntallments.options[selectorIsntallments.selectedIndex].getAttribute('data-tax');

      var cft = ""
      var tea = ""

      if(tax != null){
        var tax_split = tax.split('|');
        cft = tax_split[0].replace('_', ' ');
        tea = tax_split[1].replace('_', ' ');

        if(cft == "CFT 0,00%" && tea == "TEA 0,00%"){
          cft = ""
          tea = ""
        }

      }

      document.querySelector(MPv1.selectors.taxTextCFT).innerHTML = cft;
      document.querySelector(MPv1.selectors.taxTextTEA).innerHTML = tea;

    }

    /*
    *
    * Utilities
    *
    */

    MPv1.referer = (function () {
      var referer = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');

      return referer;
    })();

    MPv1.AJAX = function(options) {
      var useXDomain = !!window.XDomainRequest;

      var req = useXDomain ? new XDomainRequest() : new XMLHttpRequest()
      var data;

      options.url += (options.url.indexOf("?") >= 0 ? "&" : "?") + "referer="+escape(MPv1.referer);

      options.requestedMethod = options.method;

      if (useXDomain && options.method == "PUT") {
        options.method = "POST";
        options.url += "&_method=PUT";
      }

      req.open(options.method, options.url, true);

      req.timeout = options.timeout || 1000;

      if (window.XDomainRequest) {
        req.onload = function(){
          data = JSON.parse(req.responseText);
          if (typeof options.success === "function") {
            options.success(options.requestedMethod === 'POST' ? 201 : 200, data);
          }
        };
        req.onerror = req.ontimeout = function(){
          if(typeof options.error === "function"){
            options.error(400,{user_agent:window.navigator.userAgent, error : "bad_request", cause:[]});
          }
        };
        req.onprogress = function() {};
      } else {
        req.setRequestHeader('Accept','application/json');

        if(options.contentType){
          req.setRequestHeader('Content-Type', options.contentType);
        }else{
          req.setRequestHeader('Content-Type', 'application/json');
        }

        req.onreadystatechange = function() {
          if (this.readyState === 4){
            if (this.status >= 200 && this.status < 400){
              // Success!
              data = JSON.parse(this.responseText);
              if (typeof options.success === "function") {
                options.success(this.status, data);
              }
            }else if(this.status >= 400){
              data = JSON.parse(this.responseText);
              if (typeof options.error === "function") {
                options.error(this.status, data);
              }
            }else if (typeof options.error === "function") {
              options.error(503, {});
            }
          }
        };
      }

      if(options.method === 'GET' || options.data == null || options.data == undefined){
        req.send();
      }else{
        req.send(JSON.stringify(options.data));
      }
    }




    /*
    *
    *
    * Initialization function
    *
    */

    MPv1.Initialize = function(site_id, public_key, coupon_mode, discount_action_url, payer_email){
      console.info("Initialize");
      //sets
      MPv1.site_id = site_id
      MPv1.public_key = public_key
      MPv1.coupon_of_discounts.default = coupon_mode
      MPv1.coupon_of_discounts.discount_action_url = discount_action_url
      MPv1.coupon_of_discounts.payer_email = payer_email

      Mercadopago.setPublishableKey(MPv1.public_key);

      // flow coupon of discounts
      if (MPv1.coupon_of_discounts.default) {
        MPv1.addListenerEvent(document.querySelector(MPv1.selectors.applyCoupon), 'click', MPv1.checkCouponEligibility);
      } else {
        document.querySelector(MPv1.selectors.formCoupon).style.display = 'none';
      }

      //flow: customer & cards
      var selectorPmCustomerAndCards = document.querySelector(MPv1.selectors.pmCustomerAndCards);

      console.info("===select===" + selectorPmCustomerAndCards);

      if(selectorPmCustomerAndCards != null &&
        MPv1.customer_and_card.default &&
        selectorPmCustomerAndCards.childElementCount > 0){
        MPv1.addListenerEvent(document.querySelector(MPv1.selectors.paymentMethodSelector), 'change', MPv1.cardsHandler);
        MPv1.cardsHandler();
      }else{
        //if customer & cards is disabled
        //or customer does not have cards
        MPv1.customer_and_card.status = false;
        document.querySelector(MPv1.selectors.formCustomerAndCard).style.display = 'none';
      }

      if(MPv1.create_token_on.event){
        MPv1.createTokenByEvent();
      }else{
        MPv1.createTokenBySubmit()
      }

      //flow: MLM
      if(MPv1.site_id != "MLM"){
        Mercadopago.getIdentificationTypes();
      }

      if(MPv1.site_id == "MLM"){

        //hide documento for mex
        document.querySelector(MPv1.selectors.mpDoc).style.display = 'none';
        // document.querySelector(MPv1.selectors.mpPaymentMethodSelector).removeAttribute('style');

        if(!MPv1.customer_and_card.status){
          document.querySelector(MPv1.selectors.mpSecurityCodeCustomerAndCard).style.display = 'none';
        }

        document.querySelector(MPv1.selectors.formCustomerAndCard).removeAttribute('style');

        //removing not used fields for this country
        MPv1.inputs_to_create_token.splice(MPv1.inputs_to_create_token.indexOf("docType"), 1);
        MPv1.inputs_to_create_token.splice(MPv1.inputs_to_create_token.indexOf("docNumber"), 1);

        MPv1.addListenerEvent(document.querySelector(MPv1.selectors.paymentMethodSelector), 'change', MPv1.changePaymetMethodSelector);

        //get payment methods and populate selector
        MPv1.getPaymentMethods();
      }

      //flow: MLB AND MCO
      if (MPv1.site_id == "MLB") {

        document.querySelector(MPv1.selectors.mpDocType).style.display = 'none';
        document.querySelector(MPv1.selectors.mpIssuer).style.display = 'none';
        //ajust css
        document.querySelector(MPv1.selectors.docNumber).classList.remove("mp-col-75");
        document.querySelector(MPv1.selectors.docNumber).classList.add("mp-col-100");

      } else if (MPv1.site_id == "MCO") {
        document.querySelector(MPv1.selectors.mpIssuer).style.display = 'none';
      } else if (MPv1.site_id == "MLA") {


        document.querySelector(MPv1.selectors.boxInstallmentsSelector).classList.remove("mp-col-100");
        document.querySelector(MPv1.selectors.boxInstallmentsSelector).classList.add("mp-col-70");

        document.querySelector(MPv1.selectors.taxCFT).style.display = 'block';
        document.querySelector(MPv1.selectors.taxTEA).style.display = 'block';

        MPv1.addListenerEvent(document.querySelector(MPv1.selectors.installments), 'change', MPv1.showTaxes);

      }

      if (MPv1.debug) {
        document.querySelector(MPv1.selectors.utilities_fields).style.display = 'inline-block';
        console.log(MPv1);
      }

      document.querySelector(MPv1.selectors.site_id).value = MPv1.site_id;

      //set form for basic ou customer & cards
      // MPv1.setForm();

      return;
    }


    this.MPv1 = MPv1;

  }).call();
