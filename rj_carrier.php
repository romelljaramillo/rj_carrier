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
use Roanja\Module\RjCarrier\Model\RjCarrierConfiguration;

use Roanja\Module\RjCarrier\Carrier\CarrierFactory;

define('IMG_ICON_COMPANY_DIR', 'ruta_icons');

class Rj_Carrier extends Module
{
    protected $order;
    protected $_html;
    protected $_errors = [];
    protected $_warning = [];
    protected $_success = [];
    protected $_info = [];
    protected $fields_additional_config = [];

    const CONFIG_CONTRAREEMBOLSO = 'RJ_MODULE_CONTRAREEMBOLSO';

    const DEFAULT_CARRIER_SHORTNAME = 'DEF';

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
        'AdminRjCarrierCompany' => [
            'name' => 'Carriers Companies',
            'visible' => true,
            'class_name' => 'AdminRjCarrierCompany',
            'parent_class_name' => 'AdminParentTabRjCarrier',
            'icon' => 'local_shipping'
        ],
        'AdminRjShipments' => [
            'name' => 'Shipments',
            'visible' => true,
            'class_name' => 'AdminRjShipments',
            'parent_class_name' => 'AdminParentTabRjCarrier',
            'icon' => 'poll'
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
        ],
        'AdminRjLogs' => [
            'name' => 'Log errors',
            'visible' => true,
            'class_name' => 'AdminRjLogs',
            'parent_class_name' => 'AdminParentTabRjCarrier',
            'icon' => 'warning'
        ]
    ];

    public function __construct()
    {
        $this->name = 'rj_carrier';
        $this->tab = 'administration';
        $this->version = '2.0.5';
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
    public function installTab($tabData)
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
    public function uninstallTab($tabData)
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

    /**
     * Proceso principal para manejar acciones en el backoffice.
     */
    public function getContent()
    {
        if (Tools::isSubmit('add_type_shipment') ||
            Tools::isSubmit('update_type_shipment')
        ) {
            return $this->renderFormTypeShipment();
        }

        $this->_postProcess();

        $tab = Tools::getValue('tab_form');

        $this->context->smarty->assign([
            'notifications' => $this->prepareNotifications(),
            'form_info_shop' => $this->renderFormInfoShop(),
            'form_config_carriers' => $this->renderCarriersConfig(),
            'form_additional_config' => $this->renderAdditionalConfigCarriers(),
            'tab' => $tab
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Renderiza el formulario de tipo de envío.
     *
     * @return void
     */
    private function renderFormTypeShipment()
    {
        $shortname = '';

        if (Tools::isSubmit('id_type_shipment') && RjcarrierTypeShipment::typeShipmentExists((int)Tools::getValue('id_type_shipment'))) {
            $typeShipment = new RjcarrierTypeShipment((int)Tools::getValue('id_type_shipment'));
            $carrier_company = new RjcarrierCompany((int)$typeShipment->id_carrier_company);
            $shortname = $carrier_company->shortname;
        } else {
            $shortname = Tools::getValue('carrier');
        }

        $carrier = $this->getCarrierClass($shortname);

        return $carrier->formTypeShipment();
    }

    /**
     * Renderiza el formulario de información de la tienda.
     *
     * @return void
     */
    public function renderFormInfoShop()
    {
        $countries = Country::getCountries((int) $this->context->language->id);
        $countries_array = array_map(function ($country) {
            return [
                'id' => $country['id_country'],
                'name' => $country['name']
            ];
        }, $countries);

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
                        'validate' => 'isUnsignedInt',
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
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitInfoShopConfig';
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
     * Renderiza los formularios de configuración de cada transportista.
     */
    public function renderCarriersConfig()
    {
        $html = [];
        $carries_company = RjcarrierCompany::getAllCarrierCompany();

        foreach ($carries_company as $company) {
            $carrier = $this->getCarrierClass($company['shortname']);
            $fields_form = $carrier->getFieldsFormConfig();
            $values_form = $carrier->getValuesConfigFields();

            $fields_list = $carrier->getListTypeShipmentFields();
            $values_list = $carrier->getListTypeShipmentValues();

            $html[$carrier->shortname] = $this->formCarrierConfig($carrier->shortname, $fields_form, $values_form)
            . $carrier->viewAddTypeShipment() . $this->listTypeShipment($carrier->shortname, $fields_list, $values_list);
        }

        return $html;
    }

    public function listTypeShipment($shortname, $fields, $values)
    {
        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->trans('Type shipment', [], 'Modules.rj_carrier.Admin');
        $helper_list->shopLinkType = '';
        $helper_list->no_link = false;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = true;
        $helper_list->identifier = 'id_type_shipment';
        $helper_list->table = '_type_shipment';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&shortname=' . $shortname;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = ['edit', 'delete'];

        $helper_list->listTotal = count($values);

        return $helper_list->generateList($values, $fields);
    }

    /**
     * renderiza el formulario de configuración de un transportista.
     *
     * @param [type] $shortname
     * @return void
     */
    private function formCarrierConfig($shortname, $fields, $values)
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCarrierConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$shortname.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields]);
    }

    private function getAdditionalFieldsConfig()
    {
        $carriers_company = RjcarrierCompany::getAllCarrierCompany();
        foreach ($carriers_company as $company) {
            $carrier = $this->getCarrierClass($company['shortname']);
            $fields = $carrier->getAdditionalFormFieldsConfig();
            $this->fields_additional_config = array_merge_recursive($this->fields_additional_config, $fields);
        }
    }

    /**
     * Renderiza el formulario de configuración de la tienda.
     */
    public function renderAdditionalConfigCarriers()
    {
        $this->getAdditionalFieldsConfig();
        return $this->formAdditionalConfig();
    }

    /**
     * Genera el formulario de información extra obteniendo los inputs desde las clases carriers de companies
     *
     * @return void
     */
    public function formAdditionalConfig()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration extra information'),
                    'icon' => 'icon-cogs'
                ],
                'input' => $this->fields_additional_config,
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
		$helper->submit_action = 'submitAdditionalConfig';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = [
			'fields_value' => $this->getAdditionalFieldsConfigValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
        ];

        $helper->override_folder = '/';

		return $helper->generateForm([$fields_form]);
    }

    /**
     * Obtiene los valores de configuración extra.
     */
    private function getAdditionalFieldsConfigValues()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = Shop::getContextShopID();
        $fields = [];

        foreach ($this->fields_additional_config as $field) {
            $fields[$field['name']] = Tools::getValue($field['name'], RjCarrierConfiguration::get($field['name'], $id_shop_group, $id_shop));
        }

        return $fields;
    }

    /**
     * Proceso de guardado de configuración.
     */
    protected function _postProcess()
    {
        if (Tools::isSubmit('submitCarrierConfig')) {

            $this->prepareCarrierConfig();

        } elseif (Tools::isSubmit('submitAdditionalConfig')) {

            $this->saveAdditionalConfig();

        } elseif (Tools::isSubmit('submitInfoShopConfig')) {

            $this->saveInfoShopConfig();

        } elseif (Tools::isSubmit('submitConfigTypeShipment')) {

            $this->prepareConfigTypeShipment();

        } elseif (Tools::isSubmit('status_type_shipment')){

            $carrier = $this->getCarrierClass(Tools::getValue('shortname'));
            $carrier->updateStatusTypeShipment((int)Tools::getValue('id_type_shipment'));

        } elseif (Tools::isSubmit('delete_type_shipment')){

            $carrier = $this->getCarrierClass(Tools::getValue('shortname'));
            $carrier->deleteTypeShipment((int)Tools::getValue('id_type_shipment'));

        }
    }

    /**
     * Guarda la configuración del tipo de envío.
     *
     * @param array $data
     * @return bool
     */
    public function prepareConfigTypeShipment()
    {
        $carrierCompany = new RjcarrierCompany((int)Tools::getValue('id_carrier_company'));
        $carrier = $this->getCarrierClass($carrierCompany->shortname);

        $carrier->saveConfigTypeShipment([
            'id_type_shipment' => (int) Tools::getValue('id_type_shipment'),
            'id_carrier_company' => (int) Tools::getValue('id_carrier_company'),
            'name' => (string) Tools::getValue('name'),
            'id_bc' => (string) Tools::getValue('id_bc'),
            'id_reference_carrier' => (int) Tools::getValue('id_reference_carrier') ?: null,
            'active' => (bool) Tools::getValue('active')
        ]);
    }

    private function prepareCarrierConfig()
    {
        if($shortname = Tools::getValue('tab_module')){
            $carrier = $this->getCarrierClass($shortname);
            $carrier->saveCarrierConfiguration();
        }

        Tools::redirectAdmin($this->context->link->getAdminLink(
            'AdminModules',
            true,
            [],
            [
                'configure' => $this->name,
                'tab_module' => $this->tab,
                'conf' => 6,
                'module_name' => $this->name,
            ]
        ));
    }

    private function saveInfoShopConfig()
    {
        $infoshop = Tools::getValue('id_infoshop') ? new RjcarrierInfoshop((int)Tools::getValue('id_infoshop')) : new RjcarrierInfoshop();

        if (Tools::getValue('id_infoshop') && !Validate::isLoadedObject($infoshop)) {
            $this->_html .= $this->displayError($this->l('Invalid infoshop ID'));
            return false;
        }

        $infoshop->firstname = Tools::getValue('firstname');
        $infoshop->lastname = Tools::getValue('lastname');
        $infoshop->company = Tools::getValue('company');
        $infoshop->additionalname = Tools::getValue('additionalname');
        $infoshop->street = Tools::getValue('street');
        $infoshop->number = Tools::getValue('number');
        $infoshop->postcode = Tools::getValue('postcode');
        $infoshop->city = Tools::getValue('city');
        $infoshop->state = Tools::getValue('state');
        $infoshop->id_country = Tools::getValue('id_country');
        $infoshop->additionaladdress = Tools::getValue('additionaladdress');
        $infoshop->isbusiness = (bool)Tools::getValue('company');
        $infoshop->email = Tools::getValue('email');
        $infoshop->phone = Tools::getValue('phone');
        $infoshop->vatnumber = Tools::getValue('vatnumber');

        $validate = $infoshop->validateFields(false, true);
        if ($validate !== true) {
            $this->_errors[] = $this->l('Required fields missing ') . $validate;
            return false;
        }

        if (!Tools::getValue('id_infoshop') ? !$infoshop->add() : !$infoshop->update()) {
            $this->_html .= $this->displayError($this->l('The infoshop could not be saved.'));
        } else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name,
                'tab_module' => $this->tab,
                'conf' => Tools::getValue('id_infoshop') ? 6 : 3,
                'module_name' => $this->name,
                'tab_form' => 'infoshop'
            ]));
        }
    }

    private function saveAdditionalConfig()
    {
        $res = true;
        $shop_context = Shop::getContext();
        $shop_groups_list = [];
        $shops = Shop::getContextListShopID();

        $this->getAdditionalFieldsConfig();

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);
            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }
            foreach ($this->fields_additional_config as $field) {
                $res &= RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id, $shop_id);
            }
        }

        if ($shop_context === Shop::CONTEXT_ALL) {
            foreach ($this->fields_additional_config as $field) {
                $res &= RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']));
            }
            foreach ($shop_groups_list as $shop_group_id) {
                foreach ($this->fields_additional_config as $field) {
                    $res &= RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                }
            }
        } elseif ($shop_context === Shop::CONTEXT_GROUP) {
            foreach ($shop_groups_list as $shop_group_id) {
                foreach ($this->fields_additional_config as $field) {
                    $res &= RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                }
            }
        }

        if (!$res) {
            $this->_html .= $this->displayError($this->l('The configuration could not be updated.'));
        } else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name,
                'tab_module' => $this->tab,
                'conf' => 6,
                'module_name' => $this->name,
                'tab_form' => 'additionalconfig'
            ]));
        }
    }

    /**
     * Obtiene una instancia de la clase del Carrier . shortname según el shortname.
     *
     * @param string|null $shortname
     * @return CarrierCompany
     */
    protected function getCarrierClass($shortname = self::DEFAULT_CARRIER_SHORTNAME)
    {
        return CarrierFactory::createCarrier($shortname);
    }

    /**
     * Obtiene la información del cliente asociado a una orden.
     *
     * @param int $id_order
     * @return array
     */
    public function getInfoCustomer($id_order)
    {
        $id_lang = Context::getContext()->language->id;

        $sql = 'SELECT o.id_order, a.*, c.email,
                    s.name AS state, cl.name AS country, co.iso_code AS countrycode
                FROM ' . _DB_PREFIX_ . 'orders o
                LEFT JOIN ' . _DB_PREFIX_ . 'address a ON o.id_address_delivery = a.id_address
                LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON o.id_customer = c.id_customer
                LEFT JOIN ' . _DB_PREFIX_ . 'state s ON a.id_state = s.id_state
                LEFT JOIN ' . _DB_PREFIX_ . 'country co ON a.id_country = co.id_country
                LEFT JOIN ' . _DB_PREFIX_ . 'country_lang cl ON co.id_country = cl.id_country AND cl.id_lang = ' . (int) $id_lang . '
                WHERE o.id_order = ' . (int) $id_order;

        return Db::getInstance()->getRow($sql) ?: [];
    }

    /**
     * Procesa la eliminación del envío
     *
     * @param int $id_shipment
     * @return void
     */
    public function deleteShipment($id_shipment)
    {
        $rjcarrierShipment = new RjcarrierShipment((int)$id_shipment);

        if (!$rjcarrierShipment->delete()) {
            $this->_errors[] = $this->l('Cannot delete shipment. Check its status.');
            return false;
        }

        $labels = RjcarrierLabel::getLabelsByShipmentId($id_shipment);
        foreach ($labels as $label) {
            $label = new RjcarrierLabel($label['id_label']);
            if (!$label->delete()) {
                $this->_errors[] = $this->l('Cannot delete label. Check its status.');
                return false;
            }

            $label_path = _PS_MODULE_DIR_ . $this->name . '/labels/' . $label->package_id . '.pdf';
            if (file_exists($label_path) && !unlink($label_path)) {
                $this->_errors[] = $this->l('Cannot delete label file. Check its status.');
                return false;
            }
        }

        $this->_success[] = $this->l('Shipment successfully deleted.');
        return true;
    }

    /**
     * Valida el cambio de transportista de la orden y elimina el envío si ha cambiado
     *
     * @param int $id_order
     * @param int $new_id_reference_carrier
     * @return void
     */
    public function deleteShipmentChangeCarrier($id_order, $id_shipment_old, $new_id_reference_carrier)
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

        $carrier_name = '';

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

        $carrier_name = Carrier::getCarrierByReference((int)$info_package['id_reference_carrier'], $id_lang);
        $info_company_carrier = CarrierCompany::getInfoCompanyByIdReferenceCarrier($info_package['id_reference_carrier']);
        $info_type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdCarrierCompany($info_company_carrier['id_carrier_company']);

        $shipment = [
            'id_order' => $id_order,
            'info_package' => $info_package,
            'info_customer' => $this->getInfoCustomer($id_order),
            'info_shop' => RjcarrierInfoshop::getShopData(),
            'carriers' => Carrier::getCarriers((int)$id_lang),
            'carrier_name' => $carrier_name->name,
            'info_company_carrier' => $info_company_carrier,
            'info_type_shipment' => $info_type_shipment,
            'config_extra_info' => $this->getAdditionalFieldsConfigValues()
        ];

        $class_carrier = $this->getCarrierClass($info_company_carrier["shortname"]);

        if(!$class_carrier->createShipment($shipment)){
            $_errors[] = $this->l('The shipment could not be generated.');
        }
    }

    /**
     * Procesa las acciones de envío
     *
     * @param array $params
     * @return void
     */
    public function hookDisplayAdminOrder($params)
    {
        $id_lang = Context::getContext()->language->id;
        $id_order = $params['id_order'];
        $order = new Order($id_order);

        $info_shipment = RjcarrierShipment::getShipmentByIdOrder($id_order);
        $id_shipment = (int)$info_shipment['id_shipment'] ?? null;

        $info_package = $this->processShipmentActions($id_order, $id_shipment);
        $id_reference_carrier = $info_package['id_reference_carrier'];

        if(!$id_reference_carrier){
            $carrier = new Carrier($order->id_carrier, $id_lang);
            $info_package['id_reference_carrier'] = $carrier->id_reference;
            $id_reference_carrier = $info_package['id_reference_carrier'];
        }else {
            $carrier = Carrier::getCarrierByReference($id_reference_carrier, $id_lang);
        }

        $type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdReferenceCarrier((int)$id_reference_carrier);

        if ($type_shipment) {
            $carrier_company = new RjcarrierCompany((int)$type_shipment['id_carrier_company']);
            $info_company_carrier = $carrier_company->getFields();
            $info_type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdCarrierCompany($id_reference_carrier);
        }

        // Preparar los datos del envío para la vista
        $shipment = [
            'link' => $this->context->link,
            'id_order' => $id_order,
            'reference' => $order->reference,
            'carriers' => Carrier::getCarriers((int)$id_lang),
            'carrier_name' => $carrier->name,
            'url_ajax' => $this->context->link->getAdminLink('AdminAjaxRjCarrier'),
            'config_extra_info' => $this->getAdditionalFieldsConfigValues(),
            'info_package' => $info_package,
            'info_shipment' => $info_shipment,
            'info_customer' => $this->getInfoCustomer($id_order),
            'info_shop' => RjcarrierInfoshop::getShopData(),
            'info_company_carrier' => $info_company_carrier,
            'info_type_shipment' => $info_type_shipment,
        ];

        // Manejar creación de envíos y etiquetado
        $this->handleShipmentCreationAndLabeling($id_order, $shipment);

        // Preparar notificaciones para la vista
        $shipment['notifications'] = $this->prepareNotifications();

        // Asignar datos a Smarty y mostrar la plantilla
        $this->context->smarty->assign($shipment);
        $this->_html .= $this->display(__FILE__, 'admin-order.tpl');
        dump($shipment);
        return $this->_html;
    }

    /**
     * Maneja la creación de envíos y etiquetas.
     */
    private function handleShipmentCreationAndLabeling($id_order, &$shipment)
    {
        $shortname = $shipment['info_company_carrier']['shortname'] ?? null;
        if (!$shortname) {
            $this->_errors[] = $this->l('Carrier shortname is missing.');
            return;
        }

        $id_shipment = isset($shipment['info_shipment']['id_shipment']) ? $shipment['info_shipment']['id_shipment'] : null;
        $carrier_class = $this->getCarrierClass($shortname);

        // Configuración específica del transportista desde la clase obtenida con la fábrica
        if ($carrier_class) {
            $shipment['show_create_label'] = $carrier_class->show_create_label ?? false;
            $shipment['config_carrier_company'] = $carrier_class->getValuesConfigFields();
        } else {
            $this->_errors[] = $this->l('Invalid carrier class.');
            return;
        }

        if (!$id_shipment && (Tools::isSubmit('submitShipment') || Tools::isSubmit('submitSavePackSend'))) {
            if ($this->validationConfiguration($shortname)) {
                if(!$carrier_class->createShipment($shipment)){
                    $this->_errors[] = $this->l('The shipment could not be generated.');
                    return;
                }
            } else {
                $this->_errors[] = $this->l('The shipment cannot be generated, the configuration is not valid.');
                return;
            }
        }

        $shipment['info_shipment'] = RjcarrierShipment::getShipmentByIdOrder($id_order);
        $id_shipment = $shipment['info_shipment']['id_shipment'] ?? null;

        if (!empty($id_shipment) && Tools::isSubmit('submitCreateLabel') && !RjcarrierLabel::getIdsLabelsByIdShipment($id_shipment)) {
            $carrier_class->createLabel($id_shipment, $id_order);
        }

        $shipment['labels'] = RjcarrierLabel::getLabelsByIdShipment($id_shipment);
    }

    protected function validationConfiguration($company_shorname)
    {
        $this->validateCarrierCompany($company_shorname);
        $this->validateShopInfo();

        if ($this->_warning) {
            $this->_warning[] = '<a class="btn btn-primary" target="_blank" href="' . $this->context->link->getAdminLink(
                'AdminModules', true, [], ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name]
            ) . '">' . $this->l('Go to configuration!') . '</a>';
            return false;

        }

        return true;
    }

    private function validateCarrierCompany($shortname)
    {
        $carrier_class = $this->getCarrierClass($shortname);

        $validation = method_exists($carrier_class, 'validationConfiguration') ? $carrier_class->validationConfiguration() : [];

        if (!is_array($validation)) {
            $validation = [];
        }

        if (!empty($validation)) {
            $this->_warning = array_merge($this->_warning ?? [], $validation);
        }
    }

    private function validateShopInfo()
    {
        $infoshop = new RjcarrierInfoshop(1);
        $validate = $infoshop->validateFields(false, true);
        if ($validate !== true) {
            $this->_warning[] = $this->l('Required data missing in shop configuration:') . $validate;
        }
    }

    private function processShipmentActions($id_order, &$id_shipment)
    {
        $info_package = $this->getInfoPackage($id_order);

        if (Tools::isSubmit('submitDeleteShipment') && Tools::getValue('id_shipment'))
        {
            if ($this->deleteShipment(Tools::getValue('id_shipment'))){
                $info_package = $this->getInfoPackage($id_order);
            }
        }

        if ((Tools::isSubmit('submitFormPackCarrier') || Tools::isSubmit('submitSavePackSend'))) {
            if (Tools::getValue('id_reference_carrier') && $id_shipment) {
                if ($this->deleteShipmentChangeCarrier($id_order, $id_shipment, Tools::getValue('id_reference_carrier'))) {
                    $id_shipment = null;
                }
            }

            $info_package = $this->processInfoPackage();
        }

        return $info_package;
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
            $module_contrareembolso = RjCarrierConfiguration::get(self::CONFIG_CONTRAREEMBOLSO, $id_shop_group, $id_shop);

            $order = new Order($id_order);

            if($module_contrareembolso == $order->module){
                $rj_carrier_infopackage['cash_ondelivery'] = $order->total_paid_tax_incl;
            }
        }

        return $rj_carrier_infopackage;
    }

    private function processInfoPackage()
    {
        $id_reference_carrier = (int) Tools::getValue('id_reference_carrier');

        $info_type_shipment = RjcarrierTypeShipment::getTypeShipmentsActiveByIdReferenceCarrier($id_reference_carrier);
        $carrier_company = new RjcarrierCompany((int)$info_type_shipment['id_carrier_company']);

        $validate_config = $this->validationConfiguration($carrier_company->shortname);

        if(!$validate_config) {
            return false;
        }

        $hour_from = Tools::getValue('rj_hour_from') ? (string) Tools::getValue('rj_hour_from') . ':00' : '00:00:00';
        $hour_until = Tools::getValue('rj_hour_until') ? (string) Tools::getValue('rj_hour_until') . ':00' : '00:00:00';

        $rj_carrier_infopackage['id_infopackage'] = (int) Tools::getValue('id_infopackage');
        $rj_carrier_infopackage['id_order'] = (int) Tools::getValue('id_order');
        $rj_carrier_infopackage['id_reference_carrier'] = $id_reference_carrier;
        $rj_carrier_infopackage['id_type_shipment'] = (int) Tools::getValue('id_type_shipment');
        $rj_carrier_infopackage['quantity'] = (int) Tools::getValue('rj_quantity') ?: 1;
        $rj_carrier_infopackage['weight'] = (float) Tools::getValue('rj_weight') ?: 0;
        $rj_carrier_infopackage['length'] = (float) Tools::getValue('rj_length') ?: 0;
        $rj_carrier_infopackage['width'] = (float) Tools::getValue('rj_width') ?: 0;
        $rj_carrier_infopackage['height'] = (float) Tools::getValue('rj_height') ?: 0;
        $rj_carrier_infopackage['cash_ondelivery'] = (float) Tools::getValue('rj_cash_ondelivery') ?: 0;
        $rj_carrier_infopackage['message'] = (string) Tools::getValue('rj_message') ?: '';
        $rj_carrier_infopackage['hour_from'] = CarrierCompany::validateFormatTime($hour_from) ? $hour_from : '00:00:00';
        $rj_carrier_infopackage['hour_until'] = CarrierCompany::validateFormatTime($hour_until) ? $hour_until : '00:00:00';
        $rj_carrier_infopackage['retorno'] = (int) Tools::getValue('rj_retorno') ?: 0;
        $rj_carrier_infopackage['rcs'] = (bool) Tools::getValue('rj_rcs') ? 1 : 0;
        $rj_carrier_infopackage['vsec'] = (float) Tools::getValue('rj_vsec') ?: 0;
        $rj_carrier_infopackage['dorig'] = (string) Tools::getValue('rj_dorig') ?: '';

        return CarrierCompany::saveInfoPackage($rj_carrier_infopackage);
    }

    public function getOrderShippingCost($params, $shipping_cost)
	{
        return $shipping_cost;
    }

    /**
     * Prepara notificaciones para mostrar en el BO.
     */
    protected function prepareNotifications()
    {
        return [
            'error' => $this->_errors,
            'warning' => $this->_warning,
            'success' => $this->_success,
            'info' => $this->_info,
        ];
    }
}
