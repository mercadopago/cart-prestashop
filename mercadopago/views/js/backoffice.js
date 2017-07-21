
$(document).ready(function(){
	$('.mercadopago-tabs nav .tab-title').click(function(){
		var elem = $(this);
		var target = $(elem.data('target'));
		elem.addClass('active').siblings().removeClass('active');
		target.show().siblings().hide();
	})

	if ($('.mercadopago-tabs nav .tab-title.active').length == 0){
		$('.mercadopago-tabs nav .tab-title:first').trigger("click");
	}

	$('[data-toggle="tooltip"]').tooltip();

	var list_payment = [
		'visa',
		'master',
		'hipercard',
		'amex',
		'diners',
		'elo',
		'melicard',
		'bolbradesco',
	];

/*

	$("input:radio[name='MERCADOPAGO_STARDAND_ACTIVE']").change(function(){
		if ($(this).is(':checked') && $(this).val() == '1') {

			alert("entrou aqui === " + $(this).val());
			for(i=0; i<list_payment.length;i++){
		    	payment = list_payment[i];
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_on").removeAttr("checked");
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_off").attr("checked", true);

				alert($("#MERCADOPAGO_"+payment+"_ACTIVE_on"));
		    	alert($("#MERCADOPAGO_"+payment+"_ACTIVE_off"));
	    	}
		}
	});


	function validationAllCardsOn(list_payment){
		if($("input:radio[name='MERCADOPAGO_STARDAND_ACTIVE']:checked").val() == '1')
		{
		    for(i=0; i<list_payment.length;i++){
		    	payment = list_payment[i];
		    	console.info(payment);
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_on").removeAttr("checked");
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_off").attr("checked", true);

				var active = $("input:radio[name='MERCADOPAGO_"+payment+"_ACTIVE']:checked").val();

		    	$('<input>').attr({
				    type: 'hidden',
				    id: 'HIDDEN_'+payment+'_ACTIVE',
				    name: 'MERCADOPAGO_'+payment+'_ACTIVE',
				    value: active,
				}).insertAfter("#MERCADOPAGO_"+payment+"_ACTIVE_on");

				$("#MERCADOPAGO_"+payment+"_ACTIVE_on").attr("disabled", true);
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_off").attr("disabled", true);
		    }
		}
	}

	function validationAllCardsOff(list_payment){
		if($("input:radio[name='MERCADOPAGO_STARDAND_ACTIVE']:checked").val() == '0')
		{
			for(i=0; i<list_payment.length;i++){
		    	payment = list_payment[i];
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_on").removeAttr("disabled");
		    	$("#MERCADOPAGO_"+payment+"_ACTIVE_off").removeAttr("disabled");

		    	$("#HIDDEN_"+payment+"_ACTIVE").remove();
		    }
		}
	}

	validationAllCardsOn(list_payment); */

});