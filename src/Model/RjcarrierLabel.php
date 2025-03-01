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

use Shop;
use Db;
use Context;
use Validate;

class RjcarrierLabel extends \ObjectModel
{
    public $id_shipment;
    public $package_id;
    public $tracker_code;
    public $label_type;
    public $pdf;
    public $print;
    public $date_add;
    public $date_upd;
    public $id_shop;

    const TABLE_NAME = _DB_PREFIX_ . 'rj_carrier_label';
    const TABLE_SHOP = _DB_PREFIX_ . 'rj_carrier_label_shop';

    public static $definition = [
        'table' => 'rj_carrier_label',
        'primary' => 'id_label',
        'multishop' => true,
        'fields' => [
            'id_shipment' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'package_id' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50],
            'tracker_code' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100],
            'label_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100],
            'pdf' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
            'print' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    public function __construct($id_label = null, $id_lang = null, $id_shop = null, \Context $context = null)
    {
        Shop::addTableAssociation('rj_carrier_label', ['type' => 'shop']);
        parent::__construct($id_label, $id_lang, $id_shop);
    }

    public static function getLabelsByIdShipment($id_shipment, $id_shop = null)
    {
        if (!Validate::isUnsignedInt($id_shipment)) {
            return [];
        }

        $id_shop = $id_shop ?: (int)Context::getContext()->shop->id;

        if (!Validate::isUnsignedInt($id_shop)) {
            return false;
        }

        $sql = 'SELECT l.*, s.num_shipment
                FROM `' . self::TABLE_NAME . '` l
                LEFT JOIN `' . self::TABLE_SHOP . '` ls ON ls.`id_label` = l.`id_label`
                LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_shipment` s ON s.`id_shipment` = l.`id_shipment`
                WHERE l.`id_shipment` = ' . (int)$id_shipment . '
                AND ls.`id_shop` = ' . (int)$id_shop;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getPDFsByIdShipment($id_shipment)
    {
        if (!Validate::isUnsignedInt($id_shipment)) {
            return [];
        }

        $sql = 'SELECT l.id_label, l.package_id, l.pdf
                FROM `' . self::TABLE_NAME . '` l
                WHERE l.`id_shipment` = ' . (int)$id_shipment;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getIdsLabelsByIdShipment($id_shipment)
    {
        if (!Validate::isUnsignedInt($id_shipment)) {
            return [];
        }

        $sql = 'SELECT l.id_label as id
                FROM `' . self::TABLE_NAME . '` l
                WHERE l.`id_shipment` = ' . (int)$id_shipment;

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return array_column($results, 'id');
    }

    public static function isPrintedIdShipment($id_shipment)
    {
        if (!Validate::isUnsignedInt($id_shipment)) {
            return false;
        }

        $sql = 'SELECT l.`print`
                FROM `' . self::TABLE_NAME . '` l
                WHERE l.`id_shipment` = ' . (int)$id_shipment . '
                LIMIT 1';

        return (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public static function deleteLabelsByIdShipment($id_shipment)
    {
        if (!Validate::isUnsignedInt($id_shipment)) {
            return false;
        }

        $db = Db::getInstance();

        // Delete from the shop table first
        $db->delete(
            'rj_carrier_label_shop',
            'id_label IN (SELECT id_label FROM ' . self::TABLE_NAME . ' WHERE id_shipment = ' . (int)$id_shipment . ')'
        );

        // Then delete from the main table
        return $db->delete(
            'rj_carrier_label',
            'id_shipment = ' . (int)$id_shipment
        );
    }
}
