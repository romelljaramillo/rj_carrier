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
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_infoshop` (
    `id_infoshop` INT(11) NOT NULL AUTO_INCREMENT,
	`firstname` VARCHAR(100) NOT NULL,
	`lastname` VARCHAR(100) NULL DEFAULT NULL,
	`company` VARCHAR(100) NULL DEFAULT NULL,
	`additionalname` VARCHAR(100) NULL DEFAULT NULL,
	`id_country` INT(10) UNSIGNED NOT NULL,
	`state` VARCHAR(255) NULL DEFAULT NULL,
	`city` VARCHAR(255) NOT NULL,
	`street` VARCHAR(255) NOT NULL,
	`number` VARCHAR(10) NULL DEFAULT NULL,
	`postcode` VARCHAR(10) NULL DEFAULT NULL,
	`additionaladdress` VARCHAR(100) NULL DEFAULT NULL,
	`isbusiness` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
	`email` VARCHAR(150) NULL DEFAULT NULL,
	`phone` VARCHAR(20) NULL DEFAULT NULL,
	`vatnumber` VARCHAR(20) NULL DEFAULT NULL,
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_infoshop`),
    INDEX `id_country` (`id_country`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_infoshop_shop` (
    `id_infoshop` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_infoshop`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_infopackage` (
    `id_infopackage` INT(11) NOT NULL AUTO_INCREMENT,
	`id_order` INT(10) NOT NULL,
	`id_reference_carrier` INT(10) NOT NULL,
	`id_type_shipment` INT(10) NULL DEFAULT NULL,
	`quantity` INT(10) UNSIGNED NOT NULL DEFAULT 1,
	`cash_ondelivery` DECIMAL(20,6) NULL DEFAULT \'0.000000\',
	`weight` DECIMAL(20,6) NULL DEFAULT NULL,
	`length` DECIMAL(20,6) NULL DEFAULT NULL,
	`width` DECIMAL(20,6) NULL DEFAULT NULL,
	`height` DECIMAL(20,6) NULL DEFAULT NULL,
	`message` VARCHAR(255) NULL DEFAULT NULL,
	`hour_from` TIME NULL DEFAULT NULL,
	`hour_until` TIME NULL DEFAULT NULL,
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_infopackage`),
    INDEX `id_infopackage` (`id_infopackage`, `id_order`, `id_reference_carrier`, `id_type_shipment`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_infopackage_shop` (
    `id_infopackage` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_infopackage`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_shipment` (
    `id_shipment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_order` INT(10) UNSIGNED NOT NULL,
	`reference_order` VARCHAR(100) NOT NULL,
	`num_shipment` CHAR(36) NOT NULL,
	`id_carrier_company` INT(10) NOT NULL,
	`id_infopackage` INT(10) UNSIGNED NOT NULL,
	`account` VARCHAR(100) NULL DEFAULT NULL,
	`product` VARCHAR(100) NOT NULL,
	`request` TEXT NULL DEFAULT NULL,
	`response` TEXT NULL DEFAULT NULL,
	`delete` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_shipment`),
	INDEX `id_shipment` (`id_shipment`, `id_order`, `id_infopackage`, `num_shipment`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_shipment_shop` (
    `id_shipment` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_shipment`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_label` (
    `id_label` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_shipment` INT(11) UNSIGNED NOT NULL,
	`package_id` VARCHAR(100) NOT NULL,
	`tracker_code` VARCHAR(100) NOT NULL,
	`label_type` VARCHAR(100) NOT NULL,
	`pdf` MEDIUMBLOB NULL DEFAULT NULL,
	`print` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_label`),
	INDEX `id_shipment` (`id_shipment`, `package_id`, `tracker_code`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_label_shop` (
    `id_label` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_label`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_company` (
    `id_carrier_company` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`shortname` VARCHAR(4) NOT NULL,
	`icon` VARCHAR(250) NULL DEFAULT NULL,
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_carrier_company`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_type_shipment` (
    `id_type_shipment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_carrier_company` INT(10) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	`id_bc` VARCHAR(50) NOT NULL,
	`id_reference_carrier` INT(10) NULL DEFAULT NULL,
	`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_type_shipment`),
    INDEX `id_type_shipment` (`id_type_shipment`,`id_carrier_company`,`id_reference_carrier`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_log` (
    `id_carrier_log` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order` INT(10) UNSIGNED NOT NULL,
	`name` VARCHAR(250) NOT NULL,
	`request` TEXT NULL DEFAULT NULL,
	`response` TEXT NULL DEFAULT NULL,
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_carrier_log`),
    INDEX `id_order` (`id_order`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'rj_carrier_company` (`id_carrier_company`, `name`, `shortname`, `icon`) VALUES
	(1, \'Default Carrier\', \'DEF\', NULL),
	(2, \'DHL\', \'DHL\', NULL),
	(3, \'Correo Express\', \'CEX\', NULL),
	(4, \'GOI\', \'GOI\', NULL);';

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
    (18,2,\'DHL PARCEL IBERIA\', \'IBERIA\',NULL,0),
    (19, 4, \'Goi carrier\', \'T,M\', 2, 0),
    (20, 4, \'GOI - Montaje\', \'T,I,M\', 1, 0);';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
