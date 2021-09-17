<div class="rj-tag-transport">
    <div class="rj-data-carrier">
        <h4>{$name_carrier}</h4>
        <p class="rj-sub-title">{l s='Transportista' mod='rj_carrier'}</p>
    </div>
    <div class="rj-data-from row">
        <div class="col-md-2">
            <h4>{l s='from' mod='rj_carrier'}.</h4>
        </div>
        <div class="col-md-6">
            <h4>{$infoShop.company|upper}</h4>
            <p class="rj-sub-title">{l s='empresa' mod='rj_carrier'}</p>
            <h4>{$infoShop.street}</h4>
            <p class="rj-sub-title">{l s='dirección' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-4">
            <h4>{$infoShop.phone}</h4>
            <p class="rj-sub-title">{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$infoShop.email}</h4>
            <p class="rj-sub-title">{l s='email' mod='rj_carrier'}</p>
        </div>
    </div>
    <div class="rj-data-customer row">
        <div class="col-md-2">
            <h4>TO.</h4>
        </div>
        <div class="col-md-6">
            <h4>{$infoCustomer.firstname} {$infoCustomer.lastname}</h4>
            <p class="rj-sub-title">{l s='Transportista' mod='rj_carrier'}cliente</p>
            <h4>{$infoCustomer.address1}</h4>
            <h4>{$infoCustomer.postcode} - {$infoCustomer.city} - {$infoCustomer.country}</h4>
            <p class="rj-sub-title">{l s='dirección' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-4">
            <h4>{$infoCustomer.phone} - {$infoCustomer.phone_mobile}</h4>
            <p class="rj-sub-title">{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$infoPackage.message}</h4>
            <p class="rj-sub-title">{l s='message' mod='rj_carrier'}</p>
        </div>
    </div>
    <div class="rj-data-order row">
        <div class="col-md-4">
            <h4>{$order_id}</h4>
            <p class="rj-sub-title">{l s='Nº pedido' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-4 rj-weight">
            <h4>{$infoPackage.weight}</h4>
            <p class="rj-sub-title">{l s='Weight' mod='rj_carrier'}</p>
        </div>
        <div class="col-md-4">
            <h4>{$infoPackage.packages}</h4>
            <p class="rj-sub-title">{l s='Packages' mod='rj_carrier'}</p>
        </div>
    </div>
</div>