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

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_infopackage` (
    `id_infopackage` int(11) NOT NULL AUTO_INCREMENT,
    `id_order` int(10) NOT NULL,
    `id_reference_carrier` int(10) NOT NULL,
    `packages` int(10) unsigned NOT NULL DEFAULT \'1\',
    `price_contrareembolso` DECIMAL(20,6) NULL,
    `weight` decimal(20,6) NULL,
    `length` decimal(20,6) NULL,
    `width` decimal(20,6) NULL,
    `height` decimal(20,6) NULL,
    `message` varchar(255) NULL,
    `print` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_infopackage`),
    INDEX `id_order` (`id_order`) USING BTREE,
    INDEX `id_reference_carrier` (`id_reference_carrier`) USING BTREE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_infopackage_shop` (
    `id_infopackage` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_infopackage`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_infoshop` (
    `id_infoshop` int(11) NOT NULL AUTO_INCREMENT,
    `firstname` varchar(100) NOT NULL,
    `lastname` varchar(100) NULL,
    `company` varchar(100) NULL,
    `additionalname` varchar(100) NULL,
    `countrycode` varchar(5) NULL,
    `city` varchar(255) NOT NULL,
    `street` varchar(255) NOT NULL,
    `number` varchar(10) NULL,
    `postcode` varchar(10) NULL,
    `additionaladdress` varchar(100) NULL,
    `isbusiness` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `email` varchar(150) NULL,
    `addition` varchar(100) NULL,
    `phone` varchar(20) NULL,
    `vatnumber` varchar(20) NULL,
    `eorinumber` varchar(20) NULL,
    `date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_infoshop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_infoshop_shop` (
    `id_infoshop` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_infoshop`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_shipment` (
    `id_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `shipmentid` CHAR(36) NOT NULL,
    `id_infopackage` int(10) NOT NULL,
    `id_order` int(10) NOT NULL,
    `product` varchar(100) NOT NULL,
    `order_reference` varchar(100) NOT NULL,
    `delete` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
    INDEX ( `id_shipment` , `id_order`, `id_infopackage`, `shipmentid`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_shipment_shop` (
    `id_shipment` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_shipment`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rj_carrier_label` (
    `id_label` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_shipment` INT(11) UNSIGNED NOT NULL,
    `labelid` CHAR(36) NOT NULL,
    `tracker_code` varchar(100) NOT NULL,
    `parcel_type` varchar(100) NOT NULL,
    `piece_number` int(10) NOT NULL,
    `label_type` varchar(100) NOT NULL,
    `routing_code` varchar(100) NOT NULL,
    `userid` CHAR(36) NOT NULL,
    `organizationid` CHAR(36) NOT NULL,
    `order_reference` varchar(100) NOT NULL,
    `pdf` BLOB,
    `print` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
    INDEX ( `id_shipment` , `labelid`, `tracker_code`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rj_carrier_label_shop` (
    `id_label` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_label`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
