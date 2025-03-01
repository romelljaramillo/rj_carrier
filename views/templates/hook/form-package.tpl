<form name="order_rj_packages"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $id_order|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}#block-rj-carrier"
    method="post" class="form-horizontal">
    <input type="hidden" name="id_order" id="rj_id_order" value="{$id_order}">
    <input type="hidden" name="id_infopackage" value="{$info_package.id_infopackage}">
    <input type="hidden" name="carrier_company_shorname" value="{$info_company_carrier.shortname}">

    <!-- Select Carrier -->
    <div class="form-row">
        <div class="form-group col-sm-12">
            <label class="form-label" for="id_reference_carrier">{l s='Select Carrier' mod='rj_carrier'}</label>
            <select class="custom-select form-control" name="id_reference_carrier" id="id_reference_carrier">
                <option value="0">-</option>
                {foreach from=$carriers item=carrier}
                <option value="{$carrier.id_reference}" {if $carrier.id_reference == $info_package.id_reference_carrier} selected="selected" {/if}>
                    {$carrier.name|escape:'html':'UTF-8'}
                </option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_contrareembolso">{l s='Contrareembolso' mod='rj_carrier'}</label>
            <div class="input-group mb-3">
                <div class="input-group-append">
                    <div class="input-group-text">
                    <input type="checkbox" name="rj_contrareembolso" id="rj_contrareembolso" value="1" {if $info_package.cash_ondelivery > 0} checked {/if}>
                    </div>
                </div>
                <input type="text" class="form-control" name="rj_cash_ondelivery" id="rj_cash_ondelivery" value="{$info_package.cash_ondelivery|string_format:"%.2f"}">
                <div class="input-group-append">
                    <span class="input-group-text"> € </span>
                </div>
            </div>
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_quantity">{l s='Packages' mod='rj_carrier'}</label>
            <input type="text" class="form-control" name="rj_quantity" id="rj_quantity" value="{$info_package.quantity}">
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_weight">{l s='Weight' mod='rj_carrier'}</label>
            <div class="input-group">
                <input type="text" class="form-control" name="rj_weight" id="rj_weight" value="{$info_package.weight|string_format:"%.2f"}">
                <div class="input-group-append">
                    <span class="input-group-text"> kg </span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_length">{l s='length' mod='rj_carrier'}</label>
            <div class="input-group">
                <input type="text" class="form-control" name="rj_length" id="rj_length" value="{$info_package.length|string_format:"%.2f"}">
                <div class="input-group-append">
                    <span class="input-group-text"> cm </span>
                </div>
            </div>
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_width">{l s='width' mod='rj_carrier'}</label>
            <div class="input-group">
                <input type="text" class="form-control" name="rj_width" id="rj_width" value="{$info_package.width|string_format:"%.2f"}">
                <div class="input-group-append">
                    <span class="input-group-text"> cm </span>
                </div>
            </div>
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_height">{l s='height' mod='rj_carrier'}</label>
            <div class="input-group">
                <input type="text" class="form-control" name="rj_height" id="rj_height" value="{$info_package.height|string_format:"%.2f"}">
                <div class="input-group-append">
                    <span class="input-group-text"> cm </span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_hour_from">{l s='hour from' mod='rj_carrier'}</label>
            <input type="time" class="form-control" name="rj_hour_from" id="rj_hour_from" value="{$info_package.hour_from|substr:0:-3}">
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_hour_until">{l s='hour until' mod='rj_carrier'}</label>
            <input type="time" class="form-control" name="rj_hour_until" id="rj_hour_until" value="{$info_package.hour_until|substr:0:-3}">
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label">{l s='Type Shipment' mod='rj_carrier'}</label>
            <select class="custom-select form-control" name="id_type_shipment" id="id_type_shipment">
            {foreach from=$info_type_shipment item=type_shipment}
                <option value="{$type_shipment.id_type_shipment}" {if $type_shipment.id_type_shipment == $info_package.id_type_shipment} selected="selected" {/if}>
                    {$type_shipment.name|escape:'html':'UTF-8'}
                </option>
            {/foreach}
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_retorno">{l s='Retorno' mod='rj_carrier'}</label>
            <select id="rj_retorno" name="rj_retorno" title="Retorno" alt="Retorno" class="form-control">
                <option value="0" {if $info_package.retorno == '0'} selected="selected"{/if}>{l s='Sin retorno' mod='rj_carrier'}</option>
                <option value="1" {if $info_package.retorno == '1'} selected="selected"{/if}>{l s='Obligatorio' mod='rj_carrier'}</option>
                <option value="2" {if $info_package.retorno == '2'} selected="selected"{/if}>{l s='Opcional' mod='rj_carrier'}</option>
            </select>
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_rcs">RCS</label>
            <select name="rj_rcs" class="form-control" id="rj_rcs">
                <option value="0" {if $info_package.rcs == '0'} selected="selected"{/if}>{l s='No' mod='rj_carrier'}</option>
                <option value="1" {if $info_package.rcs == '1'} selected="selected"{/if}>{l s='Si' mod='rj_carrier'}</option>
            </select>
            <span>{l s='Retorno Copia Sellada' mod='rj_carrier'}</span>
        </div>
        <div class="form-group col-md-4 col-sm-12">
            <label class="form-label" for="rj_vsec">{l s='Valor asegurado' mod='rj_carrier'}</label>
            <div class="input-group">
                <input type="text" class="form-control" name="rj_vsec" id="rj_vsec" value="{$info_package.vsec|string_format:"%.2f"}">
                <div class="input-group-append">
                    <span class="input-group-text"> € </span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-sm-12">
            <label class="form-label" for="rj_dorig">{l s='Dep. origen' mod='rj_carrier'}</label>
            <input type="text" id="rj_dorig" name="rj_dorig" class="form-control" value="{$info_package.dorig}" />
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-sm-12">
            <label class="form-label">{l s='Carrier message' mod='rj_carrier'}</label>
            <textarea id="rj_message" cols="30" rows="3" class="js-countable-input form-control" data-max-length="1200"
                maxlength="1200" name="rj_message">{$info_package.message}</textarea>
        </div>
    </div>
    <div class="panel-footer">
        <button type="submit" id="formPackageCarrier" class="btn btn-primary" name="submitFormPackCarrier">
            {l s='Save'}
        </button>
        <button type="submit" id="savePackSend" class="btn btn-primary" name="submitSavePackSend">
            {l s='Save and generate' mod='rj_carrier'}
        </button>
    </div>
