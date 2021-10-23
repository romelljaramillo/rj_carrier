<form name="order_rj_packages"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $order_id|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}#block-rj-carrier"
    method="post" class="form-horizontal">
    <div class="form-group row">
        <label class="form-control-label label-on-top col-12">{l s='Select Carrier'
            mod='rj_carrier'}</label>
        <div class="col-12">
            <select class="custom-select" name="id_reference_carrier" id="id_reference_carrier">
                <option value="0">-</option>
                {foreach from=$carriers item=carrier}
                <option value="{$carrier.id_reference}" {if $carrier.id_reference==$infoPackage.id_reference_carrier}
                    selected="selected" {/if}>
                    {$carrier.name|escape:'html':'UTF-8'}
                </option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='Contrareembolso' mod='rj_carrier'}</label>
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                    <input type="checkbox" name="rj_contrareembolso" id="rj_contrareembolso" value="1" {if $infoPackage.price_contrareembolso > 0} checked {/if}>
                    </div>
                </div>
                <input type="text" class="form-control" name="rj_price_contrareembolso" id="rj_price_contrareembolso" value="{$infoPackage.price_contrareembolso|string_format:"%.2f"}">
            </div>
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='Packages'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_packages" id="rj_packages" value="{$infoPackage.packages}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='Weight'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_weight" id="rj_weight" value="{$infoPackage.weight|string_format:"%.2f"}">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='length'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_length" id="rj_length" value="{$infoPackage.length|string_format:"%.2f"}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='width'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_width" id="rj_width" value="{$infoPackage.width|string_format:"%.2f"}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='height'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_height" id="rj_height" value="{$infoPackage.height|string_format:"%.2f"}">
        </div>
    </div>
    <div class="form-group row">
        <label class="form-control-label label-on-top col-12">{l s='Carrier message'
            mod='rj_carrier'}</label>
        <div class="col-12">
            <textarea id="rj_message" cols="30" rows="3" class="js-countable-input form-control" data-max-length="1200"
                maxlength="1200" name="rj_message">{$infoPackage.message}</textarea>
        </div>
    </div>
    <input type="hidden" name="id_order" id="rj_id_order" value="{$infoPackage.order_id}">
    <input type="hidden" name="id_infopackage" value="{$infoPackage.id}">
    <div class="panel-footer">
        <button type="submit" id="formPackageCarrier" class="btn btn-primary pull-right" name="submitFormPackCarrier">
            {l s='Save'}
        </button>
        <button type="submit" id="savePackSend" class="btn btn-primary pull-right" name="submitSavePackSend">
            {l s='Save and generate' mod='rj_carrier'}
        </button>
    </div>
</form>
<script type="text/javascript">

    $(document).ready(function(){
        $('#rj_contrareembolso').on('click', function(){
            getPriceOrder();
        })
    })

    function getPriceOrder(){
        console.log('getPriceOrder');
        if($('#rj_contrareembolso').prop('checked')){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '{$url_ajax}', 
                data:
                {
                    ajax: 1,
                    action: 'contrareembolso',
                    order_id: '{$order_id}',
                },
                success: function(msg){
                    $('#rj_price_contrareembolso').val(msg);
                },
                error: function(msg){

                }
            });
        }else{
            $('#rj_price_contrareembolso').val('');
        }
    }
</script>