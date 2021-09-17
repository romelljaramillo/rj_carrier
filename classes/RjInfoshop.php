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

class RjInfoshop extends ObjectModel
{
    public $firstname;
    public $lastname;
    public $company;
    public $additionalname;
    public $countrycode;
    public $city;
    public $street;
    public $number;
    public $postcode;
    public $additionaladdress;
    public $isbusiness;
    public $addition;
    public $email;
    public $phone;
    public $vatnumber;
    public $eorinumber;
    public $date_add;
    public $date_upd;
    public $id_shop;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'rj_infoshop',
        'primary' => 'id_infoshop',
        'multishop' => true,
        'fields' => array(
            'firstname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'lastname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'company' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'additionalname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'countrycode' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'city' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'street' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'number' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'postcode' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'additionaladdress' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'isbusiness' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'addition' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'phone' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'vatnumber' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'eorinumber' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'date_add' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat']
        )
    );

    public function __construct($id_infoshop = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_infoshop', array('type' => 'shop'));
		parent::__construct($id_infoshop, $id_lang, $id_shop);
	}

    public static function getInfoShopID()
    {
        $sql = 'SELECT hs.`id_infoshop`
        FROM `'._DB_PREFIX_.'rj_infoshop` hs';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return ($row);
    }

}