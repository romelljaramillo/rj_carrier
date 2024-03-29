<table class="table" id="rjShipmentTable">
    <thead>
        <tr>
            <th>{l s='id' mod='rj_carrier'}</th>
            <th>{l s='id shipment' mod='rj_carrier'}</th>
            <th>{l s='package id' mod='rj_carrier'}</th>
            <th>{l s='tracker code' mod='rj_carrier'}</th>
            <th>{l s='Print' mod='rj_carrier'}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$labels item=label}
        <tr id="rjLabels" class="cellLabels">
            <td class="cellIdLabel">
                {$label.id_label}
            </td>
            <td class="cellIdShipmentLabel">
                {$label.id_shipment}
            </td>
             <td class="cell-package-id">
                {$label.package_id}
            </td>
            <td class="cellTrackerCode">
                {$label.tracker_code}
            </td>
            <td class="cellTrackerCode">
                {if $label.print} 
                <i class="material-icons text-success" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='printed' mod='rj_carrier'}">print</i> 
                {else} 
                <i class="material-icons text-muted" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='not printed' mod='rj_carrier'}">print_disabled</i> 
                {/if}
            </td>
            <td class="text-right cellShipmentActions">
                <form name="order_rj_shipment"
                    action="{$link->getAdminLink('AdminRjLabel', true, ['id_order' => $id_order|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}"
                    method="post" class="form-horizontal">
                    <input type="hidden" name="id_label" value="{$label.id_label}">
                    <button type="submit" id="btnGetLabel" name="submitGetLabel" formtarget="_blank"
                    data-toggle="pstooltip" data-placement="top" data-original-title="print label" class="btn btn-sm tooltip-link js-rjShipment-delete-btn">
                        <i class="material-icons">receipt</i>
                    </button>
                </form>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>