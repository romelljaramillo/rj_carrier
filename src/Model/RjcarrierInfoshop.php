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
use Shop;
use Context;
use Validate;

class RjcarrierInfoshop extends \ObjectModel
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
    public $email;
    public $phone;
    public $vatnumber;
    public $date_add;
    public $date_upd;
    public $id_shop;

    const TABLE_NAME = _DB_PREFIX_ . 'rj_carrier_infoshop';
    const TABLE_SHOP = _DB_PREFIX_ . 'rj_carrier_infoshop_shop';

    public static $definition = [
        'table' => 'rj_carrier_infoshop',
        'primary' => 'id_infoshop',
        'multishop' => true,
        'fields' => [
            'firstname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100, 'required' => true],
            'lastname' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100, 'required' => true],
            'company' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'id_country' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'state' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255, 'required' => true],
            'city' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255, 'required' => true],
            'street' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100, 'required' => true],
            'additionaladdress' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'number' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100, 'required' => true],
            'postcode' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100, 'required' => true],
            'isbusiness' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 100],
            'phone' => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 100, 'required' => true],
            'vatnumber' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat']
        ]
    ];

    public function __construct($id_infoshop = null, $id_lang = null, $id_shop = null, Context $context = null)
    {
        Shop::addTableAssociation('rj_carrier_infoshop', ['type' => 'shop']);
        parent::__construct($id_infoshop, $id_lang, $id_shop);
    }

    public static function getInfoShopID()
    {
        $sql = 'SELECT hs.`id_infoshop`
                FROM `' . _DB_PREFIX_ . 'rj_carrier_infoshop` hs';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    public static function getShopData($id_infoshop = null)
    {
        if (!$id_infoshop) {
            $id_infoshop = self::getInfoShopID();
        }

        if (!$id_infoshop) {
            return [];
        }

        $infoshop = new self((int)$id_infoshop);

        $fields = [
            'id_infoshop' => $infoshop->id,
            'firstname' => $infoshop->firstname,
            'lastname' => $infoshop->lastname,
            'company' => $infoshop->company,
            'additionalname' => $infoshop->additionalname,
            'id_country' => $infoshop->id_country,
            'country' => \Country::getNameById(Context::getContext()->language->id, $infoshop->id_country),
            'state' => $infoshop->state,
            'city' => $infoshop->city,
            'street' => $infoshop->street,
            'number' => $infoshop->number,
            'postcode' => $infoshop->postcode,
            'additionaladdress' => $infoshop->additionaladdress,
            'isbusiness' => $infoshop->isbusiness,
            'email' => $infoshop->email,
            'phone' => $infoshop->phone,
            'vatnumber' => $infoshop->vatnumber,
        ];

        return $fields;
    }
}
