<form name="order_rj_packages"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $order_id|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}"
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
        <div class="col-6">
            <label class="form-control-label label-on-top col-12">{l s='Packages'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_packages" id="rj_packages" value="{$infoPackage.packages}">
        </div>
        <div class="col-6">
            <label class="form-control-label label-on-top col-12">{l s='Weight'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_weight" id="rj_weight" value="{$infoPackage.weight}">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='length'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_length" id="rj_length" value="{$infoPackage.length}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='width'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_width" id="rj_width" value="{$infoPackage.width}">
        </div>
        <div class="col-4">
            <label class="form-control-label label-on-top col-12">{l s='height'
                mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_height" id="rj_height" value="{$infoPackage.height}">
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
    <input type="hidden" name="id_order" value="{$infoPackage.order_id}">
    <input type="hidden" name="id_rjcarrier" value="{$infoPackage.id}">
    <div class="panel-footer">
        <button type="submit" id="FormPackageCarrier" class="btn btn-primary pull-right" name="submitFormPackCarrier">
            {l s='Save'}
        </button>
    </div>
</form>