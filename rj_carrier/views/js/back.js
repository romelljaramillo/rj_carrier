/**
* 2007-2021 PrestaShop
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
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/


function saveRjTransport(){
    var data = {
        "rj_carrier" : $("#rj_carrier").val(),
        "rj_quantity" : $("#rj_quantity").val(),
        "rj_weight" : $("#rj_weight").val(),
        "rj_message" : $("#rj_message").val(),
        "action"	: "submitFormPackageCarrier"
    }
	// v"ar noteContent = $('#noteContent').val();
	// var data = 'token=' + 
    // token_admin_customers + 
    // '&tab=AdminCustomers&ajax=1&action=updateCustomerNote&id_customer=' + 
    // customerId + 
    // '&note=' + 
    // encodeURIComponent(noteContent);
	$.ajax({
		type: "POST",
		url: "index.php",
		data: data,
		async : true,
		success: function(r) {

			if (r == 'ok') {
				// $('#submitCustomerNote').attr('disabled', true);
			}
			showSuccessMessage(update_success_msg);
		}
	});
}