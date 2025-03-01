<div class="rj-label-carrier">
    <div class="rj-data-carrier">
        <h4>{$carrier_name}</h4>
        <p class="rj-sub-title">{l s='Transportista' mod='rj_carrier'}</p>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h4 class="m-1 rj-sub-title">{l s='from' mod='rj_carrier'}.</h4>
        </div>
    </div>
    <div class="rj-data-from row mb-3">
        <div class="col-md-7">
            <h4>{$info_shop.company|upper}</h4>
            <p class="rj-sub-title">{l s='empresa' mod='rj_carrier'}</p>
            <h4>{$info_shop.street}</h4>
            <p class="rj-sub-title">{l s='dirección' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-5">
            <h4>{$info_shop.phone}</h4>
            <p class="rj-sub-title">{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$info_shop.email}</h4>
            <p class="rj-sub-title">{l s='email' mod='rj_carrier'}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h4 class="m-1 rj-sub-title">{l s='To' mod='rj_carrier'}.</h4>
        </div>
    </div>
    <div class="rj-data-customer row">
        <div class="col-md-7">
            <h4>{$info_customer.firstname} {$info_customer.lastname}</h4>
            <p class="rj-sub-title">{l s='cliente' mod='rj_carrier'}</p>
            <h4>{$info_customer.address1}</h4>
            <h4>{$info_customer.postcode}
                {if $info_customer.city} - {$info_customer.city}{/if}
                {if $info_customer.state} - {$info_customer.state}{/if}
                {if $info_customer.country} - {$info_customer.country}{/if}
            </h4>
            <p class="rj-sub-title">{l s='dirección' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-5">
            <h4>{$info_customer.phone} - {$info_customer.phone_mobile}</h4>
            <p class="rj-sub-title">{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$info_package.message}</h4>
            <p class="rj-sub-title">{l s='message' mod='rj_carrier'}</p>
        </div>
    </div>
    <div class="rj-data-package row">
        <div class="col-md-3 border-right">
            <p class="rj-sub-title">{l s='Nº pedido' mod='rj_carrier'}</p>
            <h4>{$id_order}</h4>
        </div>
        <div class="col-md-3 border-right">
            <p class="rj-sub-title">{l s='Weight' mod='rj_carrier'}</p>
            <h4>{$info_package.weight|string_format:"%.2f"}</h4>
        </div>
        <div class="col-md-3 border-right">
            <p class="rj-sub-title">{l s='Packages' mod='rj_carrier'}</p>
            <h4>{$info_package.quantity}</h4>
        </div>
        <div class="col-md-3">
            <p class="rj-sub-title">{l s='Cash on delivery' mod='rj_carrier'}</p>
            <h4>{Tools::displayPrice($info_package.cash_ondelivery)}</h4>
        </div>
    </div>
    <div id="rj-data-label-gls" class="rj-data-package row border-top-0 mt-0">
        <div class="col-md-3 border-right">
            <p class="rj-sub-title">{l s='Retorno' mod='rj_carrier'}</p>
            <h4>
                {if $info_package.retorno == 0}
                    {l s='Sin retorno' mod='rj_carrier'}
                {elseif $info_package.retorno == 1}
                    {l s='Obligatorio' mod='rj_carrier'}
                {elseif $info_package.retorno == 2}
                    {l s='Opcional' mod='rj_carrier'}
                {/if}
            </h4>
        </div>
        <div class="col-md-3 border-right">
            <p class="rj-sub-title">{l s='RCS' mod='rj_carrier'}</p>
            <h4>
                {if $info_package.rcs == 0}
                    {l s='No' mod='rj_carrier'}
                {else}
                    {l s='Si' mod='rj_carrier'}
                {/if}
            </h4>
        </div>
        <div class="col-md-3 border-right">
            <p class="rj-sub-title">{l s='Valor asegurado' mod='rj_carrier'}</p>
            <h4>
                {if $info_package.vsec == 0}
                    {l s='No' mod='rj_carrier'}
                {else}
                    {l s='Si' mod='rj_carrier'}
                {/if}
            </h4>
        </div>
        <div class="col-md-3">
            <p class="rj-sub-title">{l s='Departamento' mod='rj_carrier'}</p>
            <h4>{$info_package.dorig}</h4>
        </div>
    </div>
</div>
