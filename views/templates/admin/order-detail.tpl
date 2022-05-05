<div class="panel">
    <div class="panel-heading">
        <i class="icon-eye"></i> {l s='Info order' mod='rj_carrier'}  - {$id_order}

        <span class="panel-heading-action">
            <a id="desc-rj_carrier_infopackage-cancel" class="list-toolbar-btn" href="{$link->getAdminLink('AdminRjShipmentGenerate')|escape:'html':'UTF-8'}">
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Cancelar" data-html="true" data-placement="top">
                    <i class="process-icon-cancel"></i>
                </span>
            </a>
        </span>
    </div>
    <div class="panel-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{l s='Id' mod='rj_carrier'}</th>
                    <th>{l s='Producto' mod='rj_carrier'}</th>
                    <th>{l s='Quantity' mod='rj_carrier'}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$order_detail item=item}
                <tr>
                    <td>{$item.product_id}</td>
                    <td>{$item.product_name}</td>
                    <td>{$item.product_quantity}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    <div class="panel-footer">

        <a href="{$link->getAdminLink('AdminRjShipmentGenerate')}&id_infopackage={$id_infopackage}&{$action}" 
        {if $shipmet_active}target="_blank"{/if} class="{$printed|escape:'html':'UTF-8'} btn btn-default">
            <i class="material-icons" {if $printed}style="color: green"{/if}>{if $shipmet_active}print{else}description{/if}</i> <br> {$action_label|escape:'html':'UTF-8'}
        </a>

    </div>
</div>