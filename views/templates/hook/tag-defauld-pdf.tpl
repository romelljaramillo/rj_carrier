<style>
    h4{
        line-height: 0.9;
        font-size: 10px; 
        font-family: 'Ubuntu Condensed',Helvetica,Arial,sans-serif;
    }
    p{
        line-height: 0.6;
        color: #777; 
        font-size: 8px;
    }
</style>
<table style="width: 100%; top:0;">
    <tr>
        <td>
            <h4 style="font-size: 20px; text-align: right;">{$name_carrier}</h4>
            <p>{l s='Transportista' mod='rj_carrier'}</p>
        </td>
    </tr>
    <tr><td style="border-bottom: 1px solid #777;"></td></tr>
    <tr>
        <td style="width: 14%">
            <h4>{l s='from' mod='rj_carrier'}.</h4>
        </td>
        <td style="width: 43%">
            <p>{l s='empresa' mod='rj_carrier'}</p>
            <h4>{$infoShop.company|upper}</h4>
        </td>
        <td style="width: 43%">
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
           <h4>{$infoShop.phone}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%">
        </td>
        <td style="width: 43%">
            <p>{l s='dirección' mod='rj_carrier'}</p>
            <h4>{$infoShop.street}</h4>
        </td>
        <td style="width: 43%">
            <p>{l s='email' mod='rj_carrier'}</p>
            <h4>{$infoShop.email}</h4>
        </td>
    </tr>
    
    <tr><td colspan="3" style="border-bottom: 5px solid #777;"></td></tr>
    
    <tr>
        <td style="width: 14%; border-left: 5px solid #777;">
            <h4>{l s='TO' mod='rj_carrier'}</h4>
        </td>
        <td style="width: 43%;">
            <p>{l s='Transportista' mod='rj_carrier'}cliente</p>
            <h4>{$infoCustomer.firstname} {$infoCustomer.lastname}</h4>
        </td>
        <td style="width: 43%; border-right: 5px solid #777;">
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$infoCustomer.phone} - {$infoCustomer.phone_mobile}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #777;">
        </td>

        <td colspan="2" style="border-right: 5px solid #777;">
            <p>{l s='dirección' mod='rj_carrier'}</p>
            <h4>{$infoCustomer.address1}</h4>
            <h4>{$infoCustomer.postcode} - {$infoCustomer.city} - {$infoCustomer.country}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #777;">
        </td>
        <td colspan="2" style="border-right: 5px solid #777;">
            <p>{l s='mensaje' mod='rj_carrier'}</p>
            <h4>{$infoPackage.message}</h4>
            <br>
        </td>
    </tr>

    <tr><td colspan="3" style="border-top: 5px solid #777;"></td></tr>
    
    <tr style="text-align: center; border-top: 1px solid #ccc;">
        <td style="width:25%; border-top: 1px solid #ccc;">
            <p>{l s='Nº pedido' mod='rj_carrier'}</p>
            <h4 style="font-size: 14px;">{$order_id}</h4>
        </td>
        <td style="width:25%; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-top: 1px solid #ccc;">
            <p>{l s='Weight' mod='rj_carrier'}</p>
            <h4>{$infoPackage.weight|string_format:"%.2f"}</h4>
        </td>
        <td style="width:25%; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-top: 1px solid #ccc;">
            <p>{l s='Packages' mod='rj_carrier'}</p>
            <h4>{$count} / {$infoPackage.packages}</h4>
        </td>
        <td style="width:25%; border-top: 1px solid #ccc;">
            <p>{l s='Contra reembolso' mod='rj_carrier'}</p>
            <h4>{Tools::displayPrice($infoPackage.price_contrareembolso)}</h4>
        </td>
    </tr>
</table>