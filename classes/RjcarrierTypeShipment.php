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

class RjcarrierTypeShipment extends ObjectModel
{
    public $id_carrier_company;
    public $name;
    public $id_bc;
    public $id_reference_carrier;
    public $active;
    public $date_add;
	public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'rj_carrier_type_shipment',
        'primary' => 'id_type_shipment',
        'fields' => [
            'id_carrier_company'  => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100, 'required' => true],
            'id_bc'    => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100, 'required' => true],
            'id_reference_carrier'  => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt'],
            'active'         => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    public static function getTypeShipmentsByIdCarrierCompany($id_carrier_company)
    {
        $sql = 'SELECT cts.`id_type_shipment`, cts.`name`, cts.`id_bc`, cts.`active`, cc.`name` as carrier_company, cc.`shortname`, c.`name` as reference_carrier 
        FROM `' . _DB_PREFIX_ . 'rj_carrier_type_shipment` cts
        LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_company` cc ON cts.`id_carrier_company` = cc.`id_carrier_company`
        LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON cts.`id_reference_carrier` = c.`id_reference` AND c.deleted = 0
        WHERE cts.`id_carrier_company` =' . (int)$id_carrier_company;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getTypeShipmentsActiveByIdCarrierCompany($id_carrier_company)
    {
        $sql = 'SELECT cts.`id_type_shipment`, cts.`name`, cts.`id_bc`, cts.`id_reference_carrier`, cts.`active`, cc.`name` as carrier_company, cc.`shortname`, c.`name` as reference_carrier 
        FROM `' . _DB_PREFIX_ . 'rj_carrier_type_shipment` cts
        LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_company` cc ON cts.`id_carrier_company` = cc.`id_carrier_company`
        LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON cts.`id_reference_carrier` = c.`id_reference` AND c.deleted = 0
        WHERE cts.`id_carrier_company` =' . (int)$id_carrier_company .' AND cts.active = 1';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getTypeShipmentsActiveByIdReferenceCarrier($id_reference_carrier)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'rj_carrier_type_shipment` cts
        WHERE cts.`id_reference_carrier` =' . (int)$id_reference_carrier .' AND cts.active = 1';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    public static function typeShipmentExists($id_type_shipment)
    {
        $req = 'SELECT cts.`id_type_shipment`
                FROM `'._DB_PREFIX_.'rj_carrier_type_shipment` cts
                WHERE cts.`id_type_shipment` = '.(int)$id_type_shipment;

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

        return ($row);
    }

    public static function typeShipmentExistsByIdReference($id_reference){
        $req = 'SELECT cts.`id_type_shipment`
                FROM `'._DB_PREFIX_.'rj_carrier_type_shipment` cts
                WHERE cts.`id_reference_carrier` = '.(int)$id_reference .' AND cts.active = 1';

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

        return ($row);
    }
}