</form>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        toggleGlsFields('{$info_company_carrier.shortname}', {$info_package|json_encode});
        document.getElementById('rj_contrareembolso').addEventListener('click', function() {
            getPriceOrder();
        });

        document.getElementById('id_reference_carrier').addEventListener('change', function(e) {
            e.preventDefault();
            getTypeShipment(this.value);
        });
    });

    function getPriceOrder() {
        let contrareembolsoCheckbox = document.getElementById('rj_contrareembolso');
        let cashOnDeliveryInput = document.getElementById('rj_cash_ondelivery');

        if (contrareembolsoCheckbox.checked) {
            fetch('{$url_ajax}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: 1,
                    action: 'contrareembolso',
                    id_order: '{$id_order}',
                })
            })
            .then(response => response.json())
            .then(msg => {
                cashOnDeliveryInput.value = msg;
            })
            .catch(error => {
                console.error('Error fetching price order', error);
            });
        } else {
            cashOnDeliveryInput.value = '';
        }
    }

    function getTypeShipment(id_reference_carrier) {
        fetch('{$url_ajax}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                ajax: 1,
                action: 'typeShipment',
                id_reference_carrier: id_reference_carrier,
                id_order: '{$id_order}',
            })
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {

                let selectTypeshipments = response.info_type_shipment.map(type_shipment =>
                    '<option value="' + type_shipment.id_type_shipment + '">' + type_shipment.name + '</option>'
                ).join('');

                document.getElementById('id_type_shipment').innerHTML = selectTypeshipments;

                let info_package = response.info_package;

                let shortname = response.shortname || '';

                const fieldsToUpdate = ['quantity', 'weight', 'length', 'width', 'height', 'hour_from', 'hour_until', 'message'];
                fieldsToUpdate.forEach(field => {
                    document.getElementById('rj_' + field).value = info_package[field] || '';
                });

                toggleGlsFields(shortname, info_package);
            } else {
                showErrorMessage(response.message || '{l s='An error occurred while fetching data.'}');
                document.getElementById('id_type_shipment').innerHTML = '';
            }
        })
        .catch(() => {
            showErrorMessage('{l s='Failed to retrieve carrier information.'}');
            document.getElementById('id_type_shipment').innerHTML = '';
        });
    }

    function toggleGlsFields(shortname, info_package) {
        const fields = ['rj_retorno', 'rj_rcs', 'rj_vsec', 'rj_dorig'];
        const displayStyle = shortname === 'GLS' ? '' : 'none';
        const isDisabled = shortname !== 'GLS';

        fields.forEach(field => {
            const element = document.getElementById(field);
            const value = info_package[field.replace('rj_', '')];
            if (field === 'rj_retorno' || field === 'rj_rcs') {
                element.value = value !== undefined ? value : '0';
            } else {
                element.value = shortname === 'GLS' ? value || '' : '';
            }
            element.parentElement.parentElement.style.display = displayStyle;
            element.disabled = isDisabled;
        });

        document.getElementById('rj-data-label-gls').style.display = displayStyle;
    }

</script>

