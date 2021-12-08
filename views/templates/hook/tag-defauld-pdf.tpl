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
            <h4 style="font-size: 20px; text-align: right;">{$name_carrier|escape:'html':'UTF-8'}</h4>
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
            <h4>{$info_shop.company|upper}</h4>
        </td>
        <td style="width: 43%">
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
           <h4>{$info_shop.phone}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%">
        </td>
        <td style="width: 43%">
            <p>{l s='dirección' mod='rj_carrier'}</p>
            <h4>{$info_shop.street|escape:'html':'UTF-8'}</h4>
        </td>
        <td style="width: 43%">
            <p>{l s='email' mod='rj_carrier'}</p>
            <h4>{$info_shop.email}</h4>
        </td>
    </tr>
    <tr><td colspan="3" style="border-bottom: 5px solid #777;"></td></tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #777;">
            <h4>{l s='TO' mod='rj_carrier'}</h4>
        </td>
        <td style="width: 43%;">
            <p>{l s='Transportista' mod='rj_carrier'}cliente</p>
            <h4>{$info_customer.firstname|escape:'html':'UTF-8'} {$info_customer.lastname|escape:'html':'UTF-8'}</h4>
        </td>
        <td style="width: 43%; border-right: 5px solid #777;">
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$info_customer.phone} - {$info_customer.phone_mobile}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #777;">
        </td>

        <td colspan="2" style="border-right: 5px solid #777;">
            <p>{l s='dirección' mod='rj_carrier'}</p>
            <h4>{$info_customer.address1|escape:'html':'UTF-8'}</h4>
            <h4>{$info_customer.postcode} - {$info_customer.city} - {$info_customer.country}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 5px solid #777;">
        </td>
        <td colspan="2" style="border-right: 5px solid #777;">
            <p>{l s='mensaje' mod='rj_carrier'}</p>
            <h4>{$info_shipment.message|escape:'html':'UTF-8'}</h4>
            <br>
        </td>
    </tr>

    <tr><td colspan="3" style="border-top: 5px solid #777;"></td></tr>
    
    <tr style="text-align: center; border-top: 1px solid #ccc;">
        <td style="width:25%; border-top: 1px solid #ccc;">
            <p>{l s='Nº pedido' mod='rj_carrier'}</p>
            <h4 style="font-size: 14px;">{$id_order}</h4>
        </td>
        <td style="width:25%; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-top: 1px solid #ccc;">
            <p>{l s='Weight' mod='rj_carrier'}</p>
            <h4>{$info_package.weight|string_format:"%.2f"}</h4>
        </td>
        <td style="width:25%; border-left: 1px solid #ccc; border-right: 1px solid #ccc; border-top: 1px solid #ccc;">
            <p>{l s='Packages' mod='rj_carrier'}</p>
            <h4>{$count} / {$info_package.quantity}</h4>
        </td>
        <td style="width:25%; border-top: 1px solid #ccc;">
            <p>{l s='cash on delivery' mod='rj_carrier'}</p>
            <h4>{Tools::displayPrice($info_shipment.cash_ondelivery)}</h4>
        </td>
    </tr>
</table>