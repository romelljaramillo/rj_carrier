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
// namespace Classes;

class InfoShop
{
    private  $id_shop;
    private  $shop;
    private  $id_shop_group;
    private  $infoshop = array();

    public function __construct(){
        // $this->context = Context::getContext();
        $this->id_shop = $this->context->shop->id;
        $this->id_shop_group = $this->context->shop->id_shop_group;
        $this->id_lang = $this->context->language->id;
        $this->setShopId();
    }

    public static function getShopData() {
        $id_infoshop = RjInfoshop::getInfoShopID();
        return self::getInfoShop($id_infoshop);
    }

    public function getShopName()
    {
        return Configuration::get('PS_SHOP_NAME', null, $this->id_shop_group, $this->id_shop);
    }

    /**
     * @return Address the current shop address
     */
    public function getAddressShop()
    {
        $address = [];
        if ($this->id_shop) {
            $id_country = Configuration::get('PS_SHOP_COUNTRY_ID', null, $this->id_shop_group, $this->id_shop) ? 
            Configuration::get('PS_SHOP_COUNTRY_ID', null, $this->id_shop_group, $this->id_shop) : 
            Configuration::get('PS_COUNTRY_DEFAULT', null, $this->id_shop_group, $this->id_shop);
            $idState = Configuration::get('PS_SHOP_STATE_ID', null, $this->id_shop_group, $this->id_shop);
            $address['shop_country'] = Country::getNameById($this->id_lang, $id_country);
            $address['shop_country_iso'] = Country::getIsoById($id_country);
            $address['shop_state'] = State::getNameById($idState);
            $address['street'] = Configuration::get('PS_SHOP_ADDR1', null, $this->id_shop_group, $this->id_shop);
            $address['additionaladdress'] = Configuration::get('PS_SHOP_ADDR2', null, $this->id_shop_group, $this->id_shop);
            $address['postcode'] = Configuration::get('PS_SHOP_CODE', null, $this->id_shop_group, $this->id_shop);
            $address['shop_city'] = Configuration::get('PS_SHOP_CITY', null, $this->id_shop_group, $this->id_shop);
        }
        return $address;
    }

    public function getShopInfoExtra()
    {
        return [
            'shop_phone' => Configuration::get('PS_SHOP_PHONE', null, $this->id_shop_group, $this->id_shop),
            'shop_email' => Configuration::get('PS_SHOP_EMAIL', null, $this->id_shop_group, $this->id_shop),
            'shop_details' => Configuration::get('PS_SHOP_DETAILS', null, $this->id_shop_group, $this->id_shop),
        ];
    }

    public static function getInfoShop($id_infoshop = null) {
        $fields = array();
        
        if ($id_infoshop) {
            $infoshop = new RjInfoshop((int)$id_infoshop);
        } else {
            $infoshop = new RjInfoshop();
        }

        if($id_infoshop){
            $fields['id_infoshop'] = Tools::getValue('id_infoshop', $infoshop->id);
        }

        $fields['firstname'] = Tools::getValue('firstname', $infoshop->firstname);
        $fields['lastname'] = Tools::getValue('lastname', $infoshop->lastname);
        $fields['company'] = Tools::getValue('company', $infoshop->company);
        $fields['additionalname'] = Tools::getValue('additionalname', $infoshop->additionalname);
        $fields['countrycode'] = Tools::getValue('countrycode', $infoshop->countrycode);
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
        $fields['eorinumber'] = Tools::getValue('eorinumber', $infoshop->eorinumber);

        return $fields;
    }

    public function infoShopExists($id_infoshop)
    {
        $sql = 'SELECT hs.`id_infoshop` as id_infoshop
                FROM `'._DB_PREFIX_.'rj_infoshop` hs
                WHERE hs.`id_infoshop` = '.(int)$id_infoshop;
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return ($row);
    }

    public function setShopId()
    {
        $this->shop = new Shop($this->id_shop);
        if (Validate::isLoadedObject($this->shop)) {
            Shop::setContext(Shop::CONTEXT_SHOP, (int)$this->id_shop);
        }
    }

    /**
     * Returns the shop address
     *
     * @return string
     */
    public function getShopAddressFormatted()
    {
        $shop_address = '';

        $shop_address_obj = $this->shop->getAddress();
        if (isset($shop_address_obj) && $shop_address_obj instanceof Address) {
            $shop_address = AddressFormat::generateAddress($shop_address_obj, array(), ' - ', ' ');
        }

        return $shop_address;
    }

    /**
     * Returns the logo
     */
    protected function getLogo()
    {
        $logo = '';

        $id_shop = (int)$this->id_shop;

        if (Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop) != false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, $this->id_shop_group, $this->id_shop))) {
            $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE', null, $this->id_shop_group, $this->id_shop);
        } elseif (Configuration::get('PS_LOGO', null, null, $id_shop) != false && file_exists(_PS_IMG_DIR_.Configuration::get('PS_LOGO', null, $this->id_shop_group, $this->id_shop))) {
            $logo = _PS_IMG_DIR_.Configuration::get('PS_LOGO', null, $this->id_shop_group, $this->id_shop);
        }
        return $logo;
    }

}