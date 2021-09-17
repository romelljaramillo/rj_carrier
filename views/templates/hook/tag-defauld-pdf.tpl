<style>
    h4{
        line-height: 1;
        font-size: 12px; 
        font-family: 'Ubuntu Condensed',Helvetica,Arial,sans-serif;
    }
    p{
        line-height: 0.7;
        color: #c0c0c0; 
        font-size: 9px;
    }
</style>
<table style="width: 100%; top:0;">
    <tr>
        <td>
            <h4 style="font-size: 20px; text-align: right;">{$name_carrier}</h4>
            <p>{l s='Transportista' mod='rj_carrier'}</p>
        </td>
    </tr>
    <tr><td style="border-bottom: 1px solid #c0c0c0;"></td></tr>
    <tr>
        <td style="width: 14%">
            <h4>{l s='from' mod='rj_carrier'}.</h4>
        </td>
        <td style="width: 43%">
            <h4>{$infoShop.company|upper}</h4>
            <p>{l s='empresa' mod='rj_carrier'}</p>
        </td>
        <td style="width: 43%">
           <h4>{$infoShop.phone}</h4>
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
        </td>
    </tr>
    <tr>
        <td style="width: 14%">
        </td>
        <td style="width: 43%">
            <h4>{$infoShop.street}</h4>
            <p>{l s='dirección' mod='rj_carrier'}</p>
        </td>
        <td style="width: 43%">
            <h4>{$infoShop.email}</h4>
            <p>{l s='email' mod='rj_carrier'}</p>
        </td>
    </tr>
    
    <tr><td colspan="3" style="border-bottom: 5px solid #c0c0c0;"></td></tr>
    
    <tr>
        <td style="width: 14%; border-left: 5px solid #c0c0c0;">
            <h4>{l s='TO' mod='rj_carrier'}</h4>
        </td>
        <td style="width: 43%;">
            <h4>{$infoCustomer.firstname} {$infoCustomer.lastname}</h4>
            <p>{l s='Transportista' mod='rj_carrier'}cliente</p>
        </td>
        <td style="width: 43%; border-right: 5px solid #c0c0c0;">
            <h4>{$infoCustomer.phone} - {$infoCustomer.phone_mobile}</h4>
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #c0c0c0;">
        </td>

        <td colspan="2" style="border-right: 5px solid #c0c0c0;">
            <h4>{$infoCustomer.address1}</h4>
            <h4>{$infoCustomer.postcode} - {$infoCustomer.city} - {$infoCustomer.country}</h4>
            <p>{l s='dirección' mod='rj_carrier'}</p>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #c0c0c0;">
        </td>
        <td colspan="2" style="border-right: 5px solid #c0c0c0;">
            <h4>{$infoPackage.message}</h4>
            <p>{l s='message' mod='rj_carrier'}</p>
            <br>
        </td>
    </tr>

    <tr><td colspan="3" style="border-top: 5px solid #c0c0c0;"></td></tr>
    
    <tr>
        <td style="width: 33%; text-align: center; border-top: 1px solid #ccc;">
            <h4>{$order_id}</h4>
            <p>{l s='Nº pedido' mod='rj_carrier'}</p>
        </td>
        <td style="width: 33%; text-align: center; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-top: 1px solid #ccc;">
            <h4>{$infoPackage.weight}</h4>
            <p>{l s='Weight' mod='rj_carrier'}</p>
        </td>
        <td style="width: 33%; text-align: center; border-top: 1px solid #ccc;">
            <h4>{$count} / {$infoPackage.packages}</h4>
            <p>{l s='Packages' mod='rj_carrier'}</p>
        </td>
    </tr>
</table>