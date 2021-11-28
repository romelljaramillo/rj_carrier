<form name="order_rj_shipment"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $order_id|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}"
    method="post" class="form-horizontal">
    <input type="hidden" name="id_infopackage" value="{$infoPackage.id}">
    <input type="hidden" name="company_carrier" value="{$company_carrier}">
    <button type="submit" id="btnShipment" class="btn btn-success pull-right"
        name="submitShipment">
        <i class="material-icons">local_shipping</i>
        {l s='Create shipment' mod='rj_carrier'} - {$company_carrier}
    </button>
</form>