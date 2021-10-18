{if $shipment}
<table class="table" id="rjShipmentTable">
    <thead>
        <tr>
            <th>{l s='Id' mod='rj_carrier'}</th>
            <th>{l s='ShipmentId' mod='rj_carrier'}</th>
            <th>{l s='Order' mod='rj_carrier'}</th>
            <th>{l s='Carrier' mod='rj_carrier'}</th>
            <th>{l s='Product' mod='rj_carrier'}</th>
            <th>{l s='Reference' mod='rj_carrier'}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr id="rjShipment" class="cellShipment">
            <td class="cellIdShipment">
                {$shipment.id_shipment}
            </td>
            <td class="cellShipmentId">
                {$shipment.shipmentid}
            </td>
            <td class="cellShipmentOrderId">
                {$shipment.id_order}
            </td>
            <td class="cellShipmentCarrier">
                {$shipment.name_carrier}
            </td>
            <td class="cellShipmentProduct">
                {$shipment.product}
            </td>
            <td class="cellShipmentReference">
                {$shipment.order_reference}
            </td>
            <td class="text-right cellShipmentActions">
            <div class="row">
                <div class="col-6 p-0">
                    <form name="order_rj_shipment"
                        action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $order_id|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}"
                        method="post" class="form-horizontal">
                        <input type="hidden" name="id_shipment" value="{$shipment.id_shipment}">
                        <button type="submit" id="btnDeleteShipment" name="submitDeleteShipment" data-toggle="pstooltip" 
                        data-placement="top" data-original-title="delete" class="btn btn-sm tooltip-link js-rjShipment-delete-btn">
                            <i class="material-icons">delete</i>
                        </button>
                    </form>
                </div>
                <div class="col-6 pl-1">
                    <form action="{$link->getAdminLink('AdminRJCarrier', true, ['id_order' => $order_id|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}"
                        method="post" class="form-horizontal">
                        <input type="hidden" name="id_shipment" value="{$shipment.id_shipment}">
                        <button type="submit" id="btnCreateLabelsShipment" name="submitCreateLabelsShipment" formtarget="_blank"
                            data-toggle="pstooltip" data-placement="top" data-original-title="print labels" class="btn btn-sm tooltip-link js-rjShipment-delete-btn">
                            <i class="material-icons">receipt</i>
                        </button>
                    </form>
                </div>
            </div>
            </td>
        </tr>
    </tbody>
</table>
{/if}
    