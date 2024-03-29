<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Roanja\Module\RjCarrier\Carrier;

use Roanja\Module\RjCarrier\lib\Common;

use Roanja\Module\RjCarrier\Model\RjcarrierCompany;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierShipment;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\Model\RjcarrierInfoPackage;
use Roanja\Module\RjCarrier\Model\RjcarrierLog;
use Roanja\Module\RjCarrier\lib\Pdf\RjPDF;

use Configuration;
use Db;
use Module;
use Tools;
use Language;
use Shop;
use Validate;
use HelperForm;
use HelperList;
use Carrier;
use Context;
use Order;

/**
 * Class CarrierCompany.
 */
class CarrierCompany extends Module
{

    /** @var string Nombre unico de transportista */
    public $name_carrier = 'name carrier';

    
    /** @var string Nombre corto del transportista siglas ejemp: CEX Correo Express */
    public $shortname = 'DEF';
    public $display_pdf = 'S';
    public $label_type = 'B2X_Generic_A4_Third';
    public $show_create_label = false;

    /** @var array Campos de configuración */
    protected $fields_config = [];

    protected $fields_config_info_extra = [
        [
            'name' => 'RJ_ETIQUETA_TRANSP_PREFIX',
            'require' => false,
            'type' => 'string'
        ],
        [
            'name' => 'RJ_MODULE_CONTRAREEMBOLSO',
            'require' => true,
            'type' => 'float'
        ]
    ];

    /** @var array Campos del formulario configuración */
    public $fields_form;

    public $fields_form_extra;

    public $context;
    public $_html;

    public function __construct()
    {
        $this->module = 'rj_carrier';
        $this->name = 'rj_carrier';
        parent::__construct();

    }

    public function renderConfig() 
    {
        if (Tools::isSubmit('add_Type_shipment_'.$this->shortname) 
            || (Tools::isSubmit('update_type_shipment_'.$this->shortname) 
            && Tools::isSubmit('id_type_shipment') 
            && RjcarrierTypeShipment::typeShipmentExists((int)Tools::getValue('id_type_shipment')))
        ) {
            $this->_html .= $this->renderFormTypeShipment();
        } else {

            $this->_postProcess();

            $this->_html .= $this->renderFormConfig();
            $this->_html .= $this->viewAddTypeShipment();
            $this->_html .= $this->renderListTypeShipment();
        }
        
        return $this->_html;
    }

    public function viewAddTypeShipment()
    {
        $add = 'add_Type_shipment_'. $this->shortname;
        $this->context->smarty->assign([
            'link' =>$this->context->link->getAdminLink('AdminModules', true, [],[
                'configure'=> $this->module,
                'tab_module' => $this->tab,
                'tab_form' => $this->shortname,
                $add => '1',
            ]),
            'company' => $this->shortname,
        ]);
        
        return $this->display($this->_path, '/views/templates/hook/create-type-shipment.tpl');
    }

    public function getConfigFieldsExtra()
    {
        return $this->fields_config_info_extra;
    }

    public function getFieldsFormConfigExtra()
    {
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

        return [
            [
                'type' => 'text',
                'label' => $this->l('Prefix etiqueta'),
                'name' => 'RJ_ETIQUETA_TRANSP_PREFIX',
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'select',
                'label' => $this->l('Module contrareembolso'),
                'name' => 'RJ_MODULE_CONTRAREEMBOLSO',
                'options' => [
                    'query' => $modules_array,
                    'id' => 'id',
                    'name' => 'name'
                ]
            ]
        ];
    }

    /**
     * Valida que los carries seleccionados hayan sido configurados
     *
     * @return void
     */
    public function validationConfiguration()
    {
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = Context::getContext()->shop->id_shop_group;
        $warning = [];

        // valida los campos de configuaración de los carrier company
        foreach ($this->fields_config as $value) {
            if($value['require'] && !Configuration::get($value['name'], null, $id_shop_group, $id_shop)){
                $warning[] = $this->getTranslator()->trans('Required data module configuration!. ', [], 'Modules.Rj_Carrier.Admin') . 
                $value['name'];
            }
        }

        // valida los campos de configuaración extra de los carrier company
        foreach ($this->fields_config_info_extra as $value) {
            if($value['require'] && !Configuration::get($value['name'], null, $id_shop_group, $id_shop)){
                $warning[] = $this->getTranslator()->trans('Required data the module extra configuration!. ', [], 'Modules.Rj_Carrier.Admin') . 
                $value['name'];
            }
        }

        if(count($warning)){
            return $warning;
        }

        return false;
    }

