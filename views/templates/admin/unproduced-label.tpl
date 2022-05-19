<div class="panel">
    <div class="panel-body">
        <div class="alert alert-info" role="alert" data-alert="info">
            <div class="alert-text">
                <h4>{l s='No se ha generado la orden para emitir la etiqueta' mod='rj_carrier'} - {$id_order}</h4>
                <a href="{$link->getAdminLink('AdminRjShipmentGenerate')}" class="btn btn-default btn-info">
                    {l s='volver' mod='rj_carrier'}
                </a>
            </div>
        </div>
    </div>
</div>