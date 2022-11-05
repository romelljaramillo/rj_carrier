<?php
/**
* 2007-2021 Roanja
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
*  @author    Roanja SA <contact@roanja.com>
*  @copyright 2007-2021 Roanja
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Model\RjcarrierShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\Model\RjcarrierInfoPackage;
use Roanja\Module\RjCarrier\Model\RjcarrierInfoshop;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierCompany;

define('IMG_ICON_COMPANY_DIR', 'ruta_icons');

class Rj_Carrier extends Module
{
    protected $order;
    protected $_html;
    protected $_errors = [];
    protected $_warning = [];
    protected $_success = [];
    protected $_info = [];

    /**
     * Default hook to install
     * 1.6 and 1.7
     *
     * @var array
     */
    const RJ_HOOK_LIST = [
        'displayHeader',
        'displayProductAdditionalInfo',
        'displayBackOfficeHeader'
    ];

    /**
     * Hook to install for 1.7
     *
     * @var array
     */
    const RJ_HOOK_LIST_17 = [
        'displayAdminOrder',
        'displayAfterCarrier',
        'displayBeforeCarrier',
        'header',
    ];

    /**
     * Hook to install for 1.6
     *
     * @var array
     */
    const RJ_HOOK_LIST_16 = [
        'adminOrder',
        'displayCarrierList',
        'updateCarrier'
    ];

    /**
     * Names of ModuleAdminController used
     */
    const RJ_MODULE_ADMIN_CONTROLLERS = [
        'AdminParentTabRjCarrier' => [
            'name' => 'Rj Carrier',
            'visible' => true,
            'class_name' => 'AdminParentTabRjCarrier'
        ],
        'AdminRjCarrierModule' => [
            'name' => 'Configuration',
            'visible' => true,
            'class_name' => 'AdminRjCarrierModule',
            'parent_class_name' => 'AdminParentTabRjCarrier',
            'icon' => 'settings'
        ],
        'AdminRjShipments' => [
            'name' => 'Shipments',
            'visible' => true,
            'class_name' => 'AdminRjShipments',
            'parent_class_name' => 'AdminParentTabRjCarrier',
            'icon' => 'local_shipping'
        ],
        'AdminRjLabel' => [
            'name' => 'AdminRJLabel',
            'visible' => true,
            'class_name' => 'AdminRjLabel'
        ],
        'AdminAjaxRjCarrier' => [
            'name' => 'AdminAjaxRjCarrier',
            'visible' => true,
            'class_name' => 'AdminAjaxRjCarrier'
        ],
        'AdminRjShipmentGenerate' => [
            'name' => 'Generate Shipment',
            'visible' => true,
            'class_name' => 'AdminRjShipmentGenerate',
            'parent_class_name' => 'AdminParentTabRjCarrier',
            'icon' => 'assessment'
        ]
    ];

    /**
     * Names of fields config Info Extra carrier used
     */
    protected $fields_config_info_extra = [];

    public $fields_form_config_info_extra = [];

    public $fields_multi_confi = [];

    public function __construct()
    {
        $this->name = 'rj_carrier';
        $this->tab = 'administration';
        $this->version = '2.0.2';
        $this->author = 'Roanja';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Rj Carrier');
        $this->description = $this->l('Service multi-carrier economic');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        $defaultInstall = parent::install()
            && $this->registerHook(self::RJ_HOOK_LIST)
            && $this->installTabs();

        if(!$defaultInstall){
            return false;
        }

        include(dirname(__FILE__).'/sql/install.php');

        // Install specific to prestashop 1.7
        if(_PS_VERSION_ >= 1.7){
            $result = $this->registerHook(self::RJ_HOOK_LIST_17);
            $this->updatePosition(\Hook::getIdByName('displayAdminOrder'), false, 1);
            return $result;
        }

        // Install specific to prestashop 1.6
        $result = $this->registerHook(self::RJ_HOOK_LIST_16);
        $this->updatePosition(\Hook::getIdByName('adminOrder'), false, 1);
        return $result;
    }

    /**
     * Install all Tabs.
     *
     * @return bool
     */
    public function installTabs()
    {
        foreach (static::RJ_MODULE_ADMIN_CONTROLLERS as $adminTab) {
            if (false === $this->installTab($adminTab)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install Tab.
     * Used in upgrade script.
     *
     * @param array $tabData
     *
     * @return bool
     */
    public function installTab(array $tabData)
    {
        if (Tab::getIdFromClassName($tabData['class_name'])) {
            return true;
        }

        $tab = new Tab();
        $tab->module = $this->name;
        $tab->class_name = $tabData['class_name'];
        $tab->id_parent = empty($tabData['parent_class_name']) ? 0 : Tab::getIdFromClassName($tabData['parent_class_name']);
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabData['name'];
        }
        if(!empty($tabData['icon'])){
            $tab->icon = $tabData['icon'];
        }

        return $tab->add();
    }

    /**
     * Function executed at the uninstall of the module
     *
     * @return bool
     */
    public function uninstall()
    {
        // include(dirname(__FILE__).'/sql/uninstall.php');
        
        return parent::uninstall() && $this->uninstallTabs();
    }

    /**
     * Uninstall all Tabs.
     *
     * @return bool
     */
    public function uninstallTabs()
    {
        foreach (static::RJ_MODULE_ADMIN_CONTROLLERS as $adminTab) {
            if (false === $this->uninstallTab($adminTab)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall Tab.
     * Can be used in upgrade script.
     *
     * @param array $tabData
     *
     * @return bool
     */
    public function uninstallTab(array $tabData)
    {
        $tabId = Tab::getIdFromClassName($tabData['class_name']);
        $tab = new Tab($tabId);

        if (false === Validate::isLoadedObject($tab)) {
            return false;
        }

        if (false === (bool) $tab->delete()) {
            return false;
        }

        if (isset($tabData['core_reference'])) {
            $tabCoreId = Tab::getIdFromClassName($tabData['core_reference']);
            $tabCore = new Tab($tabCoreId);

            if (Validate::isLoadedObject($tabCore)) {
                $tabCore->active = true;
            }

            if (false === (bool) $tabCore->save()) {
                return false;
            }
        }

        return true;
    }

    public function getContent()
    {
        $this->setFieldsConfigExtraCarriers();

        if (Tools::isSubmit('submitConfigExtraInfo')
            || Tools::isSubmit('submitConfigInfoShop')
        ){
            $this->_postProcess();
        }
        
        $tab = Tools::getValue('tab_form');
        $this->context->smarty->assign([
            'notifications' => $this->prepareNotifications(),
            'form_info_shop' => $this->renderFormInfoShop(),
            'form_config_carriers' => $this->renderConfigCarriers(),
            'form_info_extra' => $this->renderFormConfigInfoExtra(),
            'tab' => $tab
        ]);
        
        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Devuelve los formularios de config de las class Carrier + company
     *
     * @return html Forms
     */
    public function renderConfigCarriers() 
    {
        $html = [];
        $carries_company = RjcarrierCompany::getCarrierCompany();
        
        foreach ($carries_company as $company) {
            $shortname = strtolower($company['shortname']);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/Carrier/'. ucfirst($shortname) .'/'.$class_name.'.php')) {
                $Class = '\Roanja\Module\RjCarrier\Carrier\\'. ucfirst($shortname) .'\\' . $class_name;
                if (class_exists($Class)) {
                    $class = new $Class();
                    $html[$shortname] = $class->renderConfig();
                    $fields = $class->getFieldsFormConfigExtra();
                    $this->fields_form_config_info_extra = array_merge($this->fields_form_config_info_extra, $fields);
                }
            }
        }
        
        return $html;
    }

    /**
     * Setea la variable fields_config_info_extra con todos los campos extras de los Carries Company
     *
     * @return void
     */
    public function setFieldsConfigExtraCarriers() 
    {
        $carries_company = RjcarrierCompany::getCarrierCompany();
        foreach ($carries_company as $company) {
            $shortname = strtolower($company['shortname']);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/Carrier/'. ucfirst($shortname) .'/'.$class_name.'.php')) {
                    $Class = '\Roanja\Module\RjCarrier\Carrier\\'. ucfirst($shortname) .'\\' . $class_name;
                    if (class_exists($Class)) {
                        $class = new $Class();
                    $fields = $class->getConfigFieldsExtra();
                    $this->fields_config_info_extra = array_merge_recursive($this->fields_config_info_extra, $fields);
                }
            }
        }
    }

    /**
     * Obtine los datos del customer
     *
     * @param int $id_order
     * @return array $info_customer
     */
    public function getInfoCustomer($id_order)
    {
        $order = new Order($id_order);
        $address = new Address($order->id_address_delivery);
        $info_customer = $address->getFields();
        $info_customer['state'] = State::getNameById($address->id_state);
        $info_customer['country'] = $address->country;
        $info_customer['countrycode'] = Country::getIsoById($info_customer['id_country']);

        $customer = new Customer((int)$info_customer['id_customer']);
        $info_customer['email'] = $customer->email;
        
        return $info_customer;
    }

    /**
     * Obtiene la información de los paquetes
     *
     * @param int $id_order
     * @return array $rj_carrier_infopackage
     */
    public function getInfoPackage($id_order)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_shop_group = $this->context->shop->id_shop_group;

        $rj_carrier_infopackage = RjcarrierInfoPackage::getPackageByIdOrder($id_order, $id_shop);

        // obtener contrareembolso
        if(!isset($rj_carrier_infopackage['cash_ondelivery'])){
            $module_contrareembolso = Configuration::get('RJ_MODULE_CONTRAREEMBOLSO', null, $id_shop_group, $id_shop);
            
            $order = new Order($id_order);

            if($module_contrareembolso == $order->module){
                $rj_carrier_infopackage['cash_ondelivery'] = $order->total_paid_tax_incl;
            }
        }

        return $rj_carrier_infopackage;
    }

    /**
     * Procesa la eliminación del envío
     *
     * @param int $id_shipment
     * @return void
     */
    private function deleteShipment($id_shipment)
    {
        $rjcarrierShipment = new RjcarrierShipment((int)$id_shipment);
        
        if(!$rjcarrierShipment->delete()){
            $this->_errors[] = $this->l('No se puede eliminar el envío revisar su estado!.');
            return false;
        }

        if(!RjcarrierLabel::deleteLabelsByIdShipment($id_shipment)){
            $this->_errors[] = $this->l('No se eliminaron las etiquetas del envio!.');
            return false;
        }
            
        $this->_success[] = $this->l('Se ha eliminado el envío.');
        return true;
    }

    /**
     * Valida el cambio de transportista de la orden y elimina el envío si ha cambiado 
     *
     * @param int $id_order
     * @param int $new_id_reference_carrier
     * @return void
     */
    public function deleteShitmentChangeCarrier($id_order, $id_shipment_old, $new_id_reference_carrier)
    {
        $id_shop = Context::getContext()->shop->id;
        $info_package_old = RjcarrierInfoPackage::getPackageByIdOrder($id_order, $id_shop);

        if ($info_package_old['id_reference_carrier'] != (int) $new_id_reference_carrier) {
            $info_company_carrier_old = CarrierCompany::getInfoCompanyByIdReferenceCarrier($info_package_old['id_reference_carrier']);
            $info_company_carrier_new = CarrierCompany::getInfoCompanyByIdReferenceCarrier($new_id_reference_carrier);
            if ($info_company_carrier_old != $info_company_carrier_new) {
                $this->deleteShipment($id_shipment_old);
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene el id_reference
     *
     * @param int $id_order
     * @return int id_reference
     */
    public function getIdReferenceCarrierByIdOrder($id_order)
    {
        $id_lang = Context::getContext()->language->id;
        $order = new Order($id_order);
        $carrier = new Carrier($order->id_carrier, $id_lang);
        return $carrier->id_reference;
    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
    
    protected function validationConfigurationCarrier()
    {
        $carries_company = RjcarrierCompany::getCarrierCompany();
        foreach ($carries_company as $company) {
            $shortname = strtolower($company['shortname']);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/Carrier/'. ucfirst($shortname) .'/'.$class_name.'.php')) {
                $Class = '\Roanja\Module\RjCarrier\Carrier\\'. ucfirst($shortname) .'\\' . $class_name;
                if (class_exists($Class)) {
                    $class = new $Class();
                    $validation = $class->validationConfiguration();
                    if($validation){
                        $this->_warning = array_merge($this->_warning, $validation);
                    }
                }
            }
        }

        $fields = RjcarrierInfoshop::$definition['fields'];
        $info_shop = RjcarrierInfoshop::getShopData();
        foreach ($info_shop as $key => $value) {
            if($fields[$key]['required'] && !$value){
                $this->_warning[] = $this->l('Required data module configuration Info shop!. ') . 
                $key;
            }
        }

        if($this->_warning){
            $this->_warning[] = '<a class="btn btn-primary" target="_blank" href="'.$this->context->link->getAdminLink(
                'AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name]).'">'. 
                $this->l('Go to configuration!. ').'</a>';
                return false;
        }

        return true;
    }

    /**
     * Generael envío y las etiquetas del mismo
     *
     * @param int $id_infopackage
     * @return void
     */
    public function generateLabel($id_infopackage)
    {
        if(!$id_infopackage){
            return;
        }

        $rjcarrier_infoPackage = new RjcarrierInfoPackage((int)$id_infopackage);
        $info_package = $rjcarrier_infoPackage->getFields();
        
        $id_order = $info_package['id_order'];
        
        $id_lang = Context::getContext()->language->id;
        
        $info_shipment = RjcarrierShipment::getShipmentByIdOrder($id_order);

        if($info_shipment){
            return;
        }

        if(!$info_package['id_reference_carrier']){
            $info_package['id_reference_carrier'] = $this->getIdReferenceCarrierByIdOrder($id_order);
        }

        $carrier = Carrier::getCarrierByReference((int)$info_package['id_reference_carrier'], $id_lang);

        $name_carrier = '';
        if($carrier->name){
            $name_carrier = $carrier->name;
        }

        $info_company_carrier = CarrierCompany::getInfoCompanyByIdReferenceCarrier($info_package['id_reference_carrier']);
        $info_type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdCarrierCompany($info_company_carrier['id_carrier_company']);

        $shipment = [
            'id_order' => $id_order,
            'info_package' => $info_package,
            'info_customer' => $this->getInfoCustomer($id_order),
            'info_shop' => RjcarrierInfoshop::getShopData(),
            'name_carrier' => $name_carrier,
            'info_company_carrier' => $info_company_carrier,
            'info_type_shipment' => $info_type_shipment,
            'config_extra_info' => $this->getConfigExtraFieldsValues()
        ];

        $shipment['info_shipment'] = CarrierCompany::saveShipment($shipment);

        $class_carrier = $this->getClassCarrier($info_company_carrier["shortname"]);
        $class_carrier->createShipment($shipment);
    }

    /**
     * Vista del modulo en admin order
     *
     * @param [type] $params
     * @return void
     */
    public function hookDisplayAdminOrder($params)
    {
        $id_lang = Context::getContext()->language->id;
        $id_order = (int)$params['id_order'];
        $info_package = [];
        $info_company_carrier = [];
        $name_carrier = '';

        $validate_config = $this->validationConfigurationCarrier();

        $info_shipment = RjcarrierShipment::getShipmentByIdOrder($id_order);
        $id_shipment = $info_shipment['id_shipment'];

        if (Tools::isSubmit('submitDeleteShipment')) {
            if($this->deleteShipment(Tools::getValue('id_shipment'))){
                $info_shipment = [];
            }
            $info_package = $this->getInfoPackage($id_order);
        } elseif ((Tools::isSubmit('submitFormPackCarrier') || Tools::isSubmit('submitSavePackSend')) && $validate_config){
            if(Tools::getValue('id_reference_carrier') && $id_shipment){
                if($this->deleteShitmentChangeCarrier($id_order, $id_shipment, Tools::getValue('id_reference_carrier')))
                    $info_shipment = [];
            }
            $info_package = CarrierCompany::saveInfoPackage($id_order);
        } else {
            $info_package = $this->getInfoPackage($id_order);
        }
        
        if(!$info_package['id_reference_carrier']){
            $info_package['id_reference_carrier'] = $this->getIdReferenceCarrierByIdOrder($id_order);
        }

        $name_carrier = Carrier::getCarrierByReference((int)$info_package['id_reference_carrier'], $id_lang);
        $info_company_carrier = CarrierCompany::getInfoCompanyByIdReferenceCarrier($info_package['id_reference_carrier']);
        $info_type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdCarrierCompany($info_company_carrier['id_carrier_company']);

        $shipment = [
            'link' => $this->context->link,
            'id_order' => $id_order,
            'info_package' => $info_package,
            'info_shipment' => $info_shipment,
            'info_customer' => $this->getInfoCustomer($id_order),
            'info_shop' => RjcarrierInfoshop::getShopData(),
            'carriers' => Carrier::getCarriers((int)$id_lang),
            'name_carrier' => $name_carrier->name,
            'info_company_carrier' => $info_company_carrier,
            'info_type_shipment' => $info_type_shipment,
            'url_ajax' => $this->context->link->getAdminLink('AdminAjaxRjCarrier'),
        ];

        if(isset($info_company_carrier["shortname"])){
            $class_carrier = $this->getClassCarrier($info_company_carrier["shortname"]);
            $shipment['show_create_label'] = $class_carrier->show_create_label;
        }

        if(!$id_shipment){
            if ((Tools::isSubmit('submitShipment') || Tools::isSubmit('submitSavePackSend'))){
                if($validate_config){
                    $shipment['config_extra_info'] = $this->getConfigExtraFieldsValues();
                    $shipment['info_shipment'] = CarrierCompany::saveShipment($shipment);
                    $class_carrier->createShipment($shipment);
                } else {
                    $this->_errors[] = '<a class="btn btn-primary" target="_blank" href="'.$this->context->link->getAdminLink(
                        'AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name]).'">'. 
                        $this->l('Go to configuration!. ').'</a>';
                }
            } 
        }

        if($id_shipment){
            if (Tools::isSubmit('submitCreateLabel')) {
                if(!RjcarrierLabel::getIdsLabelsByIdShipment($id_shipment)) {
                    $class_carrier->createLabel($id_shipment, $id_order);
                }
            }

            $shipment['labels'] = RjcarrierLabel::getLabelsByIdShipment($id_shipment);
        }

        $shipment['notifications'] = $this->prepareNotifications();
        
        $this->context->smarty->assign($shipment);

        $this->_html .= $this->display(__FILE__, 'admin-order.tpl');
                   
        return $this->_html;
    }

    /**
     * Obtiene la class según transportista seleccionado
     *
     * @param array $shipment
     * @return obj
     */
    protected function getClassCarrier($shortname = null)
    {
        $class = null;
        if($shortname) {
            $shortname = strtolower($shortname);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/Carrier/'. ucfirst($shortname) .'/'.$class_name.'.php')) {
                $obj = '\Roanja\Module\RjCarrier\Carrier\\'. ucfirst($shortname) .'\\' . $class_name;
                if (class_exists($obj)) {
                    $class = new $obj();
                }
            } else {
                $class = new CarrierCompany();
            }
        } else {
            $class = new CarrierCompany();
        }

        return $class;
    }

    protected function _postProcess()
	{
        if (Tools::isSubmit('submitConfigExtraInfo')) {
            $this->saveConfigExtraInfo();
        } elseif (Tools::isSubmit('submitConfigInfoShop')) {
            $this->saveInfoShop();
        }
    }

    public function saveInfoShop()
    {
        if (Tools::getValue('id_infoshop')) {
            $infoshop = new RjcarrierInfoshop((int)Tools::getValue('id_infoshop'));
            if (!Validate::isLoadedObject($infoshop)) {
                $this->_html .= $this->displayError($this->l('Invalid infoshop ID'));
                return false;
            }
        } else {
            $infoshop = new RjcarrierInfoshop();
        }

        $infoshop->firstname  = Tools::getValue('firstname');
        $infoshop->lastname  = Tools::getValue('lastname');
        $infoshop->company  = Tools::getValue('company');
        $infoshop->additionalname  = Tools::getValue('additionalname');
        $infoshop->street  = Tools::getValue('street');
        $infoshop->number  = Tools::getValue('number');
        $infoshop->postcode  = Tools::getValue('postcode');
        $infoshop->city  = Tools::getValue('city');
        $infoshop->state  = Tools::getValue('state');
        $infoshop->id_country  = Tools::getValue('id_country');
        $infoshop->additionaladdress  = Tools::getValue('additionaladdress');
        $infoshop->isbusiness  = (Tools::getValue('company')) ? true : false;
        $infoshop->email  = Tools::getValue('email');
        $infoshop->phone  = Tools::getValue('phone');
        $infoshop->vatnumber  = Tools::getValue('vatnumber');
        
        $validate = $infoshop->validateFields(false, true);
        if($validate !== true){
            $this->_errors[] = $this->l('Required fields missing ') . $validate;
            return false;
        }

        if (!Tools::getValue('id_infoshop')) {
            if (!$infoshop->add()) {
                $this->_html .= $this->displayError($this->l('The infoshop could not be added.'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 3, 'module_name' => $this->name, 'tab_form' => 'infoshop']));
            }
        } elseif (!$infoshop->update()) {
            $this->_html .= $this->displayError($this->l('The infoshop could not be updated.'));
        } else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 6, 'module_name' => $this->name, 'tab_form' => 'infoshop']));
        }
    }

    private function saveConfigExtraInfo()
    {
        $res = true;
        $shop_context = Shop::getContext();

        $shop_groups_list = [];
        $shops = Shop::getContextListShopID();

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }

            foreach ($this->fields_config_info_extra as $field) {
                $res =  Configuration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id, $shop_id);
            }
        }

        /* Update global shop context if needed*/
        switch ($shop_context) {
            case Shop::CONTEXT_ALL:
                foreach ($this->fields_config_info_extra as $field) {
                    $res &= Configuration::updateValue($field['name'], Tools::getValue($field['name']));
                }
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        foreach ($this->fields_config_info_extra as $field) {
                            $res &= Configuration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                        }
                    }
                }
                break;
            case Shop::CONTEXT_GROUP:
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        foreach ($this->fields_config_info_extra as $field) {
                            $res &= Configuration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                        }
                    }
                }
                break;
        }

        /* Display errors if needed */
		if (!$res)
            $this->_html .= $this->displayError($this->l('The configuration could not be updated.'));
        else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 6, 'module_name' => $this->name, 'tab_form' => 'infoextra']));
        }
    }

    public function renderFormInfoShop()
    {

        $countries = Country::getCountries((int) $this->context->language->id);
        foreach ($countries as $country) {
            // $this->statuses_array[$status['id_order_state']] = $status['name'];
            $countries_array[] =  [
                'id' => $country['id_country'],
                'name' => $country['name']
            ];
        }
        
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Information company'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Firstname'),
                        'name' => 'firstname',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Lastname'),
                        'name' => 'lastname',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Company'),
                        'name' => 'company',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Additional name'),
                        'name' => 'additionalname',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'name' => 'email',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Phone'),
                        'name' => 'phone',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('VAT Number'),
                        'name' => 'vatnumber',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Street'),
                        'name' => 'street',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Number'),
                        'name' => 'number',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Postcode'),
                        'name' => 'postcode',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('City'),
                        'name' => 'city',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('State'),
                        'name' => 'state',
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Select Country'),
                        'name' => 'id_country',
                        'required' => true,
                        'options' => [
                            'query' => $countries_array,
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Additional address'),
                        'name' => 'additionaladdress',
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ],
        ];

        if ($id_infoshop = RjcarrierInfoshop::getInfoShopID()) {
            $fields_form['form']['input'][] = ['type' => 'hidden', 'name' => 'id_infoshop'];
        }

        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = [];
        $helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfigInfoShop';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = [
			'fields_value' => RjcarrierInfoshop::getShopData(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
        ];

		return $helper->generateForm([$fields_form]);
    }

    /**
     * Genera el formulario de información extra obteniendo los inputs desde las clases carriers de companies
     *
     * @return void
     */
    public function renderFormConfigInfoExtra()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration extra information'),
                    'icon' => 'icon-cogs'
                ],
                'input' => $this->fields_form_config_info_extra,
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ],
        ];

        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfigExtraInfo';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = [
			'fields_value' => $this->getConfigExtraFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
        ];

        $helper->override_folder = '/';

		return $helper->generateForm([$fields_form]);
    }

    /**
     * Obtiene los datos de configuración
     *
     * @param array $fields
     * @return array
     */
    public function getConfigExtraFieldsValues()
	{
		$id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $arry_fields = [];

        $this->setFieldsConfigExtraCarriers();

        foreach ($this->fields_config_info_extra as $field) {
            $arry_fields[$field['name']] = Tools::getValue($field['name'], Configuration::get($field['name'], null, $id_shop_group, $id_shop));
        }

        return $arry_fields;
	}
    
    /**
     * Notificaciones de procesos
     *
     * @return void
     */
    protected function prepareNotifications()
    {
        $notifications = [
            'error' => $this->_errors,
            'warning' => $this->_warning,
            'success' => $this->_success,
            'info' => $this->_info,
        ];

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['notifications'])) {
            $notifications = array_merge($notifications, json_decode($_SESSION['notifications'], true));
            unset($_SESSION['notifications']);
        } elseif (isset($_COOKIE['notifications'])) {
            $notifications = array_merge($notifications, json_decode($_COOKIE['notifications'], true));
            unset($_COOKIE['notifications']);
        }

        return $notifications;
    }
}