    protected function _postProcess()
	{
        if (Tools::isSubmit('submitConfigTypeShipment'. $this->shortname)) {
            $this->_postProcessTypeShipment();
        } elseif (Tools::isSubmit('submitConfig'. $this->shortname)) {
            $res = true;
            $shop_context = Shop::getContext();
    
            $shop_groups_list = array();
            $shops = Shop::getContextListShopID();
    
            foreach ($shops as $shop_id) {
                $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);
    
                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }
                
                foreach ($this->fields_config as $field) {
                    if($field['type'] === 'password') {
                        if(Tools::getValue($field['name']))
                            $res &=  Configuration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])), false, $shop_group_id, $shop_id);
                    } else {
                        $res &=  Configuration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id, $shop_id);
                    }
                }
            }
    
            switch ($shop_context) {
                case Shop::CONTEXT_ALL:
                    foreach ($this->fields_config as $field) {
                        if($field['type'] === 'password') {
                            if(Tools::getValue($field['name']))
                                $res &= Configuration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])));
                        } else {
                            $res &= Configuration::updateValue($field['name'], Tools::getValue($field['name']));
                        }
                    }
                    
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            if($field['type'] === 'password') {
                                if(Tools::getValue($field['name']))
                                    $res &= Configuration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])), false, $shop_group_id);
                            }
                            foreach ($this->fields_config as $field) {
                                $res &= Configuration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                            }
                        }
                    }
                    break;
                case Shop::CONTEXT_GROUP:
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            foreach ($this->fields_config as $field) {
                                if($field['type'] === 'password') {
                                    if(Tools::getValue($field['name']))
                                        $res &= Configuration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])), false, $shop_group_id);
                                } else {
                                    $res &= Configuration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                                }
                            }
                        }
                    }
                    break;
            }
    
            if (!$res)
                $this->_html .= $this->displayError($this->l('The Configuration could not be added.'));
            else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 6, 'module_name' => $this->name, 'tab_form' => $this->shortname]));
            }
        } elseif (Tools::isSubmit('status_type_shipment_'.$this->shortname)) {
            $typeShipment = new RjcarrierTypeShipment((int) Tools::getValue('id_type_shipment'));
            if ($typeShipment->id) {
                $typeShipment->active = (int) (!$typeShipment->active);
                $typeShipment->save();
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 4, 'module_name' => $this->name, 'tab_form' => $this->shortname]));
        } elseif (Tools::isSubmit('delete_type_shipment_'. $this->shortname)) {
            $typeShipment = new RjcarrierTypeShipment((int) Tools::getValue('id_type_shipment'));
            if(!$typeShipment->delete()){
                $this->_html .= $this->displayError($this->l('Could not delete.'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 1, 'module_name' => $this->name, 'tab_form' => $this->shortname]));
            }
        }
    }

    protected function _postProcessTypeShipment()
    {
        if (Tools::isSubmit('id_type_shipment') && RjcarrierTypeShipment::typeShipmentExists((int)Tools::getValue('id_type_shipment'))) {
            $typeShipment = new RjcarrierTypeShipment((int)Tools::getValue('id_type_shipment'));
        } else {
            $typeShipment = new RjcarrierTypeShipment();
        }

        $typeShipment->id_carrier_company = (int)Tools::getValue('id_carrier_company');
        $typeShipment->name = Tools::getValue('name');
        $typeShipment->id_bc = Tools::getValue('id_bc');
        $typeShipment->id_reference_carrier = (int)Tools::getValue('id_reference_carrier');
        $typeShipment->active = (boolean)Tools::getValue('active');

        if (!Tools::getValue('id_type_shipment')) {
            if (!$typeShipment->add()) {
                $this->_html .= $this->displayError($this->l('The Type Shipment could not be added.'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 3, 'module_name' => $this->name, 'tab_form' => $this->shortname]));
            }
        } elseif (!$typeShipment->update()) {
            $this->_html = $this->displayError($this->l('The Type Shipment could not be updated.'));
        } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'conf' => 6, 'module_name' => $this->name, 'tab_form' => $this->shortname]));
        }
    }

    /**
     * Devuelve shortname comapany a partir del id_refernce_carrier
     *
     * @param string $id_carrier
     * @return string
     */
    public static function getInfoCompanyByIdReferenceCarrier($id_reference_carrier)
    {
        $type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdReferenceCarrier($id_reference_carrier);
        if($type_shipment){
            $carrier_company = new RjcarrierCompany((int)$type_shipment['id_carrier_company']);
            return  $carrier_company->getFields();
        } else {
            $carries_company = RjcarrierCompany::getCarrierCompany();
        }

        return $carries_company[0];
    }

    public function renderFormConfig()
    {

        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfig' . $this->shortname;
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->module.'&tab_module='.$this->tab.'&module_name='.$this->module;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($this->fields_form));
    }

    /**
     * Devuelve el listado de type shipment 
     *
     * @return void
     */
    private function renderListTypeShipment()
    {

        $carrier_company = RjcarrierCompany::getCarrierCompanyByShortname($this->shortname);
        $carrier_type_shipments = RjcarrierTypeShipment::getTypeShipmentsByIdCarrierCompany($carrier_company['id_carrier_company']);

        if(!$carrier_type_shipments){
            return;
        }

        $fields_list = array(
            'id_type_shipment' => array(
                'title' => $this->l('Id'),
                'width' => 140,
                'type' => 'text',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 140,
                'type' => 'text',
            ),
            'carrier_company' => array(
                'title' => $this->l('Company'),
                'width' => 140,
                'type' => 'text',
            ),
            'shortname' => array(
                'title' => $this->l('shortname'),
                'width' => 140,
                'type' => 'text',
            ),
            'id_bc' => array(
                'title' => $this->l('id bc'),
                'width' => 140,
                'type' => 'text',
            ),
            'reference_carrier' => array(
                'title' => $this->l('reference carrier'),
                'width' => 140,
                'type' => 'text',
            ),
            'active' => array(
                'title' => $this->l('active'),
                'active' => 'status',
                'type' => 'bool',
            ),
        );

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->trans('Type shipment', [], 'Modules.rj_carrier.Admin');
        $helper_list->shopLinkType = '';
        $helper_list->no_link = false;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = true;
        $helper_list->identifier = 'id_type_shipment';
        $helper_list->table = '_type_shipment_'.$this->shortname;
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name .'&tab_form='.$this->shortname;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = ['edit', 'delete'];

        $helper_list->listTotal = count($carrier_type_shipments);

        return $helper_list->generateList($carrier_type_shipments, $fields_list);
    }

    public function renderFormTypeShipment()
    {
        $carriers = Carrier::getCarriers((int) $this->context->language->id);
        $fieldsValuesTypeShipment = $this->getConfigFieldsValuesTypeShipment();
        
        $carrier_array[] =  [
            'id' => '',
            'name' => ''
        ];
        
        foreach ($carriers as $carrier) {
            if($fieldsValuesTypeShipment['id_reference_carrier'] == $carrier['id_reference'] || !RjcarrierTypeShipment::typeShipmentExistsByIdReference($carrier['id_reference'])){
                $carrier_array[] =  [
                    'id' => $carrier['id_reference'],
                    'name' => $carrier['name']
                ];
            }
        }

        $carrier_company = RjcarrierCompany::getCarrierCompanyByShortname($this->shortname);

        $company_array[] =  [
            'id' => $carrier_company['id_carrier_company'],
            'name' => $carrier_company['name']
        ];


        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Type shipment relations'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'name',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Select Company'),
                        'name' => 'id_carrier_company',
                        'options' => [
                            'query' => $company_array,
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('id bc'),
                        'name' => 'id_bc',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Select carrier'),
                        'name' => 'id_reference_carrier',
                        'desc' => $this->l('Solo se veran transportistas los que no han sido asignados'),
                        'options' => [
                            'query' => $carrier_array,
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Enabled', [], 'Admin.Global'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', [], 'Admin.Global')
                                ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', [], 'Admin.Global')
                            ]
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ],
        ];

        if (Tools::isSubmit('id_type_shipment') && RjcarrierTypeShipment::typeShipmentExists((int)Tools::getValue('id_type_shipment'))) {
            $fields_form['form']['input'][] = ['type' => 'hidden', 'name' => 'id_type_shipment'];
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitConfigTypeShipment'. $this->shortname;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->module.'&tab_module='.$this->tab.'&tab_form='.$this->shortname;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $fieldsValuesTypeShipment,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValuesTypeShipment()
	{
        $fields = array();

        if (Tools::isSubmit('id_type_shipment') && RjcarrierTypeShipment::typeShipmentExists((int)Tools::getValue('id_type_shipment'))) {
            $typeShipment = new RjcarrierTypeShipment((int)Tools::getValue('id_type_shipment'));
            $fields['id_type_shipment'] = (int)Tools::getValue('id_type_shipment', $typeShipment->id);
        } else {
            $typeShipment = new RjcarrierTypeShipment();
        }

        $fields['id_carrier_company'] = Tools::getValue('id_carrier_company', $typeShipment->id_carrier_company);
        $fields['name'] = Tools::getValue('name', $typeShipment->name);
        $fields['id_bc'] = Tools::getValue('id_bc', $typeShipment->id_bc);
        $fields['id_reference_carrier'] = Tools::getValue('id_reference_carrier', $typeShipment->id_reference_carrier);
        $fields['active'] = Tools::getValue('active', $typeShipment->active);


        return $fields;
    }

    /**
     * Obtiene los datos de configuración
     *
     * @param array $fields
     * @return array
     */
    public function getConfigFieldsValues()
	{
		$id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $fields = [];

        foreach ($this->fields_config as $field) {
            if($field['type'] === 'password'){
                $fields[$field['name']] = Tools::getValue($field['name'], Common::encrypt('decrypt',Configuration::get($field['name'], null, $id_shop_group, $id_shop)));
            }  else {
                $fields[$field['name']] = Tools::getValue($field['name'], Configuration::get($field['name'], null, $id_shop_group, $id_shop));
            }
        }

        return $fields;
	}

    public function createShipment($shipment)
    {
        $shipment['num_shipment'] = Common::getUUID();
        $info_shipment = $this->saveShipment($shipment);
        $shipment['info_shipment'] = $info_shipment;
        $packages_qty = $shipment['info_package']['quantity'];
        
        for($num_package = 1; $num_package <= $packages_qty; $num_package++) { 
            $rjpdf = new RjPDF($this->shortname, $shipment, RjPDF::TEMPLATE_LABEL, $num_package);
            $pdf = $rjpdf->render($this->display_pdf);

            if ($pdf) {
                $this->saveLabels($info_shipment['id_shipment'], $pdf, $num_package);
            }
        }
        
        return true;
    }

    /**
     * Procesa el request a la api de carrier para obtener las etiquetas
     * Se usa para GOI pero se podria sobreescribir para otro transportista
     * Mirar el funcionamiento en transporte GOI
     *
     * @param int $id_shipment
     * @param int $id_order
     * @return boolean
     */
    public function createLabel($id_shipment, $id_order)
    {
        return true;
    }

    public function saveLabels($id_shipment, $pdf, $num_package = 1)
    {
        $uuid = Common::getUUID();
        $rj_carrier_label = new RjcarrierLabel();
        $rj_carrier_label->id_shipment = $id_shipment;
        $rj_carrier_label->package_id = $uuid;
        $rj_carrier_label->label_type = $this->label_type;
        $rj_carrier_label->tracker_code = 'TC' .$uuid . '-' . $num_package;
        
        if(Common::createFileLabel($pdf, $uuid)){
            $rj_carrier_label->pdf = $uuid;
        }

        if (!$rj_carrier_label->add())
            return false;

        return true;
    }

    /**
     * save data db table rj_carrier_shipment
     *
     * @param array $info_shipment
     * @return obj || boolean
     */
    public function saveShipment($info_shipment, $response = null)
    {
        $id_order = $info_shipment['id_order'];
        $num_shipment = $info_shipment['num_shipment'];
        $id_infopackage = $info_shipment['info_package']['id_infopackage'];
        $id_carrier_company = $info_shipment['info_company_carrier']['id_carrier_company'];

        if (!$id_order) {
            return false;
        }
        
        $id_shipment = RjcarrierShipment::getIdByIdOrder((int)$id_order);
        $order = new Order((int)$id_order);

        if($id_shipment){
            $rj_carrier_shipment = new RjcarrierShipment((int)$id_shipment);
        } else {
            $rj_carrier_shipment = new RjcarrierShipment();
        }

        $rj_carrier_shipment->id_order = (int)$id_order;
        $rj_carrier_shipment->reference_order = $order->reference;
        $rj_carrier_shipment->num_shipment = $num_shipment;
        $rj_carrier_shipment->id_infopackage = (int)$id_infopackage;
        $rj_carrier_shipment->id_carrier_company = (int)$id_carrier_company;
        $rj_carrier_shipment->product = $info_shipment['name_carrier'];
        $rj_carrier_shipment->request = json_encode($info_shipment);
        $rj_carrier_shipment->response = ($response) ? json_encode($response) : null;

        if (!$id_shipment) {
            if (!$rj_carrier_shipment->add())
                return false;
        }elseif (!$rj_carrier_shipment->update()){
            return false;
        } 

        return $rj_carrier_shipment->getFields();
    }

    /**
     * save data db table rj_carrier_infopackage - data del paquete order
     *
     * @return obj
     */
    public static function saveInfoPackage()
    {
        $id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();

        if (Tools::getValue('id_infopackage')) {
            $rj_carrier_infopackage = new RjcarrierInfoPackage((int)Tools::getValue('id_infopackage'));

            if (!Validate::isLoadedObject($rj_carrier_infopackage))
                return false;
        } else {
            $rj_carrier_infopackage = new RjcarrierInfoPackage();
        }

        $hour_from = (Tools::getValue('rj_hour_from')) ? Tools::getValue('rj_hour_from') . ':00' : Configuration::get('RJ_HOUR_FROM', null, $id_shop_group, $id_shop) . ':00';
        $hour_until = (Tools::getValue('rj_hour_until')) ? Tools::getValue('rj_hour_until') . ':00': Configuration::get('RJ_HOUR_UNTIL', null, $id_shop_group, $id_shop) . ':00';

        $rj_carrier_infopackage->id_order = (int)Tools::getValue('id_order');
        $rj_carrier_infopackage->id_reference_carrier = (int)Tools::getValue('id_reference_carrier');
        $rj_carrier_infopackage->quantity = (!(int)Tools::getValue('rj_quantity')) ? 1 : (int)Tools::getValue('rj_quantity');
        $rj_carrier_infopackage->weight = Tools::getValue('rj_weight');
        $rj_carrier_infopackage->length = Tools::getValue('rj_length');
        $rj_carrier_infopackage->cash_ondelivery = Tools::getValue('rj_cash_ondelivery');
        $rj_carrier_infopackage->message = Tools::getValue('rj_message');
        $rj_carrier_infopackage->id_type_shipment = Tools::getValue('id_type_shipment');
        $rj_carrier_infopackage->hour_from = (self::validateFormatTime($hour_from))?$hour_from:'00:00:00';
        $rj_carrier_infopackage->hour_until = (self::validateFormatTime($hour_until))?$hour_until:'00:00:00';
        $rj_carrier_infopackage->width = Tools::getValue('rj_width');
        $rj_carrier_infopackage->height = Tools::getValue('rj_height');

        if (!Tools::getValue('id_infopackage'))
        {
            if (!$rj_carrier_infopackage->add()){
                return false;
            }
        }elseif (!$rj_carrier_infopackage->update()){
            return false;
        } 

        return $rj_carrier_infopackage->getFields();
    }

    public static function getModulesPay()
    {
        $id_shop = Shop::getContextShopID();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT m.`name`  FROM `'._DB_PREFIX_.'module` m
        INNER JOIN `'._DB_PREFIX_.'module_carrier` mc ON m.`id_module` = mc.`id_module`
        WHERE mc.`id_shop` = ' . $id_shop . '
        GROUP BY m.`id_module`');
    }

    public function getPosicionLabel($posicionLabel)
    {
        switch ($posicionLabel) {
            case '1':
                return '0';
                break;
            case '2':
                return '1';
                break;
            case '3':
                return '2';
                break;

            default:
                return '0';
                break;
        }

    }

    public static function validateFormatTime($time)
    {
        if(preg_match("/(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)/",$time)){
            return true;
        }
        return false;
    }

    public static function saveLog($name, $id_order, $body, $response) 
    {
        $rjcarrierLog = new RjcarrierLog();
        $rjcarrierLog->name = $name;
        $rjcarrierLog->id_order = $id_order;
        $rjcarrierLog->request = $body;
        $rjcarrierLog->response = $response;

        $rjcarrierLog->add();
    }
}
