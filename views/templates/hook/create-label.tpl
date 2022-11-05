<form name="order_rj_shipment"
    action="{$link->getAdminLink('AdminOrders', true, ['id_order' => $id_order|intval, 'vieworder' => 1])|escape:'html':'UTF-8'}#block-rj-carrier"
    method="post" class="form-horizontal">
    <input type="hidden" name="id_infopackage" value="{$info_package.id_infopackage}">
    <input type="hidden" name="info_company_carrier" value="{$info_company_carrier.id_carrier_company}">
    <button type="submit" id="btnCreateLabel" class="btn btn-success" name="submitCreateLabel">
        <i class="material-icons">local_shipping</i>
        {l s='Create labels' mod='rj_carrier'} - {$info_company_carrier.shortname}
    </button>
</form>