{* <form name="order_rj_shipment"
    action="{$link}"
    method="get" class="form-horizontal">
    $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->module.'&tab_module='.$this->tab.'&module_name='.$this->module.'&tab_form='.$this->shortname;
    <input type="hidden" name="id_infopackage" value="{$company}">
    <input type="hidden" name="info_company_carrier" value="{$company}">
    <button type="submit" id="btnShipment" class="btn btn-success pull-right"
        name="submitShipment">
        <i class="material-icons">local_shipping</i>
        {l s='Create shipment' mod='rj_carrier'} - {$company}
    </button>
</form> *}


<a href="{$link}" class="btn btn-primary">{l s='Create type shipment' mod='rj_carrier'} - {$company}</a>