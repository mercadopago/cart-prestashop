
<form id="module_form" class="defaultForm form-horizontal" action="{$currentIndex|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <div class="panel">
        <div class="panel-heading">{l s='Help' d='Modules.MercadoPago.Admin'}</div>
        <div class="panel-body">
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='View the log:' mod='mercadopago'} </label>
                <div class="col-lg-5">
                    <p><a href="{$log|escape:'htmlall':'UTF-8'}" class="btn btn-link" target="_blank" >{l s="Click here to see the error log" mod='mercadopago'}</a></p>
                </div>
            </div>
        </div>
    </div>
</form>


