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
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierLabel.php');
class RjcarrierShipment extends ObjectModel
{
    public $shipmentid;
    public $id_rjcarrier;
    public $id_order;
    public $product;
    public $order_reference;
    public $delete;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'rj_carrier_shipment',
        'primary' => 'id_shipment',
        'multishop' => true,
        'fields' => array(
            'shipmentid'    => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 36),
            'id_order'      => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'id_rjcarrier'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'product'       => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'order_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'delete'         => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );

    public	function __construct($id_shipment = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier_shipment', array('type' => 'shop'));
		parent::__construct($id_shipment, $id_lang, $id_shop);
	}

    public static function getShipmentByIdOrder($id_order)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT cs.*, ca.name as name_carrier
		FROM `' . _DB_PREFIX_ . 'rj_carrier_shipment` cs
		INNER JOIN `' . _DB_PREFIX_ . 'rj_carrier_shipment_shop` css ON cs.`id_shipment`= css.`id_shipment`
		INNER JOIN `' . _DB_PREFIX_ . 'rj_carrier` c ON cs.`id_rjcarrier`= c.`id_rjcarrier`
		INNER JOIN `' . _DB_PREFIX_ . 'carrier` ca ON c.`id_reference_carrier`= ca.`id_reference` AND ca.`deleted`= 0
		WHERE cs.`id_order` = ' . (int) $id_order . ' 
        AND css.`id_shop`=' . (int) Context::getContext()->shop->id . '
        AND cs.`delete` = 0' );

        if($res['name_carrier']  == '0'){
            $res['name_carrier'] = Carrier::getCarrierNameFromShopName();
        }

        return $res;
    }

    public static function getShipmentIdByIdOrder($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.shipmentid
		FROM `' . _DB_PREFIX_ . 'rj_carrier_shipment` c
		WHERE c.`id_order` = ' . (int) $id_order . ' AND c.`delete` = 0');
    }

    public static function getIdShipmentByIdOrder($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.id_shipment
		FROM `' . _DB_PREFIX_ . 'rj_carrier_shipment` c
		WHERE `id_order` = ' . (int) $id_order . ' AND c.`delete` = 0');
    }

    public static function shipmentExists($id_shipment)
    {
        $req = 'SELECT hs.`id_shipment` as id_shipment
                FROM `'._DB_PREFIX_.'rj_carrier_shipment` hs
                WHERE hs.`id_shipment` = '.(int)$id_shipment;
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

        return ($row);
    }

    public function delete()
	{
        $this->delete = true;
        $order = new Order($this->id_order);
        if($order->valid){
            return false;
        }
        if(!$this->update())
            return false;
        return true;
	}
}