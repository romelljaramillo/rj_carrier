
{assign var=color_p value="#777777"}
{assign var=line_height_p value="9pt"}
{assign var=line_height_h value="10pt"}
{assign var=font_size_p value="9px"}
{assign var=font_size_h value="10px"}

<style>
    h4 {
        line-height: {$line_height_h};
        font-size: {$font_size_h};
    }

    p { 
        line-height: {$line_height_p};
        color: {$color_p};
        font-size: {$font_size_p};
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
            <h4>{l s='from' mod='rj_carrier'}</h4>
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
    <tr><td colspan="3" style="border-bottom: 4px solid #777;"></td></tr>
    <tr>
        <td style="width: 14%; border-left: 4px solid #777;">
            <h4>{l s='TO' mod='rj_carrier'}</h4>
        </td>
        <td style="width: 43%;">
            <p>{l s='Transportista' mod='rj_carrier'}cliente</p>
            <h4>{$info_customer.firstname|escape:'html':'UTF-8'} {$info_customer.lastname|escape:'html':'UTF-8'}</h4>
        </td>
        <td style="width: 43%; border-right: 4px solid #777;">
            <p>{l s='teléfonos' mod='rj_carrier'}</p>
            <h4>{$info_customer.phone} - {$info_customer.phone_mobile}</h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 4px solid #777;">
        </td>
        <td colspan="2" style="border-right: 4px solid #777;">
            <p>{l s='dirección' mod='rj_carrier'}</p>
            <h4>{$info_customer.address1|escape:'html':'UTF-8'}</h4>
            <h4>{$info_customer.postcode}
                {if $info_customer.city} - {$info_customer.city}{/if}
                {if $info_customer.state} - {$info_customer.state}{/if}
                {if $info_customer.country} - {$info_customer.country}{/if}
            </h4>
        </td>
    </tr>
    <tr>
        <td style="width: 14%; border-left: 4px solid #777;">
        </td>
        <td colspan="2" style="border-right: 4px solid #777;">
            <p>{l s='mensaje' mod='rj_carrier'}</p>
            <h4>{$info_package.message|escape:'html':'UTF-8'}</h4>
            <br>
        </td>
    </tr>
    <tr><td colspan="3" style="border-top: 4px solid #777;"></td></tr>
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
            <h4>{Tools::displayPrice($info_package.cash_ondelivery)}</h4>
        </td>
    </tr>
</table>