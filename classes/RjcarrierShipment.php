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

class RjcarrierShipment extends ObjectModel
{
    public $id_order;
    public $num_shipment;
    public $reference_order;
    public $id_carrier_company;
    public $id_infopackage;
    public $account;
    public $product;
    public $request;
    public $response;
    public $delete;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'rj_carrier_shipment',
        'primary' => 'id_shipment',
        'multishop' => true,
        'fields' => [
            'id_order'      => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'reference_order' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'num_shipment'    => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'id_carrier_company' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt'],
            'id_infopackage'  => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'account'       => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'product'       => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'request' =>	['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'response' =>	['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'delete'         => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ]
    ];

    public	function __construct($id_shipment = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier_shipment', ['type' => 'shop']);
		parent::__construct($id_shipment, $id_lang, $id_shop);
	}

    public static function getShipmentByIdOrder($id_order)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT cs.*
		FROM `' . _DB_PREFIX_ . 'rj_carrier_shipment` cs
		WHERE cs.`id_order` = ' . (int) $id_order . ' 
        AND cs.`delete` = 0' );

        if($res['name_carrier']  == '0'){
            $res['name_carrier'] = Carrier::getCarrierNameFromShopName();
        }

        return $res;
    }

    public static function getNumShipmentByIdOrder($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.num_shipment
		FROM `' . _DB_PREFIX_ . 'rj_carrier_shipment` c
		WHERE c.`id_order` = ' . (int) $id_order . ' AND c.`delete` = 0');
    }

    public static function getIdByIdOrder($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.id_shipment
		FROM `' . _DB_PREFIX_ . 'rj_carrier_shipment` c
		WHERE c.`id_order` = ' . (int) $id_order . ' AND c.`delete` = 0');
    }

    public static function getIdInfoPackageByIdOrder($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.id_infopackage
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

    public function getInfoShipmentContrareembolso($echo, $tr)
    {
        $rjcarrierInfoPackage = new RjcarrierInfoPackage((int)$tr['id_infopackage']);

        return Tools::displayPrice($rjcarrierInfoPackage->cash_ondelivery);
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