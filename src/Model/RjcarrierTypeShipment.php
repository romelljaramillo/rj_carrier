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
use Validate;

class RjcarrierTypeShipment extends \ObjectModel
{
    public $id_carrier_company;
    public $name;
    public $id_bc;
    public $id_reference_carrier;
    public $active;
    public $date_add;
    public $date_upd;

    const TABLE_NAME = _DB_PREFIX_ . 'rj_carrier_type_shipment';

    /**
     * @var array Definition of the object model
     */
    public static $definition = [
        'table' => 'rj_carrier_type_shipment',
        'primary' => 'id_type_shipment',
        'fields' => [
            'id_carrier_company'  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100, 'required' => true],
            'id_bc' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100, 'required' => true],
            'id_reference_carrier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    /**
     * Get type shipment by ID
     *
     * @param int $id_type_shipment
     * @return array
     */
    public static function getTypeShipmentsByIdCarrierCompany($id_carrier_company)
    {
        if (!Validate::isUnsignedInt($id_carrier_company)) {
            return [];
        }

        $sql = 'SELECT cts.`id_type_shipment`, cts.`name`, cts.`id_bc`, cts.`active`, cc.`name` AS carrier_company,
                       cc.`shortname`, c.`name` AS reference_carrier
                FROM `' . self::TABLE_NAME . '` cts
                LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_company` cc ON cts.`id_carrier_company` = cc.`id_carrier_company`
                LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON cts.`id_reference_carrier` = c.`id_reference` AND c.deleted = 0
                WHERE cts.`id_carrier_company` = ' . (int)$id_carrier_company;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }

    /**
     * Get type shipment by ID carrier company
     *
     * @param int $id_carrier_company
     * @return array
     */
    public static function getTypeShipmentsActiveByIdCarrierCompany($id_carrier_company)
    {
        if (!Validate::isUnsignedInt($id_carrier_company)) {
            return [];
        }

        $sql = 'SELECT cts.`id_type_shipment`, cts.`name`, cts.`id_bc`, cts.`id_reference_carrier`, cts.`active`,
                       cc.`name` AS carrier_company, cc.`shortname`, c.`name` AS reference_carrier
                FROM `' . self::TABLE_NAME . '` cts
                LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_company` cc ON cts.`id_carrier_company` = cc.`id_carrier_company`
                LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON cts.`id_reference_carrier` = c.`id_reference` AND c.deleted = 0
                WHERE cts.`id_carrier_company` = ' . (int)$id_carrier_company . '
                AND cts.active = 1
                AND cts.id_reference_carrier IS NOT NULL
                AND cts.id_reference_carrier != 0';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
    }

    /**
     * Get type shipment by ID reference carrier
     *
     * @param int $id_reference_carrier
     * @return array
     */
    public static function getTypeShipmentsActiveByIdReferenceCarrier($id_reference_carrier)
    {
        if (!Validate::isUnsignedInt($id_reference_carrier)) {
            return null;
        }

        $sql = 'SELECT * FROM `' . self::TABLE_NAME . '`
                WHERE `id_reference_carrier` = ' . (int)$id_reference_carrier . ' AND `active` = 1';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    /**
     * Get type shipment by ID reference carrier
     *
     * @param int $id_reference_carrier
     * @return array
     */
    public static function getTypeShipmentByIdReferenceCarrier($id_reference_carrier)
    {
        if (!Validate::isUnsignedInt($id_reference_carrier)) {
            return null;
        }

        $sql = 'SELECT * FROM `' . self::TABLE_NAME . '`
                WHERE `id_reference_carrier` = ' . (int)$id_reference_carrier;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    /**
     * Get type shipment by ID
     *
     * @param int $id_type_shipment
     * @return array
     */
    public static function typeShipmentExists($id_type_shipment)
    {
        if (!Validate::isUnsignedInt($id_type_shipment)) {
            return false;
        }

        $sql = 'SELECT 1
                FROM `' . self::TABLE_NAME . '`
                WHERE `id_type_shipment` = ' . (int)$id_type_shipment;

        return (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Get type shipment by ID reference carrier
     *
     * @param int $id_reference_carrier
     * @return array
     */
    public static function typeShipmentExistsByIdReference($id_reference_carrier)
    {
        if (!Validate::isUnsignedInt($id_reference_carrier)) {
            return false;
        }

        $sql = 'SELECT 1
                FROM `' . self::TABLE_NAME . '`
                WHERE `id_reference_carrier` = ' . (int)$id_reference_carrier . ' AND `active` = 1';

        return (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}
