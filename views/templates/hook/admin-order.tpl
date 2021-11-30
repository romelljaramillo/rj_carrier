{block name='notifications'}
{include file='./notifications.tpl'}
{/block}
<div id="block-rj-carrier" class="carrier-row row">
    <div class="col-md-6 left-column">
        <div class="carrier card"  id="rjcarrierPanel">
            <div class="card-header">
                <h3 class="card-header-title">
                    {l s='Info package' mod='rj_carrier'}
                </h3>
            </div>
            <div id="rj_packages" class="card-body">
                {block name='package'}
                {include file='./form-package.tpl'}
                {/block}
            </div>
        </div>
        {if $company_carrier}
        {if !$shipment}
        <div class="carrier card"  id="rjcarrierPanel">
            <div class="card-header">
                <h3 class="card-header-title">
                    {$company_carrier}
                </h3>
            </div>
            <div id="rj_packages" class="card-body">
                {block name='create-shipment.tpl'}
                {include file='./create-shipment.tpl'}
                {/block}
            </div>
        </div>
        {/if}
        {if $shipment}
        <div class="card" id="rjlistshipmentPanel">
            <div class="card-header">
                <h3 class="card-header-title">
                    {l s='Info shipment' mod='rj_carrier'}
                </h3>
            </div>
            <div class="card-body table-responsive">
                <div class="spinner-order-products-container" id="shipmentLoading">
                    <div class="spinner spinner-primary"></div>
                </div>
                {block name='shipment'}
                {include file='./shipment.tpl'}
                {/block}
            </div>
        </div>
        <div class="card" id="rjlistlabelsPanel">
            <div class="card-header">
                <h3 class="card-header-title">
                    {l s='Info labels' mod='rj_carrier'}
                </h3>
            </div>
            <div class="card-body table-responsive">
                <div class="spinner-order-products-container" id="labelsLoading">
                    <div class="spinner spinner-primary"></div>
                </div>
                {block name='labels'}
                {include file='./labels.tpl'}
                {/block}
            </div>
        </div>
        {/if}
        {/if}
    </div>
    <div class="col-md-6 right-column">
        <div class="etiqueta card">
            <div class="card-header">
                <h3 class="card-header-title">
                    <i class="icon-envelope"></i> {l s='Info tag' mod='rj_carrier'}
                </h3>
            </div>
            <div class="card-body">
                {block name='etiqueta'}
                {include file='./tag-defauld.tpl'}
                {/block}
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        {if $info_package.print} 
                        <i class="material-icons text-success" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='printed' mod='rj_carrier'}">print</i> 
                        {else} 
                        <i class="material-icons text-muted" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='not printed' mod='rj_carrier'}">print_disabled</i> 
                        {/if}
                    </div>
                    <div class="col-md-6 text-right">
                        <a class="btn btn-primary right-column _blank"
                            href="{$link->getAdminLink('AdminRJCarrier', true, ['id_order' => $order_id|intval, 'vieworder' => 1],['action'=>'createEtiquetaPDF', 'id_infopackage'=>{$info_package.id}])|escape:'html':'UTF-8'}">
                            {l s='Print ticket' mod='rj_carrier'}
                            <i class="icon-external-link"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>