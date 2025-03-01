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
function upgrade_module_2_0_5($module)
{

    $db = Db::getInstance();

    $queries = [

         // Crear la tabla rj_carrier_configuration si no existe
         'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_configuration` (
            `id_configuration` INT(10) UNSIGNED AUTO_INCREMENT,
            `id_shop_group` INT(11) UNSIGNED NULL DEFAULT NULL,
            `id_shop` INT(11) UNSIGNED NULL DEFAULT NULL,
            `id_carrier_company` INT(11) UNSIGNED NULL DEFAULT NULL,
            `name` VARCHAR(254) NOT NULL,
            `value` TEXT NULL DEFAULT NULL,
            `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_configuration`),
            KEY (`name`),
            KEY (`id_shop`),
            KEY (`id_shop_group`),
            KEY (`id_carrier_company`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;',

        // Agregar índices únicos en tablas de relación
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infoshop_shop` ADD UNIQUE KEY `unique_shop_relation` (`id_infoshop`, `id_shop`);',
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage_shop` ADD UNIQUE KEY `unique_package_relation` (`id_infopackage`, `id_shop`);',

        // Ampliar tamaño de campos de texto
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` MODIFY `request` LONGTEXT NULL DEFAULT NULL;',
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` MODIFY `response` LONGTEXT NULL DEFAULT NULL;',

        // Índices adicionales para mejorar rendimiento
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_type_shipment` ADD INDEX `idx_id_carrier_company` (`id_carrier_company`);',
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD INDEX `idx_id_order` (`id_order`);',

        // Índice único en num_shipment
        'ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD UNIQUE KEY `unique_num_shipment` (`num_shipment`);',
    ];

    foreach ($queries as $query) {
        if (!$db->execute($query)) {
            PrestaShopLogger::addLog('Upgrade 2.0.5 failed: ' . $db->getMsgError(), 3);
            return false;
        }
    }

    if (!$module->installTabs()) {
        return false;
    }
    unset($module);

    return true;
}
