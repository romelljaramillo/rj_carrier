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

class RjCarrier extends ObjectModel
{
    public $id_order;
    public $id_carrier;
    public $packages;
    public $weight;
    public $length;
    public $width;
    public $height;
    public $message;
    public $print;
    public $id_shop;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'rj_carrier',
        'primary' => 'id_rjcarrier',
        'multishop' => true,
        'fields' => array(
            'id_order' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'id_reference_carrier' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'packages' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'weight' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'length' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'width' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'height' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'message' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'print' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );

    public	function __construct($id_rjcarrier = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier', array('type' => 'shop'));
		parent::__construct($id_rjcarrier, $id_lang, $id_shop);
	}

    public static function getDataPackage($id_order)
	{
        $resul = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
                SELECT tr.`id_rjcarrier` as id, 
                tr.`id_reference_carrier`,
                tr.`packages`,
                tr.`weight`,
                tr.`length`,
                tr.`width`,
                tr.`weight`,
                tr.`height`,
                tr.`message`,
                tr.`print`
				FROM `'._DB_PREFIX_.'rj_carrier` tr
				WHERE tr.`id_order` = '.(int)$id_order);
		return $resul;
	}
}