<div class="rj-label-carrier">
    <div class="rj-data-carrier">
        <h4>{$name_carrier}</h4>
        <p class="rj-sub-title">{l s='Transportista' mod='rj_carrier'}</p>
    </div>
    <div class="rj-data-from row">
        <div class="col-md-2">
            <h4>{l s='from' mod='rj_carrier'}.</h4>
        </div>
        <div class="col-md-6">
            <h4>{$info_shop.company|upper}</h4>
            <p class="rj-sub-title">{l s='empresa' mod='rj_carrier'}</p>
            <h4>{$info_shop.street}</h4>
            <p class="rj-sub-title">{l s='dirección' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-4">
            <h4>{$info_shop.phone}</h4>
            <p class="rj-sub-title">{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$info_shop.email}</h4>
            <p class="rj-sub-title">{l s='email' mod='rj_carrier'}</p>
        </div>
    </div>
    <div class="rj-data-customer row">
        <div class="col-md-2">
            <h4>TO.</h4>
        </div>
        <div class="col-md-6">
            <h4>{$info_customer.firstname} {$info_customer.lastname}</h4>
            <p class="rj-sub-title">{l s='cliente' mod='rj_carrier'}</p>
            <h4>{$info_customer.address1}</h4>
            <h4>{$info_customer.postcode} - {$info_customer.city} - {$info_customer.country}</h4>
            <p class="rj-sub-title">{l s='dirección' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-4">
            <h4>{$info_customer.phone} - {$info_customer.phone_mobile}</h4>
            <p class="rj-sub-title">{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$info_shipment.message}</h4>
            <p class="rj-sub-title">{l s='message' mod='rj_carrier'}</p>
        </div>
    </div>
    <div class="rj-data-package row">
        <div class="col-md-3">
            <p class="rj-sub-title">{l s='Nº pedido' mod='rj_carrier'}</p>
            <h4>{$id_order}</h4>
        </div>
        <div class="col-md-3 rj-weight">
            <p class="rj-sub-title">{l s='Weight' mod='rj_carrier'}</p>
            <h4>{$info_package.weight|string_format:"%.2f"}</h4>
        </div>
        <div class="col-md-3 rj-weight">
            <p class="rj-sub-title">{l s='Packages' mod='rj_carrier'}</p>
            <h4>{$info_package.quantity}</h4>
        </div>
        <div class="col-md-3">
            <p class="rj-sub-title">{l s='Cash on delivery' mod='rj_carrier'}</p>
            <h4>{Tools::displayPrice($info_shipment.cash_ondelivery)}</h4>
        </div>
    </div>
</div>