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

                                                    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'rj_carrier_label',
        'primary' => 'id_label',
        'multishop' => true,
        'fields' => array(
            'id_shipment'   => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'package_id'    => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 50),
            'tracker_code'  => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'label_type'    => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'pdf'           => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'print'         => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add'      => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'      => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );

    public function __construct($id_label = null, $id_lang = null, $id_shop = null, \Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier_label', array('type' => 'shop'));
		parent::__construct($id_label, $id_lang, $id_shop);
	}

    public static function getLabelsByIdShipment($id_shipment)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT l.*, s.num_shipment
		FROM `' . _DB_PREFIX_ . 'rj_carrier_label` l
		LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_label_shop` ls ON (ls.`id_label`= l.`id_label`)
		LEFT JOIN `' . _DB_PREFIX_ . 'rj_carrier_shipment` s ON (s.`id_shipment`= l.`id_shipment`)
		WHERE l.`id_shipment` = ' . (int)$id_shipment);
    }

    public static function getPDFsByIdShipment($id_shipment)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT l.id_label, l.package_id, l.pdf
		FROM `' . _DB_PREFIX_ . 'rj_carrier_label` l
		WHERE l.`id_shipment` = ' . (int)$id_shipment);
    }

    public static function getIdsLabelsByIdShipment($id_shipment)
    {
        $ids = [];

        $id_labels = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT l.id_label as id
		FROM `' . _DB_PREFIX_ . 'rj_carrier_label` l
		WHERE l.`id_shipment` = ' . (int)$id_shipment);

        if(!$id_labels) {
            return false;
        }

        foreach ($id_labels as $id_label) {
            $ids[] = $id_label['id'];
        }

        return $ids;
    }

    public static function isPrintedIdShipment($id_shipment)
    {
        $sql = 'SELECT l.`print`
		FROM `' . _DB_PREFIX_ . 'rj_carrier_label` l
		WHERE l.`id_shipment` = ' . (int)$id_shipment .' 
        GROUP BY l.`print`';
        
        $res =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if(isset($res['print'])){
            return $res['print'];
        }
        return false;
    }

    public static function deleteLabelsByIdShipment($id_shipment)
    {
        $id_labels = self::getIdsLabelsByIdShipment($id_shipment);

        foreach ($id_labels as $id_label) {
            $rjcarrierLabel = new RjcarrierLabel((int)$id_label);
            if(!$rjcarrierLabel->delete()){
                return false;
            }
        }

        return true;
    }
}