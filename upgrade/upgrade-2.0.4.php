<?php
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
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_2_0_4($module)
{

    // Insertar en rj_carrier_company
    $sqlA = 'INSERT INTO `'._DB_PREFIX_.'rj_carrier_company` (`name`, `shortname`, `icon`) VALUES (\'GLS\', \'GLS\', NULL);';
    if (Db::getInstance()->execute($sqlA) == false) {
        return false;
    }

    // Obtener el ID del último registro insertado
    $id_carrier_company = Db::getInstance()->Insert_ID();

    $services = [
        ['service_type' => 'GLS10', 'service_id' => 1],
        ['service_type' => 'GLS14', 'service_id' => 1],
        ['service_type' => 'GLS24', 'service_id' => 1],
        ['service_type' => 'BUSPAR', 'service_id' => 96],
        ['service_type' => 'ECONOMY', 'service_id' => 37],
        ['service_type' => 'EUROBUSINESSPARCEL', 'service_id' => 74],
        ['service_type' => 'SHOPDELINT', 'service_id' => 74],
        ['service_type' => 'PARCELS', 'service_id' => 1],
    ];

    foreach ($services as $service) {
        $sql[] = 'INSERT INTO `'._DB_PREFIX_.'rj_carrier_type_shipment` (`id_carrier_company`, `name`, `id_bc`, `active`) VALUES
                ('.(int)$id_carrier_company.', \'' . pSQL($service['service_type']) . '\', \''. $service['service_id'] . '\', 0);';
    }

    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage`
              ADD COLUMN `retorno` INT UNSIGNED DEFAULT 0 AFTER `hour_until`,
              ADD COLUMN `rcs` TINYINT(1) DEFAULT 0 AFTER `retorno`,
              ADD COLUMN `vsec` FLOAT DEFAULT 0.0 AFTER `rcs`,
              ADD COLUMN `dorig` VARCHAR(100) DEFAULT "" AFTER `vsec`;';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    unset($module);

    return true;
}
