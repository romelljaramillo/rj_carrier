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

class RjcarrierInfoshop extends ObjectModel
{
    public $firstname;
    public $lastname;
    public $company;
    public $additionalname;
    public $id_country;
    public $state;
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
    public $date_add;
    public $date_upd;
    public $id_shop;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'rj_carrier_infoshop',
        'primary' => 'id_infoshop',
        'multishop' => true,
        'fields' => array(
            'firstname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'lastname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'company' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'additionalname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'id_country' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'state' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'city' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'street' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'number' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'postcode' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'additionaladdress' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'isbusiness' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'addition' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'phone' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'vatnumber' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'date_add' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' =>   ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat']
        )
    );

    public function __construct($id_infoshop = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
        Shop::addTableAssociation('rj_carrier_infoshop', array('type' => 'shop'));
		parent::__construct($id_infoshop, $id_lang, $id_shop);
	}

    public static function getInfoShopID()
    {
        $sql = 'SELECT hs.`id_infoshop`
        FROM `'._DB_PREFIX_.'rj_carrier_infoshop` hs';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return ($row);
    }

    /**
     * Devuelve la información de la tienda (remitente)
     *
     * @return array
     */
    public static function getShopData($id_infoshop = null) 
    {
        $id_infoshop = RjcarrierInfoshop::getInfoShopID();
        $fields = array();
        
        if ($id_infoshop) {
            $infoshop = new RjcarrierInfoshop((int)$id_infoshop);
        } else {
            $infoshop = new RjcarrierInfoshop();
        }

        if($id_infoshop){
            $fields['id_infoshop'] = Tools::getValue('id_infoshop', $infoshop->id);
        }

        $fields['firstname'] = Tools::getValue('firstname', $infoshop->firstname);
        $fields['lastname'] = Tools::getValue('lastname', $infoshop->lastname);
        $fields['company'] = Tools::getValue('company', $infoshop->company);
        $fields['additionalname'] = Tools::getValue('additionalname', $infoshop->additionalname);
        $fields['id_country'] = Tools::getValue('id_country', $infoshop->id_country);
        $fields['country'] = Country::getNameById(Context::getContext()->language->id, $fields['id_country']);
        $fields['state'] = Tools::getValue('state', $infoshop->state);
        $fields['city'] = Tools::getValue('city', $infoshop->city);
        $fields['street'] = Tools::getValue('street', $infoshop->street);
        $fields['number'] = Tools::getValue('number', $infoshop->number);
        $fields['postcode'] = Tools::getValue('postcode', $infoshop->postcode);
        $fields['additionaladdress'] = Tools::getValue('additionaladdress', $infoshop->additionaladdress);
        $fields['isbusiness'] = Tools::getValue('isbusiness', $infoshop->isbusiness);
        $fields['addition'] = Tools::getValue('addition', $infoshop->addition);
        $fields['email'] = Tools::getValue('email', $infoshop->email);
        $fields['phone'] = Tools::getValue('phone', $infoshop->phone);
        $fields['vatnumber'] = Tools::getValue('vatnumber', $infoshop->vatnumber);

        return $fields;
    }
}