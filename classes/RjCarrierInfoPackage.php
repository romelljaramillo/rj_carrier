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

class RjcarrierInfoPackage extends ObjectModel
{
    public $quantity;
    public $weight;
    public $length;
    public $width;
    public $height;
    public $id_shop;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'rj_carrier_infopackage',
        'primary' => 'id_infopackage',
        'multishop' => true,
        'fields' => array(
            'quantity' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'weight' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'length' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'width' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'height' =>		array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'date_add' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        )
    );

    public	function __construct($id_infopackage = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier_infopackage', array('type' => 'shop'));
		parent::__construct($id_infopackage, $id_lang, $id_shop);
	}

    public static function getQuantityById($id_infopackage)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT c.quantity
		FROM `' . _DB_PREFIX_ . 'rj_carrier_infopackage` c
		WHERE c.`id_infopackage` = ' . (int)$id_infopackage);
    }
}