<?php
/**
* 2007-2021 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}
use Ramsey\Uuid\Uuid;

// use Classes\InfoShop as InfoShop;

include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierShipment.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierLabel.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjCarrierInfoPackage.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/InfoShop.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjInfoshop.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/api/DHLapi.php');

class Rj_Carrier extends Module
{
    protected $config_form = false;
    /** @var Shop */
    public $shop;
    public $order;

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

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (parent::install()) {
            if(_PS_VERSION_ >= 1.7){
                $this->registerHook('displayProductAdditionalInfo');
                $this->registerHook('displayAdminOrder');
                $this->registerHook('displayAfterCarrier');
                $this->registerHook('displayBeforeCarrier');
                $this->registerHook('displayHeader');
                $this->registerHook('displayBackOfficeHeader');
            } else {
                $this->registerHook('adminOrder');
                $this->registerHook('displayCarrierList');
                $this->registerHook('orderDetailDisplayed');
                $this->registerHook('displayHeader');
                $this->registerHook('displayProductAdditionalInfo');
                $this->registerHook('displayBackOfficeHeader');
                $this->registerHook('updateCarrier');

            }

            include(dirname(__FILE__).'/sql/install.php');
            
            $this->installTab('AdminParentTabRjCarrier', 'RJ Carrier'); 
            $this->installTab('AdminRJModule', 'Configuration', 'AdminParentTabRjCarrier');
            $this->installTab('AdminRJShipmentsDHL', 'Shipments DHL', 'AdminParentTabRjCarrier');
            $this->installTab('AdminRJCarrier', 'AdminRJCarrier');
            $this->installTab('AdminAjaxRJCarrier', 'AdminAjaxRJCarrier');

            return true;
        }

        return false;
    }

    public function installTab($className, $tabName, $tabParentName = false)
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
        
        $tab->module = $this->name;
        return $tab->add();
    }

    public function uninstall()
    {
        // include(dirname(__FILE__).'/sql/uninstall.php');
        $res = $this->uninstallTab('AdminParentTabRjCarrier');
        $res &= $this->uninstallTab('AdminRJModule');
        $res &= $this->uninstallTab('AdminRJCarrier');
        $res &= $this->uninstallTab('AdminAjaxRJCarrier');

        return parent::uninstall();
    }

    public function uninstallTab($tabName = '')
    {
        //$tab_class = Tools::ucfirst($this->name) . Tools::ucfirst($class_sfx);
        $id_tab    = Tab::getIdFromClassName($tabName);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitConfigDhl') 
        || Tools::isSubmit('submitConfigExtraInfo')
        || Tools::isSubmit('submitConfigInfoShop')){
            $this->_postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        $output .= $this->renderFormInfoCompany();
        $output .= $this->renderFormDhl();
        $output .= $this->renderFormConfigExtraInfo();
        return $output;
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

    public function hookDisplayAdminOrder($params)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_shop_group = $this->context->shop->id_shop_group;
        $id_lang = $this->context->language->id;
        $infoShop = [];
        $id_order = (int)$params['id_order'];
        $name_carrier = '';
        $generarEnvio = false;

        $this->order = new Order($id_order);
        $infoCustomer = new Address($this->order->id_address_delivery);
        

        if (Tools::isSubmit('submitFormPackCarrier') || Tools::isSubmit('submitSavePackSend')) {
            $infoPackage = $this->setPackCarrier($id_order);
            if(Tools::isSubmit('submitSavePackSend')){
                $generarEnvio = true;
            }
        }else {
            $infoPackage = RjCarrierInfoPackage::getDataPackage($id_order);
        }

        // obtener contrareembolso
        $datosOrden = '';
        if(!isset($infoPackage['price_contrareembolso'])){
            $contrareembolso = Configuration::get('RJ_MODULE_CONTRAREEMBOLSO', null, $id_shop_group, $id_shop);
            if(!$contrareembolso){
                $this->warning[] = $this->l('Aun no esta configurado el contrareembolso, debe configurarlo en opciones del modulo!.');
            }
            
            if($contrareembolso === 'codfee'){
                $datosOrden = $this->order->getFields();
                $infoPackage['price_contrareembolso'] = $datosOrden['total_paid_tax_incl'];
            }
        }


        if (Tools::isSubmit('submitDeleteShipment')) {
            $rjcarrierShipment = new RjcarrierShipment((int)Tools::getValue('id_shipment'));
            if(!$rjcarrierShipment->delete()){
                $this->errors[] = $this->l('No se puede eliminar el envío revisar su estado!.');
            } else{
                $this->success[] = $this->l('Se ha eliminado el envío.');
            }
        }

        $carriers = Carrier::getCarriers((int) $id_lang);
        $activeDHL = false;
        if($infoPackage['id_reference_carrier']){
            $name_carrier = Carrier::getCarrierByReference((int)$infoPackage['id_reference_carrier'], $id_lang);
            if($infoPackage['id_reference_carrier'] == Configuration::get('RJ_DHL_ID_REFERENCE_CARRIER', null, $id_shop_group, $id_shop)){
                $activeDHL = true;
            }
        }

        $infoShop = InfoShop::getShopData();

        $infoOrder = array(
            'link' => $this->context->link,
            'order_id' => $id_order,
            'infoPackage' => $infoPackage,
            'infoCustomer' => (array)$infoCustomer,
            'infoShop' => $infoShop,
            'carriers' => $carriers,
            'name_carrier' => $name_carrier->name,
            'activeDHL' => $activeDHL,
            'url_ajax' => $this->context->link->getAdminLink('AdminAjaxRJCarrier'),
        );

        if (Tools::isSubmit('submitShipment') || $generarEnvio){
            $this->selectShipment($infoOrder);
        }
        
        $shipment = RjcarrierShipment::getShipmentByIdOrder($id_order);
        if($shipment){
            $infoOrder['shipment'] = $shipment;
            $infoOrder['labels'] = RjcarrierLabel::getLabelsByIdShipment($shipment['id_shipment']);
        }

        $infoOrder['notifications'] = $this->prepareNotifications();
        $this->context->smarty->assign($infoOrder);

        $this->_html .= $this->display(__FILE__, 'admin-order.tpl');

        return $this->_html;
    }

    public function selectShipment($infoOrder){

        $id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();

        $id_reference_carrier = $infoOrder['infoPackage']["id_reference_carrier"];

        $dhl = Configuration::get('RJ_DHL_ID_REFERENCE_CARRIER', null, $id_shop_group, $id_shop);
        switch ($id_reference_carrier) {
            case $dhl:
                $this->dhlshipment($infoOrder);
                break;
            default:
                $this->errors[] = $this->l('No existe ese transportista.');
                break;
        }

    }

    public function dhlshipment($infoOrder){
        $shipmentId = RjcarrierShipment::getShipmentIdByIdOrder($infoOrder['order_id']);

        if(!$shipmentId){
            $dhlapi = new DHLapi();

            $body = $dhlapi->generateBodyShipment($infoOrder);

            $dataShipment = $dhlapi->postShipment($body);
            
            if(!isset($dataShipment->shipmentId)){
                $this->errors[] = $this->l('Algo esta mal en la información del envío.');
                return false;
            }

            $this->saveShipment($dataShipment, $infoOrder);
            $idShipment = RjcarrierShipment::getIdShipmentByIdOrder($infoOrder['order_id']);
            $this->saveLabels($idShipment, $dataShipment);
            // $dataShipment = $dhlapi->getShipment($dataShipment->shipmentId);
            
        } else{
            $this->errors[] = $this->l('Ya existe un envío para este pedido.');

            return false;
        }
        $this->success[] = $this->l('Envio realizado con exito.');
        return true;

    }

    public function saveShipment($dataShipment, $infoOrder)
    {
        $shipment = new RjcarrierShipment();
        $shipment->shipmentid = $dataShipment->shipmentId;
        $shipment->id_infopackage = $infoOrder['infoPackage']['id'];
        $shipment->id_order = $infoOrder['order_id'];
        $shipment->product = $dataShipment->product;
        $shipment->order_reference = $dataShipment->orderReference;

        $shipment->add();
        return true;
    }

    public function saveLabels($idShipment, $dataShipment)
    {        
        $infoLabels = $dataShipment->pieces;
        $dhlapi = new DHLapi();
        foreach ($infoLabels as $label) {
            $labelapi = $dhlapi->getLabel($label->labelId);
            $carrierLabel = new RjcarrierLabel();
            $carrierLabel->id_shipment = $idShipment;
            $carrierLabel->labelid = $labelapi->labelId;
            $carrierLabel->label_type = $labelapi->labelType;
            $carrierLabel->parcel_type = $labelapi->parcelType;
            $carrierLabel->tracker_code = $labelapi->trackerCode;
            $carrierLabel->piece_number = $labelapi->pieceNumber;
            $carrierLabel->routing_code = $labelapi->routingCode;
            $carrierLabel->userid = $labelapi->userId;
            $carrierLabel->organizationid = $labelapi->organizationId;
            $carrierLabel->order_reference = $labelapi->orderReference;
            $carrierLabel->pdf = $labelapi->pdf;

            $carrierLabel->add();
        }
        return true;
    }

    /**
     * Set data db table rjcarrier - data del paquete order
     *
     * @param [int] $id_order
     * @return void
     */
    private function setPackCarrier($id_order)
    {
        if (Tools::getValue('id_infopackage')) {
            $rjCarrierInfoPackage = new RjCarrierInfoPackage((int)Tools::getValue('id_infopackage'));

            if (!Validate::isLoadedObject($rjCarrierInfoPackage))
            {
                $this->_html .= $this->displayError($this->l('Invalid slide ID'));
                return false;
            }
        } else {
            $rjCarrierInfoPackage = new RjCarrierInfoPackage();
        }

        $rjCarrierInfoPackage->id_order = (int)$id_order;
        $rjCarrierInfoPackage->id_reference_carrier = (int)Tools::getValue('id_reference_carrier');
        $rjCarrierInfoPackage->packages = (int)Tools::getValue('rj_packages');
        $rjCarrierInfoPackage->price_contrareembolso = Tools::getValue('rj_price_contrareembolso');
        $rjCarrierInfoPackage->weight = Tools::getValue('rj_weight');
        $rjCarrierInfoPackage->length = Tools::getValue('rj_length');
        $rjCarrierInfoPackage->width = Tools::getValue('rj_width');
        $rjCarrierInfoPackage->height = Tools::getValue('rj_height');
        $rjCarrierInfoPackage->message = Tools::getValue('rj_message');

        if (!Tools::getValue('id_infopackage'))
        {
            if (!$rjCarrierInfoPackage->add())
            $this->errors[] = $this->l('The transport could not be added.');
        }elseif (!$rjCarrierInfoPackage->update()){
            $this->errors[] = $this->l('The transport could not be updated.');
        } 
        $this->success[] = $this->l('Information successfully updated.');

        return (array)$rjCarrierInfoPackage;
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
        $errors = array();
        $shop_context = Shop::getContext();

        if (Tools::isSubmit('submitConfigDhl')) {
            $shop_groups_list = array();
            $shops = Shop::getContextListShopID();

            foreach ($shops as $shop_id) {
                $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                $res = Configuration::updateValue('RJ_DHL_ACCOUNID', Tools::getValue('RJ_DHL_ACCOUNID'), false, $shop_group_id, $shop_id);
                $res = Configuration::updateValue('RJ_DHL_USERID', Tools::getValue('RJ_DHL_USERID'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('RJ_DHL_KEY', Tools::getValue('RJ_DHL_KEY'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('RJ_DHL_KEY_DEV', Tools::getValue('RJ_DHL_KEY_DEV'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('RJ_DHL_URL_PRO', Tools::getValue('RJ_DHL_URL_PRO'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('RJ_DHL_URL_DEV', Tools::getValue('RJ_DHL_URL_DEV'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('RJ_DHL_ID_REFERENCE_CARRIER', Tools::getValue('RJ_DHL_ID_REFERENCE_CARRIER'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('RJ_DHL_ENV', Tools::getValue('RJ_DHL_ENV'), false, $shop_group_id, $shop_id);
            }

            /* Update global shop context if needed*/
            switch ($shop_context) {
                case Shop::CONTEXT_ALL:
                    $res = Configuration::updateValue('RJ_DHL_ACCOUNID', Tools::getValue('RJ_DHL_ACCOUNID'));
                    $res = Configuration::updateValue('RJ_DHL_USERID', Tools::getValue('RJ_DHL_USERID'));
                    $res &= Configuration::updateValue('RJ_DHL_KEY', Tools::getValue('RJ_DHL_KEY'));
                    $res &= Configuration::updateValue('RJ_DHL_KEY_DEV', Tools::getValue('RJ_DHL_KEY_DEV'));
                    $res &= Configuration::updateValue('RJ_DHL_URL_PRO', Tools::getValue('RJ_DHL_URL_PRO'));
                    $res &= Configuration::updateValue('RJ_DHL_URL_DEV', Tools::getValue('RJ_DHL_URL_DEV'));
                    $res &= Configuration::updateValue('RJ_DHL_ID_REFERENCE_CARRIER', Tools::getValue('RJ_DHL_ID_REFERENCE_CARRIER'));
                    $res &= Configuration::updateValue('RJ_DHL_ENV', Tools::getValue('RJ_DHL_ENV'));
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('RJ_DHL_ACCOUNID', Tools::getValue('RJ_DHL_ACCOUNID'), false, $shop_group_id);
                            $res = Configuration::updateValue('RJ_DHL_USERID', Tools::getValue('RJ_DHL_USERID'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_KEY', Tools::getValue('RJ_DHL_KEY'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_KEY_DEV', Tools::getValue('RJ_DHL_KEY_DEV'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_URL_PRO', Tools::getValue('RJ_DHL_URL_PRO'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_URL_DEV', Tools::getValue('RJ_DHL_URL_DEV'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_ID_REFERENCE_CARRIER', Tools::getValue('RJ_DHL_ID_REFERENCE_CARRIER'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_ENV', Tools::getValue('RJ_DHL_ENV'), false, $shop_group_id);
                        }
                    }
                    break;
                case Shop::CONTEXT_GROUP:
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('RJ_DHL_ACCOUNID', Tools::getValue('RJ_DHL_ACCOUNID'), false, $shop_group_id);
                            $res = Configuration::updateValue('RJ_DHL_USERID', Tools::getValue('RJ_DHL_USERID'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_KEY', Tools::getValue('RJ_DHL_KEY'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_KEY_DEV', Tools::getValue('RJ_DHL_KEY_DEV'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_URL_PRO', Tools::getValue('RJ_DHL_URL_PRO'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_URL_DEV', Tools::getValue('RJ_DHL_URL_DEV'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_ID_REFERENCE_CARRIER', Tools::getValue('RJ_DHL_ID_REFERENCE_CARRIER'), false, $shop_group_id);
                            $res &= Configuration::updateValue('RJ_DHL_ENV', Tools::getValue('RJ_DHL_ENV'), false, $shop_group_id);
                        }
                    }
                    break;
            }

            if (!$res) {
                $errors[] = $this->displayError($this->l('The configuration could not be updated.'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
            }
        }elseif (Tools::isSubmit('submitConfigExtraInfo')) {
            $shop_groups_list = array();
            $shops = Shop::getContextListShopID();

            foreach ($shops as $shop_id) {
                $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                $res = Configuration::updateValue('RJ_ETIQUETA_TRANSP_PREFIX', Tools::getValue('RJ_ETIQUETA_TRANSP_PREFIX'), false, $shop_group_id, $shop_id);
                $res = Configuration::updateValue('RJ_MODULE_CONTRAREEMBOLSO', Tools::getValue('RJ_MODULE_CONTRAREEMBOLSO'), false, $shop_group_id, $shop_id);
            }

            /* Update global shop context if needed*/
            switch ($shop_context) {
                case Shop::CONTEXT_ALL:
                    $res = Configuration::updateValue('RJ_ETIQUETA_TRANSP_PREFIX', Tools::getValue('RJ_ETIQUETA_TRANSP_PREFIX'));
                    $res = Configuration::updateValue('RJ_MODULE_CONTRAREEMBOLSO', Tools::getValue('RJ_MODULE_CONTRAREEMBOLSO'));
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('RJ_ETIQUETA_TRANSP_PREFIX', Tools::getValue('RJ_ETIQUETA_TRANSP_PREFIX'), false, $shop_group_id);
                            $res = Configuration::updateValue('RJ_MODULE_CONTRAREEMBOLSO', Tools::getValue('RJ_MODULE_CONTRAREEMBOLSO'), false, $shop_group_id);
                        }
                    }
                    break;
                case Shop::CONTEXT_GROUP:
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('RJ_ETIQUETA_TRANSP_PREFIX', Tools::getValue('RJ_ETIQUETA_TRANSP_PREFIX'), false, $shop_group_id);
                            $res = Configuration::updateValue('RJ_MODULE_CONTRAREEMBOLSO', Tools::getValue('RJ_MODULE_CONTRAREEMBOLSO'), false, $shop_group_id);
                        }
                    }
                    break;
            }

            if (!$res) {
                $errors[] = $this->displayError($this->l('The configuration could not be updated.'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
            }
        }elseif(Tools::isSubmit('submitConfigInfoShop')){
            
            if (Tools::getValue('id_infoshop')) {
                $infoshop = new RjInfoshop((int)Tools::getValue('id_infoshop'));
                if (!Validate::isLoadedObject($infoshop)) {
                    $this->_html .= $this->displayError($this->getTranslator()->trans('Invalid infoshop ID', array(), 'Modules.rj_carrier.Admin'));
                    return false;
                }
            } else {
                $infoshop = new RjInfoshop();
            }

            $infoshop->firstname  = Tools::getValue('firstname');
            $infoshop->lastname  = Tools::getValue('lastname');
            $infoshop->company  = Tools::getValue('company');
            $infoshop->additionalname  = Tools::getValue('additionalname');
            $infoshop->countrycode  = Tools::getValue('countrycode');
            $infoshop->city  = Tools::getValue('city');
            $infoshop->street  = Tools::getValue('street');
            $infoshop->number  = Tools::getValue('number');
            $infoshop->postcode  = Tools::getValue('postcode');
            $infoshop->additionaladdress  = Tools::getValue('additionaladdress');
            $infoshop->isbusiness  = (Tools::getValue('isbusiness')) ? true : false;
            $infoshop->addition  = Tools::getValue('addition');
            $infoshop->email  = Tools::getValue('email');
            $infoshop->phone  = Tools::getValue('phone');
            $infoshop->vatnumber  = Tools::getValue('vatnumber');
            $infoshop->eorinumber  = Tools::getValue('eorinumber');

            if (!Tools::getValue('id_infoshop')) {
                if (!$infoshop->add()) {
                    $errors[] = $this->displayError($this->getTranslator()->trans('The infoshop could not be added.', array(), 'Modules.rj_carrier.Admin'));
                }
            } elseif (!$infoshop->update()) {
                $errors[] = $this->displayError($this->getTranslator()->trans('The infoshop could not be updated.', array(), 'Modules.rj_carrier.Admin'));
            }
        }

        /* Display errors if needed */
		if (count($errors))
            $this->_html .= $this->displayError(implode('<br />', $errors));
        elseif (Tools::isSubmit('submitConfigInfoShop') && Tools::getValue('id_infoshop')){
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        } elseif (Tools::isSubmit('submitConfigInfoShop')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=3&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }
    }

    public function renderFormInfoCompany()
    {

        $countries = Country::getCountries((int) $this->context->language->id);
        foreach ($countries as $country) {
            // $this->statuses_array[$status['id_order_state']] = $status['name'];
            $countries_array[] =  array(
                'id' => $country['iso_code'],
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
                        'label' => $this->l('Name'),
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
                        'label' => $this->l('EORI Number'),
                        'name' => 'eorinumber',
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
                        'label' => $this->l('Postcode'),
                        'name' => 'postcode',
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
                        'label' => $this->l('City'),
                        'name' => 'city',
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Country'),
                        'name' => 'countrycode',
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

        if ($id_infoshop = RjInfoshop::getInfoShopID()) {
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
			'fields_value' => InfoShop::getShopData(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
    }

    public function renderFormDhl()
    {
        $carriers = Carrier::getCarriers((int) $this->context->language->id);
        foreach ($carriers as $carrier) {
            $carrier_array[] =  array(
                'id' => $carrier['id_reference'],
                'name' => $carrier['name']
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('DHL imformation'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('accountId'),
                        'name' => 'RJ_DHL_ACCOUNID',
                        'required' => true,
                        'class' => 'fixed-width-lg'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('user Id'),
                        'name' => 'RJ_DHL_USERID',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Key PRO'),
                        'name' => 'RJ_DHL_KEY',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Key DEV'),
                        'name' => 'RJ_DHL_KEY_DEV',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url Production'),
                        'name' => 'RJ_DHL_URL_PRO',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// .')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url Develop'),
                        'name' => 'RJ_DHL_URL_DEV',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// .')
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select carrier DHL'),
                        'name' => 'RJ_DHL_ID_REFERENCE_CARRIER',
                        'options' => array(
                            'query' => $carrier_array,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
						'type' => 'switch',
						'label' => $this->l('Modo producción'),
						'name' => 'RJ_DHL_ENV',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Production')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Develop')
							)
						),
					)
                ),
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
		$helper->submit_action = 'submitConfigDhl';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValuesDhl(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
    }
    
    public function getConfigFieldsValuesDhl()
	{
		$id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();

		return array(
			'RJ_DHL_ACCOUNID' => Tools::getValue('RJ_DHL_ACCOUNID', Configuration::get('RJ_DHL_ACCOUNID', null, $id_shop_group, $id_shop)),
			'RJ_DHL_USERID' => Tools::getValue('RJ_DHL_USERID', Configuration::get('RJ_DHL_USERID', null, $id_shop_group, $id_shop)),
			'RJ_DHL_KEY' => Tools::getValue('RJ_DHL_KEY', Configuration::get('RJ_DHL_KEY', null, $id_shop_group, $id_shop)),
			'RJ_DHL_KEY_DEV' => Tools::getValue('RJ_DHL_KEY_DEV', Configuration::get('RJ_DHL_KEY_DEV', null, $id_shop_group, $id_shop)),
			'RJ_DHL_URL_PRO' => Tools::getValue('RJ_DHL_URL_PRO', Configuration::get('RJ_DHL_URL_PRO', null, $id_shop_group, $id_shop)),
			'RJ_DHL_URL_DEV' => Tools::getValue('RJ_DHL_URL_DEV', Configuration::get('RJ_DHL_URL_DEV', null, $id_shop_group, $id_shop)),
			'RJ_DHL_ID_REFERENCE_CARRIER' => Tools::getValue('RJ_DHL_ID_REFERENCE_CARRIER', Configuration::get('RJ_DHL_ID_REFERENCE_CARRIER', null, $id_shop_group, $id_shop)),
			'RJ_DHL_ENV' => Tools::getValue('RJ_DHL_ENV', Configuration::get('RJ_DHL_ENV', null, $id_shop_group, $id_shop))
		);
	}

    public function renderFormConfigExtraInfo()
    {
        $this->l('No se puede eliminar el envío revisar su estado!.');
        $modulesPay = self::getModulesPay();
        $modules_array[] =  array(
            'id' => '',
            'name' => ''
        );
        foreach ($modulesPay as $module) {
            $modules_array[] =  array(
                'id' => $module['name'],
                'name' => $module['name']
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration extra information'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Prefix etiqueta'),
                        'name' => 'RJ_ETIQUETA_TRANSP_PREFIX',
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Module contrareembolso'),
                        'name' => 'RJ_MODULE_CONTRAREEMBOLSO',
                        'options' => array(
                            'query' => $modules_array,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                ),
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
			'fields_value' => $this->getConfigFieldsValuesEtiqueta(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValuesEtiqueta()
	{
		$id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();

		return array(
			'RJ_ETIQUETA_TRANSP_PREFIX' => Tools::getValue('RJ_ETIQUETA_TRANSP_PREFIX', Configuration::get('RJ_ETIQUETA_TRANSP_PREFIX', null, $id_shop_group, $id_shop)),
			'RJ_MODULE_CONTRAREEMBOLSO' => Tools::getValue('RJ_MODULE_CONTRAREEMBOLSO', Configuration::get('RJ_MODULE_CONTRAREEMBOLSO', null, $id_shop_group, $id_shop)),
		);
	}


    public static function getModulesPay()
    {
        $id_shop = Shop::getContextShopID();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT m.`name`  FROM `'._DB_PREFIX_.'module` m
        INNER JOIN `'._DB_PREFIX_.'module_carrier` mc ON m.`id_module` = mc.`id_module`
        WHERE mc.`id_shop` = ' . $id_shop . '
        GROUP BY m.`id_module`');
    }
}