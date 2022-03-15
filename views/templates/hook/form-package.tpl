<form name="order_rj_packages"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $id_order|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}#block-rj-carrier"
    method="post" class="form-horizontal">
    <input type="hidden" name="id_order" id="rj_id_order" value="{$id_order}">
    <input type="hidden" name="id_infopackage" value="{$info_package.id_infopackage}">
    <div class="form-group row">
        <label class="form-control-label label-on-top col-12">{l s='Select Carrier' mod='rj_carrier'}</label>
        <div class="col-12">
            <select class="custom-select form-control" name="id_reference_carrier" id="id_reference_carrier">
                <option value="0">-</option>
                {foreach from=$carriers item=carrier}
                <option value="{$carrier.id_reference}" {if $carrier.id_reference == $info_package.id_reference_carrier}
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
                    <input type="checkbox" name="rj_contrareembolso" id="rj_contrareembolso" value="1" {if $info_package.cash_ondelivery > 0} checked {/if}>
                    </div>
                </div>
                <input type="text" class="form-control" name="rj_cash_ondelivery" id="rj_cash_ondelivery" value="{$info_package.cash_ondelivery|string_format:"%.2f"}">
            </div>
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='Packages' mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_quantity" id="rj_quantity" value="{$info_package.quantity}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='Weight' mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_weight" id="rj_weight" value="{$info_package.weight|string_format:"%.2f"}">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='length' mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_length" id="rj_length" value="{$info_package.length|string_format:"%.2f"}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='width' mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_width" id="rj_width" value="{$info_package.width|string_format:"%.2f"}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='height' mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_height" id="rj_height" value="{$info_package.height|string_format:"%.2f"}">
        </div>
    </div>
    <div class="form-group row">
        <div class="col">
            <label class="form-control-label label-on-top col-12">{l s='hour from' mod='rj_carrier'}</label>
            <input type="time" class="form-control" name="rj_hour_from" id="rj_hour_from" value="{$info_package.hour_from|substr:0:-3}">
        </div>
        <div class="col">
            <label class="form-control-label label-on-top col-12">{l s='hour until' mod='rj_carrier'}</label>
            <input type="time" class="form-control" name="rj_hour_until" id="rj_hour_until" value="{$info_package.hour_until|substr:0:-3}">
        </div>
        <div class="col" id="select_typeshipment">
            <label class="form-control-label label-on-top col-12">{l s='Type Shipment' mod='rj_carrier'}</label>
            <select class="custom-select form-control" name="id_type_shipment" id="id_type_shipment">
            {if $info_type_shipment}
                {foreach from=$info_type_shipment item=type_shipment}
                <option value="{$type_shipment.id_type_shipment}" {if $type_shipment.id_reference_carrier == $info_package.id_reference_carrier}
                    selected="selected" {/if}> 
                    {$type_shipment.name|escape:'html':'UTF-8'}
                </option>
                {/foreach}
            {/if}
            </select>
        
        </div>
    </div>
    <div class="form-group row">
        <label class="form-control-label label-on-top col-12">{l s='Carrier message' mod='rj_carrier'}</label>
        <div class="col-12">
            <textarea id="rj_message" cols="30" rows="3" class="js-countable-input form-control" data-max-length="1200"
                maxlength="1200" name="rj_message">{$info_package.message}</textarea>
        </div>
    </div>
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
        });
            
        $('#id_reference_carrier').on('change', function(e){
            e.preventDefault();
            getTypeShipment(this.value);
        });
    })

    function getPriceOrder(){
        if($('#rj_contrareembolso').prop('checked')){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '{$url_ajax}', 
                data:
                {
                    ajax: 1,
                    action: 'contrareembolso',
                    id_order: '{$id_order}',
                },
                success: function(msg){
                    $('#rj_cash_ondelivery').val(msg);
                },
                error: function(msg){

                }
            });
        }else{
            $('#rj_cash_ondelivery').val('');
        }
    }

    function getTypeShipment(id_reference_carrier){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '{$url_ajax}', 
            data:
            {
                ajax: 1,
                action: 'typeShipment',
                id_reference_carrier: id_reference_carrier,
            },
            success: function(typeshipments){
                let select_typeshipments = '';
                for (let key in typeshipments){
                    select_typeshipments += '<option value="'+ typeshipments[key].id_type_shipment +'">' + typeshipments[key].name + '</option>';
                }
                $('#id_type_shipment').html(select_typeshipments);
            },
            error: function(data){

            }
        });
    }
</script>