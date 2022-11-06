<div class="panel">
    <div class="panel-heading">
        <i class="icon-eye"></i> {l s='Info Log' mod='rj_carrier'} - {$log.id_carrier_log}

        <span class="panel-heading-action">
            <a id="desc-rj_carrier_infopackage-cancel" class="list-toolbar-btn" href="{$link->getAdminLink('AdminRjLogs')|escape:'html':'UTF-8'}">
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Cancelar" data-html="true" data-placement="top">
                    <i class="process-icon-cancel"></i>
                </span>
            </a>
        </span>
    </div>
    <div class="panel-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <p class="text-info">id_shipment: </p>
                {$log.id_shipment}
            </li>
            <li class="list-group-item">
                <p class="text-info">name: </p>
                {$log.name}
            </li>
            <li class="list-group-item">
                <p class="text-info">request: </p>
                {$log.request}
            </li>
            <li class="list-group-item">
                <p class="text-info">response: </p>
                {$log.response}
            </li>
            <li class="list-group-item">
                <p class="text-info">date_add: </p>
                {$log.date_add}
            </li>
            <li class="list-group-item">
                <p class="text-info">date_upd: </p>
                {$log.date_upd}
            </li>
        </ul>
    </div>
</div>