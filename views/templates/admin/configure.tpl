{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{block name='notifications'}
{include file='../hook/notifications.tpl'}
{/block}

<div id="modulecontent module_display" class="clearfix">
    <div id="menu-config-rj-carrier" class="col-lg-2 col-xs-2">
        <div class="list-group nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
            <a href="#form_info_shop" data-toggle="pill" class="list-group-item nav-link" role="tab" aria-controls="form_info_shop" aria-selected="{if !$tab || $tab == 'infoshop'}true{else}false{/if}">
                <i class="fa fa-cog"></i> {l s='Global settings' d='Modules.rj_carrier.Admin'}
            </a>
			{foreach from=$form_config_carriers item=item key=key}
            <a href="#form_config_carriers-{$key}" data-toggle="pill" class="list-group-item nav-link" role="tab" aria-controls="form_config_carriers-{$key}" aria-selected="{if $tab == $key|upper}true{else}false{/if}">
                <i class="fa fa-book"></i> {l s='Configuration' d='Modules.rj_carrier.Admin'} {$key|upper}
            </a>
			{/foreach}
            <a href="#form_info_extra" data-toggle="pill" class="list-group-item nav-link" role="tab" aria-controls="form_info_extra" aria-selected="{if $tab == 'infoextra'}true{else}false{/if}">
                <i class="fa fa-clock-o"></i> {l s='Configuration extra' d='Modules.rj_carrier.Admin'}
            </a>
        </div>
    </div>
    <div class="col-lg-9 col-xs-9">
		<div class="tab-content" id="config-tabContent">
			<div id="form_info_shop" class="tab-pane fade {if !$tab || $tab == 'infoshop'}active in{/if}" role="tabpanel" aria-labelledby="form_info_shop-tab">
				{$form_info_shop}
			</div>
			{foreach from=$form_config_carriers item=item key=key}
			<div id="form_config_carriers-{$key}" class="tab-pane fade {if $tab == $key|upper}active in{/if}" role="tabpanel" aria-labelledby="form_config_carriers-{$key}-tab">
				{$item}
			</div>
			{/foreach}
			<div id="form_info_extra" class="tab-pane fade {if $tab == 'infoextra'}active in{/if}" role="tabpanel" aria-labelledby="form_info_extra-tab">
				{$form_info_extra}
			</div>
		</div>
    </div>

</div>