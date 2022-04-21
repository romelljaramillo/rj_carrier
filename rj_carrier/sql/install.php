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
    `id_infoshop` int(11) NOT NULL AUTO_INCREMENT,
    `firstname` varchar(100) NOT NULL,
    `lastname` varchar(100) NULL,
    `company` varchar(100) NULL,
    `additionalname` varchar(100) NULL,
    `id_country` INT(10) UNSIGNED NOT NULL,
	`state` varchar(255) NULL,
	`city` varchar(255) NULL,
    `street` varchar(255) NOT NULL,
    `number` varchar(10) NULL,
    `postcode` varchar(10) NULL,
    `additionaladdress` varchar(100) NULL,
    `isbusiness` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `email` varchar(150) NULL,
    `phone` varchar(20) NULL,
    `vatnumber` varchar(20) NULL,
    `date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_infoshop`),
    INDEX `id_country` (`id_country`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_infoshop_shop` (
    `id_infoshop` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_infoshop`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_infopackage` (
    `id_infopackage` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order` INT(10) UNSIGNED NOT NULL,
    `id_reference_carrier` INT(10) UNSIGNED NOT NULL,
	`id_type_shipment` INT(10) UNSIGNED NOT NULL,
	`quantity` INT(10) UNSIGNED NOT NULL DEFAULT 1,
	`weight` DECIMAL(20,6) NULL DEFAULT NULL,
	`length` DECIMAL(20,6) NULL DEFAULT NULL,
	`width` DECIMAL(20,6) NULL DEFAULT NULL,
	`height` DECIMAL(20,6) NULL DEFAULT NULL,
    `cash_ondelivery` DECIMAL(20,6) NULL DEFAULT \'0.000000\',
	`message` VARCHAR(255) NULL,
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
	`id_carrier_company` INT(10) UNSIGNED NOT NULL,
	`id_infopackage` INT(10) UNSIGNED NOT NULL,
	`account` VARCHAR(100),
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
    `id_carrier_company` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `shortname` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
    `icon` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_carrier_company`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_type_shipment` (
    `id_type_shipment` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_carrier_company` INT(10) UNSIGNED NOT NULL,
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
        return false;
    }
}
