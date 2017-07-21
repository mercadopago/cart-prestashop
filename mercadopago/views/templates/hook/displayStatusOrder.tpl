
    <div class="row">
        <div class="col-xs-12">
            <img src="{$logo_mercadopago|escape:'htmlall':'UTF-8'}" class="logo-wrapper" alt="Mercado Pago">
        </div>
    </div>
    <br>

    {if $payment_status == "approved"}
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {l s='Thank you, your payment has been approved.' mod='mercadopago'}
        </div>
    {/if}
    {if $payment_status == "in_process"}
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {l s='Thank you, your payment is being processed.' mod='mercadopago'}
        </div>
    {/if}
    {if $payment_status == "rejected"}
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {l s='Sorry, your payment was declined.' mod='mercadopago'}
        </div>
    {/if}




