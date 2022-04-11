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
function upgrade_module_2_0_0($module)
{
    /*
     * Do everything you want right there,
     * You could add a column in one of your module's tables
     */
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_company` (
            `id_carrier_company` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            `shortname` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
            `icon` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_carrier_company`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';
    
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_type_shipment` (
            `id_type_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_carrier_company` INT(10) NOT NULL,
            `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            `id_bc` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            `id_reference_carrier` INT(10) DEFAULT NULL,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_type_shipment`),
            INDEX `id_type_shipment` (`id_type_shipment`,`id_carrier_company`,`id_reference_carrier`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

    $sql[] = 'INSERT INTO `'._DB_PREFIX_.'rj_carrier_company` (`id_carrier_company`, `name`, `shortname`, `icon`) VALUES
        (1, \'Default Carrier\', \'DEF\', NULL),
        (2, \'DHL\', \'DHL\', NULL),
        (3, \'Correo Express\', \'CEX\', NULL);';
    
    $sql[] = 'INSERT INTO `'._DB_PREFIX_.'rj_carrier_type_shipment` (`id_type_shipment`, `id_carrier_company`, `name`, `id_bc`, `id_reference_carrier`, `active`) VALUES
        (1,3,\'PAQ 10\',61,NULL,0),
        (2,3,\'PAQ 14\',62,NULL,0),
        (3,3,\'PAQ 24\',63,NULL,0),
        (4,3,\'Baleares\',66,NULL,0),
        (5,3,\'Canarias Express\',67,NULL,0),
        (6,3,\'Canarias Aéreo\',68,NULL,0),
        (7,3,\'Canarias Marítimo\',69,NULL,0),
        (8,3,\'CEX Portugal Óptica\',73,NULL,0),
        (9,3,\'Paquetería Óptica\',76,NULL,0),
        (10,3,\'Internacional Express\',91,NULL,0),
        (11,3,\'Internacional Estandard\',90,NULL,0),
        (12,3,\'Paq Empresa 14\',92,NULL,0),
        (13,3,\'ePaq 24\',93,NULL,0),
        (14,3,\'Campaña CEX\',27,NULL,0),
        (15,3,\'Entrega en Oficina\',44,NULL,0),
        (16,3,\'Entrega + Recogida Multichrono\',54,NULL,0),
        (17,3,\'Entrega + recogida + Manip Multichrono\',55,NULL,0),
        (18,2,\'DHL PARCEL IBERIA\', \'IBERIA\',NULL,0);';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return Db::getInstance()->getMsgError();
        }
    }

    // upgrade rj_carrier_infopackage
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` DROP COLUMN `print`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` ADD COLUMN `id_type_shipment` INT(10) NULL DEFAULT 2 AFTER `id_reference_carrier`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` CHANGE COLUMN `id_type_shipment` `id_type_shipment` INT(10) NULL DEFAULT NULL AFTER `id_reference_carrier`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` ADD COLUMN `hour_from` TIME NULL DEFAULT NULL AFTER `message`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` ADD COLUMN `hour_until` TIME NULL DEFAULT NULL AFTER `hour_from`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` CHANGE COLUMN `packages` `quantity` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id_type_shipment`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` CHANGE COLUMN `price_contrareembolso` `cash_ondelivery` DECIMAL(20,6) NULL DEFAULT \'0.000000\' AFTER `quantity`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_infopackage` ADD INDEX `id_type_shipment` (`id_type_shipment`)');

    // upgrade rj_infoshop
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_infoshop` DROP COLUMN `eorinumber`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_infoshop` DROP COLUMN `addition`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_infoshop` DROP COLUMN `countrycode`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_infoshop` ADD COLUMN `id_country` INT(10) UNSIGNED NOT NULL AFTER `additionalname`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_infoshop` ADD COLUMN `state` varchar(255) NULL AFTER `id_country`');
    Db::getInstance()->execute('RENAME TABLE `' . _DB_PREFIX_ . 'rj_infoshop` TO `ps_rj_carrier_infoshop`');
    Db::getInstance()->execute('RENAME TABLE `' . _DB_PREFIX_ . 'rj_infoshop_shop` TO `ps_rj_carrier_infoshop_shop`');

    // upgrade rj_carrier_shipment
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` CHANGE COLUMN `id_order` `id_order` INT(10) UNSIGNED NOT NULL AFTER `id_shipment`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` CHANGE COLUMN `order_reference` `reference_order` VARCHAR(100) NOT NULL AFTER `id_order`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` CHANGE COLUMN `shipmentid` `num_shipment` CHAR(36) NOT NULL AFTER `reference_order`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD COLUMN `id_carrier_company` INT(10) UNSIGNED NOT NULL DEFAULT 2 AFTER `num_shipment`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` CHANGE COLUMN `id_infopackage` `id_infopackage` INT(10) UNSIGNED NOT NULL AFTER `id_carrier_company`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` CHANGE COLUMN `id_carrier_company` `id_carrier_company` INT(10) NOT NULL AFTER `num_shipment`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD COLUMN `account` VARCHAR(100) AFTER `id_infopackage`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD COLUMN `request` TEXT NULL AFTER `product`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD COLUMN `response` TEXT NULL AFTER `request`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` DROP INDEX `id_shipment`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_shipment` ADD INDEX `id_shipment` (`id_shipment`, `id_order`, `id_infopackage`, `num_shipment`)');

    // upgrade rj_carrier_label
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` CHANGE COLUMN `labelid` `package_id` VARCHAR(100) NOT NULL AFTER `id_shipment`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` CHANGE COLUMN `pdf` `pdf` MEDIUMBLOB NULL DEFAULT NULL AFTER `label_type`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` DROP COLUMN `parcel_type`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` DROP COLUMN `piece_number`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` DROP COLUMN `routing_code`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` DROP COLUMN `userid`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` DROP COLUMN `organizationid`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` DROP COLUMN `order_reference`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'rj_carrier_label` ADD INDEX `id_label` (`id_label`, `id_shipment`, `package_id`, `tracker_code`)');

    unset($module);

    return true;
}
