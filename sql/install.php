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
    `addition` varchar(100) NULL,
    `phone` varchar(20) NULL,
    `vatnumber` varchar(20) NULL,
    `date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_infoshop`),
    INDEX `id_country` (`id_country`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_infoshop_shop` (
    `id_infoshop` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_infoshop`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_infopackage` (
    `id_infopackage` INT(11) NOT NULL AUTO_INCREMENT,
	`quantity` INT(10) UNSIGNED NOT NULL DEFAULT \'1\',
	`weight` DECIMAL(20,6) NULL DEFAULT NULL,
	`length` DECIMAL(20,6) NULL DEFAULT NULL,
	`width` DECIMAL(20,6) NULL DEFAULT NULL,
	`height` DECIMAL(20,6) NULL DEFAULT NULL,
	`parcel_type` VARCHAR(100) NULL DEFAULT NULL,
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_infopackage`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_infopackage_shop` (
    `id_infopackage` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_infopackage`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_shipment` (
    `id_shipment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_order` INT(10) NOT NULL,
	`reference_order` VARCHAR(100) NOT NULL,
	`num_shipment` CHAR(36) NOT NULL,
	`id_carrier_company` INT(10) NOT NULL,
	`id_infopackage` INT(10) NOT NULL,
	`id_reference_carrier` INT(10) NOT NULL,
	`account` VARCHAR(100) NOT NULL,
	`product` VARCHAR(100) NOT NULL,
	`cash_ondelivery` DECIMAL(20,6) NULL DEFAULT \'0.000000\',
	`message` VARCHAR(255) NULL DEFAULT \'0\',
	`request` TEXT NULL DEFAULT NULL,
	`response` TEXT NULL DEFAULT NULL,
	`delete` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
	`hour_from` TIME NULL DEFAULT NULL,
	`hour_until` TIME NULL DEFAULT NULL,
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
	`pdf` BLOB NULL DEFAULT NULL,
	`print` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
	`date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_label`),
	INDEX `id_shipment` (`id_shipment`, `package_id`, `tracker_code`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_label_shop` (
    `id_label` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_label`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_company` (
    `id_carrier_company` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `shortname` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
    `icon` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
	`date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_carrier_company`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'rj_carrier_company` (`id_carrier_company`, `name`, `shortname`, `icon`) VALUES
	(1, \'DHL\', \'DHL\', NULL),
	(2, \'Correo Express\', \'CEX\', NULL);';
    
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
