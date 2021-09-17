<form name="order_rj_shipment"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $order_id|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}"
    method="post" class="form-horizontal">
    <input type="hidden" name="id_rjcarrier" value="{$infoPackage.id}">
    <button type="submit" id="btnShipment" class="btn btn-success pull-right"
        name="submitShipment">
        <i class="material-icons">local_shipping</i>
        {l s='Create shipment' mod='rj_carrier'}
    </button>
</form>