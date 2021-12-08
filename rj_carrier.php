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

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierShipment.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierLabel.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierInfoPackage.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierInfoshop.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/CarrierCompany.php');

class Rj_Carrier extends Module
{
    protected $config_form = false;
    /** @var Shop */
    public $shop;
    public $order;
    public $_html;

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
        'displayHeader',
        'header',
        'displayBackOfficeHeader'
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
        'AdminParentTabRjCarrier',
        'AdminRJModule',
        'AdminRJLabel',
        'AdminAjaxRJCarrier'
    ];

    /**
     * Names of fields config Info Extra carrier used
     */
    public $fields_config_info_extra = [];

    public $fields_form_config_info_extra = [];

    public $fields_multi_confi = [];

    public function __construct()
    {
        $this->name = 'rj_carrier';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Roanja';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Service multi-carrier Rj Carrier');
        $this->description = $this->l('Service multi-carrier economic');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

    }

    public function install()
    {
        $defaultInstall = parent::install()
            && $this->registerHook(self::RJ_HOOK_LIST)
            && $this->installTab('AdminParentTabRjCarrier', 'RJ Carrier')
            && $this->installTab('AdminParentTabDhl', 'DHL', 'AdminParentTabRjCarrier', 'local_shipping')
            && $this->installTab('AdminRJModule', 'Configuration', 'AdminParentTabRjCarrier', 'settings')
            && $this->installTab('AdminRJShipmentsDHL', 'Shipments DHL', 'AdminParentTabDhl')
            && $this->installTab('AdminRJLabel', 'AdminRJLabel')
            && $this->installTab('AdminAjaxRJCarrier', 'AdminAjaxRJCarrier');

        if(!$defaultInstall){
            return false;
        }

        include(dirname(__FILE__).'/sql/install.php');

        // Install specific to prestashop 1.7
        if(_PS_VERSION_ >= 1.7){
            return $this->registerHook(self::RJ_HOOK_LIST_17) &&
                $this->updatePosition(\Hook::getIdByName('displayAdminOrder'), false, 1);
        }

        // Install specific to prestashop 1.6
        return $this->registerHook(self::RJ_HOOK_LIST_16) &&
            $this->updatePosition(\Hook::getIdByName('adminOrder'), false, 1);

    }

    public function installTab($className, $tabName, $tabParentName = false, $icon = '')
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        if ($tabParentName) {
            $tab->id_parent = (int)Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }

        $tab->icon = $icon;

        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * Function executed at the uninstall of the module
     *
     * @return bool
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        
        return parent::uninstall() && $this->uninstallTabs();
    }

    /**
     * uninstall tabs
     *
     * @return bool
     */
    public function uninstallTabs()
    {
        $uninstallTabCompleted = true;

        foreach (static::RJ_MODULE_ADMIN_CONTROLLERS as $controllerName) {
            $id_tab = (int) Tab::getIdFromClassName($controllerName);
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $uninstallTabCompleted = $uninstallTabCompleted && $tab->delete();
            }
        }

        return $uninstallTabCompleted;
    }

    public function getContent()
    {
        $this->setFieldsConfigExtraCarriers();

        if (Tools::isSubmit('submitConfigExtraInfo')
            || Tools::isSubmit('submitConfigInfoShop')
        ){
            $this->_postProcess();
        }

        // $this->context->smarty->assign('module_dir', $this->_path);
        // $this->_html = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        $this->_html .= $this->renderFormInfoShop();

        $this->_html .= $this->renderConfigCarriers();

        $this->_html .= $this->renderFormConfigInfoExtra();
        
        return $this->_html;
    }

    /**
     * Devuelve los formularios de config de las class Carrier + company
     *
     * @return html Forms
     */
    public function renderConfigCarriers() 
    {
        $html = '';
        $carries_company = CarrierCompany::getCarriersCompany();
        
        foreach ($carries_company as $company) {
            $shortname = strtolower($company['shortname']);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/carriers/'. $shortname .'/'.$class_name.'.php')) {
            include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/'. $shortname .'/'.$class_name.'.php');
                if (class_exists($class_name)) {
                    $class = new $class_name();
                    $html .= $class->renderConfig();
                    $fields = $class->getFieldsFormConfigExtra();
                    $this->fields_form_config_info_extra = array_merge($this->fields_form_config_info_extra, $fields);
                    // array_merge($this->fields_form_config_info_extra, $fields);
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
        $carries_company = CarrierCompany::getCarriersCompany();
        foreach ($carries_company as $company) {
            $shortname = strtolower($company['shortname']);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/carriers/'. $shortname .'/'.$class_name.'.php')) {
            include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/'. $shortname .'/'.$class_name.'.php');
                if (class_exists($class_name)) {
                    $class = new $class_name();
                    $fields = $class->getConfigFieldsExtra();
                    $this->fields_config_info_extra = array_merge_recursive($this->fields_config_info_extra, $fields);
                }
            }
        }
    }


    public function hookBackOfficeHeader()
    {
        // if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        // }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
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
        $info_customer = new Address($order->id_address_delivery);
        return $info_customer->getFields();
    }

    /**
     * Obtiene la información del package
     *
     * @param int $id_order
     * @return array $info_package
     */
    public function getInfoShipment($id_order)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_shop_group = $this->context->shop->id_shop_group;
        
        $rj_carrier_shipment = RjcarrierShipment::getShipmentByIdOrder($id_order);

        if(!$rj_carrier_shipment){
            return false;
        }

        // obtener contrareembolso
        $datos_orden = '';

        if(!isset($rj_carrier_shipment['cash_ondelivery'])){
            
            $contrareembolso = Configuration::get('RJ_MODULE_CONTRAREEMBOLSO', null, $id_shop_group, $id_shop);
            
            if(!$contrareembolso){
                $this->warning[] = $this->l('Aun no esta configurado el contrareembolso, debe configurarlo en opciones del modulo!.');
            }
            
            if($contrareembolso === 'codfee'){
                $datos_orden = $this->order->getFields();
                $rj_carrier_shipment['cash_ondelivery'] = $datos_orden['total_paid_tax_incl'];
            }
        }

        return $rj_carrier_shipment;
    }

    public function hookDisplayAdminOrder($params)
    {
        $id_lang = Context::getContext()->language->id;
        $id_order = (int)$params['id_order'];
        $info_package = [];
        $info_company_carrier = [];

        if (Tools::isSubmit('submitDeleteShipment')) {

            $this->deleteShipment(Tools::getValue('id_shipment'));

        }elseif (Tools::isSubmit('submitFormPackCarrier') || Tools::isSubmit('submitSavePackSend')){
            
            $info_package = $this->saveInfoPackage();
            $info_shipment = $this->saveInfoShipment($id_order, $info_package['id_infopackage']);

        } else {
           
            $info_shipment = $this->getInfoShipment($id_order);
            
            if($info_shipment){
                $rj_carrier_info_package = new RjcarrierInfoPackage((int)$info_shipment['id_infopackage']);
                
                if($rj_carrier_info_package->id){
                    $info_package = $rj_carrier_info_package->getFields();
                }
            }
        }
        
        $name_carrier = '';

        if($info_shipment['id_reference_carrier']){
            $name_carrier = Carrier::getCarrierByReference((int)$info_shipment['id_reference_carrier'], $id_lang);
            $info_company_carrier = CarrierCompany::getShortnameCompanyByIdReferenceCarrier($info_shipment['id_reference_carrier']);
        }

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
            'url_ajax' => $this->context->link->getAdminLink('AdminAjaxRJCarrier'),
        ];

        if (Tools::isSubmit('submitShipment') || Tools::isSubmit('submitSavePackSend')){
            $shipment['config_extra_info'] = $this->getConfigExtraFieldsValues();
            
            $this->selectShipment($shipment);
            
            $shipment['info_shipment'] = $this->getInfoShipment($id_order);
        }
        
        if($info_shipment['id_shipment']){
            $shipment['labels'] = RjcarrierLabel::getLabelsByIdShipment($info_shipment['id_shipment']);
        }

        $shipment['notifications'] = $this->prepareNotifications();
        
        $this->context->smarty->assign($shipment);

        $this->_html .= $this->display(__FILE__, 'admin-order.tpl');
                   
        return $this->_html;
    }

    /**
     * save data db table rj_carrier_shipment
     *
     * @return obj
     */
    private function saveInfoShipment($id_order, $id_infopackage)
    {
        if (!$id_order) {
            return false;
        }
        
        $id_shipment = RjcarrierShipment::getIdByIdOrder((int)$id_order);
        $order = new Order((int)$id_order);

        if($id_shipment){
            $rj_carrier_shipment = new RjcarrierShipment((int)$id_shipment);
            
            if($rj_carrier_shipment->id_reference_carrier != Tools::getValue('id_reference_carrier')){
                // OJO si cambia el transportista hay que modificar los datos del envío
                // PENDIENTE
            }

        } else {
            $rj_carrier_shipment = new RjcarrierShipment();
        }

        $configExtraFiels = $this->getConfigExtraFieldsValues();

        $hour_from = (Tools::getValue('rj_hour_from'))? Tools::getValue('rj_hour_from') . ':00' : $configExtraFiels['RJ_HOUR_FROM'] . ':00';
        $hour_until = (Tools::getValue('rj_hour_until'))? Tools::getValue('rj_hour_until') . ':00': $configExtraFiels['RJ_HOUR_UNTIL'] . ':00';

        $rj_carrier_shipment->id_order = (int)$id_order;
        $rj_carrier_shipment->reference_order = $order->reference;
        $rj_carrier_shipment->id_infopackage = (int)$id_infopackage;
        $rj_carrier_shipment->id_reference_carrier = Tools::getValue('id_reference_carrier');
        $rj_carrier_shipment->cash_ondelivery = Tools::getValue('rj_cash_ondelivery');
        $rj_carrier_shipment->message = Tools::getValue('rj_message');
        $rj_carrier_shipment->hour_from = ($this->validateFormatTime($hour_from))?$hour_from:'00:00:00';
        $rj_carrier_shipment->hour_until = ($this->validateFormatTime($hour_until))?$hour_until:'00:00:00';

        if (!$id_shipment)
        {
            if (!$rj_carrier_shipment->add())
            $this->errors[] = $this->l('The transport could not be added.');
        }elseif (!$rj_carrier_shipment->update()){
            $this->errors[] = $this->l('The transport could not be updated.');
        } 
        $this->success[] = $this->l('Information successfully updated.');

        return $rj_carrier_shipment->getFields();
    }

    /**
     * save data db table rj_carrier_infopackage - data del paquete order
     *
     * @return obj
     */
    private function saveInfoPackage()
    {
        if (Tools::getValue('id_infopackage')) {
            $rj_carrier_infopackage = new RjcarrierInfoPackage((int)Tools::getValue('id_infopackage'));

            if (!Validate::isLoadedObject($rj_carrier_infopackage))
            {
                $this->_html .= $this->displayError($this->l('Invalid slide ID'));
                return false;
            }
        } else {
            $rj_carrier_infopackage = new RjcarrierInfoPackage();
        }

        $rj_carrier_infopackage->quantity = (int)Tools::getValue('rj_quantity');
        $rj_carrier_infopackage->weight = Tools::getValue('rj_weight');
        $rj_carrier_infopackage->length = Tools::getValue('rj_length');
        $rj_carrier_infopackage->width = Tools::getValue('rj_width');
        $rj_carrier_infopackage->height = Tools::getValue('rj_height');

        if (!Tools::getValue('id_infopackage'))
        {
            if (!$rj_carrier_infopackage->add()){
                $this->errors[] = $this->l('The transport could not be added.');
                return false;
            }
        }elseif (!$rj_carrier_infopackage->update()){
            $this->errors[] = $this->l('The transport could not be updated.');
            return false;
        } 

        $this->success[] = $this->l('Information successfully updated.');

        return $rj_carrier_infopackage->getFields();
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
            $this->errors[] = $this->l('No se puede eliminar el envío revisar su estado!.');
        } else{
            $this->success[] = $this->l('Se ha eliminado el envío.');
        }
    }

    /**
     * Crea envío según la compañia seleccionada
     *
     * @param array $infoOrder
     * @return void
     */
    protected function selectShipment($shipment)
    {
        $shortname = $shipment['info_company_carrier']["shortname"];

        if($shortname) {
            $shortname = strtolower($shortname);
            $class_name = 'Carrier' . ucfirst($shortname);
            if (file_exists(_PS_MODULE_DIR_.'rj_carrier/src/carriers/'. $shortname .'/' . $class_name .'.php')) {
                include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/'. $shortname .'/' .$class_name .'.php');
                if (class_exists($class_name)) {
                    $class = new $class_name();
                    $class->createShipment($shipment);
                }
            }
        } else {
            $carrier_company = new CarrierCompany();
            $carrier_company->createShipment($shipment);
        }
    }

    /**
     * Notificaciones de procesos
     *
     * @return void
     */
    protected function prepareNotifications()
    {
        $notifications = [
            'error' => $this->errors,
            'warning' => $this->warning,
            'success' => $this->success,
            'info' => $this->info,
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
        $infoshop->addition  = Tools::getValue('addition');
        $infoshop->email  = Tools::getValue('email');
        $infoshop->phone  = Tools::getValue('phone');
        $infoshop->vatnumber  = Tools::getValue('vatnumber');

        if (!Tools::getValue('id_infoshop')) {
            if (!$infoshop->add()) {
                $this->_html .= $this->displayError($this->l('The infoshop could not be added.'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
            }
        } elseif (!$infoshop->update()) {
            $this->_html = $this->displayError($this->l('The infoshop could not be updated.'));
        } else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        }
    }

    private function saveConfigExtraInfo()
    {
        $res = true;
        $shop_context = Shop::getContext();

        $shop_groups_list = array();
        $shops = Shop::getContextListShopID();

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }

            foreach ($this->fields_config_info_extra as $field) {
                if(in_array($field, $this->fields_multi_confi)){
                    $toString_field =  serialize(Tools::getValue($field));
                    $res &=  Configuration::updateValue($field, $toString_field, false, $shop_group_id, $shop_id);
                } else {
                    $res &=  Configuration::updateValue($field, Tools::getValue($field), false, $shop_group_id, $shop_id);
                }

            }
        }

        /* Update global shop context if needed*/
        switch ($shop_context) {
            case Shop::CONTEXT_ALL:
                foreach ($fields as $field) {
                    if(in_array($field, $this->fields_multi_confi)){
                        $toString_field =  serialize(Tools::getValue($field));
                        $res &= Configuration::updateValue($field, $toString_field);
                    } else {
                        $res &= Configuration::updateValue($field, Tools::getValue($field));
                    }
                }
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        foreach ($fields as $field) {
                            if(in_array($field, $this->fields_multi_confi)){
                                $toString_field =  serialize(Tools::getValue($field));
                                $res &= Configuration::updateValue($field, $toString_field, false, $shop_group_id);
                            } else {
                                $res &= Configuration::updateValue($field, Tools::getValue($field), false, $shop_group_id);
                            }
                        }
                    }
                }
                break;
            case Shop::CONTEXT_GROUP:
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        foreach ($fields as $field) {
                            if(in_array($field, $this->fields_multi_confi)){
                                $toString_field =  serialize(Tools::getValue($field));
                                $res &= Configuration::updateValue($field, $toString_field, false, $shop_group_id);
                            } else {
                                $res &= Configuration::updateValue($field, Tools::getValue($field), false, $shop_group_id);
                            }
                        }
                    }
                }
                break;
        }

        /* Display errors if needed */
		if (!$res)
            $this->_html .= $this->displayError($this->l('The configuration could not be updated.'));
        else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        }
    }

    public function renderFormInfoShop()
    {

        $countries = Country::getCountries((int) $this->context->language->id);
        foreach ($countries as $country) {
            // $this->statuses_array[$status['id_order_state']] = $status['name'];
            $countries_array[] =  array(
                'id' => $country['id_country'],
                'name' => $country['name']
            );
        }
        
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Information company'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Firstname'),
                        'name' => 'firstname',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Lastname'),
                        'name' => 'lastname',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Company'),
                        'name' => 'company',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Additional name'),
                        'name' => 'additionalname',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'name' => 'email',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Phone'),
                        'name' => 'phone',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('VAT Number'),
                        'name' => 'vatnumber',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Street'),
                        'name' => 'street',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number'),
                        'name' => 'number',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Postcode'),
                        'name' => 'postcode',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('City'),
                        'name' => 'city',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('State'),
                        'name' => 'state',
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Country'),
                        'name' => 'id_country',
                        'required' => true,
                        'options' => array(
                            'query' => $countries_array,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Additional address'),
                        'name' => 'additionaladdress',
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        if ($id_infoshop = RjcarrierInfoshop::getInfoShopID()) {
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_infoshop');
        }

        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
        $helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfigInfoShop';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => RjcarrierInfoshop::getShopData(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
    }

    /**
     * Genera el formulario de información extra obteniendo los inputs desde las clases carriers de companies
     *
     * @return void
     */
    public function renderFormConfigInfoExtra()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration extra information'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $this->fields_form_config_info_extra,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

        $helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfigExtraInfo';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigExtraFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

        $helper->override_folder = '/';

		return $helper->generateForm(array($fields_form));
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
            if(in_array($field, $this->fields_multi_confi)){
                $arry_fields[$field . '[]'] = Tools::getValue($field, Tools::unSerialize(Configuration::get($field, null, $id_shop_group, $id_shop)));
            } else {
                $arry_fields[$field] = Tools::getValue($field, Configuration::get($field, null, $id_shop_group, $id_shop));
            }
        }

        return $arry_fields;
	}

    public function validateFormatTime($time)
    {
        if(preg_match("/(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)/",$time)){
            return true;
        }
        return false;
    }
}