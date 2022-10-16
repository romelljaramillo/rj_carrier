<?php
/*
* 2007-2016 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
namespace Roanja\Module\RjCarrier\Model;

use Db;
use Shop;
use Context;

class RjcarrierInfoPackage extends \ObjectModel
{
    public $id_order;
    public $id_reference_carrier;
    public $id_type_shipment;
    public $quantity;
    public $weight;
    public $length;
    public $width;
    public $height;
    public $id_shop;
    public $cash_ondelivery;
    public $message;
    public $hour_from;
    public $hour_until;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'rj_carrier_infopackage',
        'primary' => 'id_infopackage',
        'multishop' => true,
        'fields' => [
            'id_order'   => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'id_reference_carrier'  => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'id_type_shipment'  => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'quantity' =>	['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'weight' =>		['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'length' =>		['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'width' =>		['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'height' =>		['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'cash_ondelivery' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'message' =>	['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'hour_from' =>  ['type' => self::TYPE_NOTHING],
            'hour_until' => ['type' => self::TYPE_NOTHING],
            'date_delivery' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_delivery_from' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_delivery_to' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_add' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    public	function __construct($id_infopackage = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier_infopackage', ['type' => 'shop']);
		parent::__construct($id_infopackage, $id_lang, $id_shop);
	}

    public static function getQuantityById($id_infopackage)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.quantity
		FROM `' . _DB_PREFIX_ . 'rj_carrier_infopackage` c
		WHERE c.`id_infopackage` = ' . (int)$id_infopackage);
    }

    public static function getPackageByIdOrder($id_order, $id_shop)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT *
		FROM `' . _DB_PREFIX_ . 'rj_carrier_infopackage` p
        LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_infopackage_shop` ps
        ON p.`id_infopackage` = ps.`id_infopackage`
		WHERE p.`id_order` = ' . (int)$id_order .'
        AND ps.`id_shop` = ' . (int)$id_shop);
    }

}