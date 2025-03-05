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
use Roanja\Module\RjCarrier\Model\RjCarrierConfiguration;
use Roanja\Module\RjCarrier\lib\Pdf\RjPDF;
use Roanja\Module\RjCarrier\Carrier\Def\CarrierDef;

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
 * Abstract Class CarrierCompany
 */
abstract class CarrierCompany extends Module implements CarrierInterface
{

    /** @var string Nombre único del transportista */
    public $carrier_name = 'Generic Carrier';

    /** @var string Nombre corto del transportista (siglas) */
    public $shortname = 'DEF';
    public $carrier_company_id = 'DEF';

    /** @var bool Mostrar opción de crear etiqueta */
    public $show_create_label = false;

    /** @var array Configuración del transportista */
    protected $fields_config = [];

    /** @var array Configuración adicional */
    protected $fields_additional_config = [];

    /** @var string Nombre corto del transportista siglas ejemp: CEX Correo Express */
    public $display_pdf = 'S';
    public $label_type = 'B2X_Generic_A4_Third';

    public $context;
    public $_html;
    public $module = 'rj_carrier';

    public function __construct()
    {
        $this->module = 'rj_carrier';
        $this->name = 'rj_carrier';
        parent::__construct();

        $this->context = Context::getContext();

        $this->setFieldsConfig();
        $this->setFieldsAdditionalConfig();
        $this->carrier_company_id = RjcarrierCompany::getIdByShortname($this->shortname);
    }

    public function setFieldsConfig() {}
    public function setFieldsAdditionalConfig() {}

    public function setFieldsFormConfig()
    {
        $active[] = [
            'type' => 'switch',
            'label' => $this->l('Active'),
            'name' => $this->shortname . '_ACTIVE',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Active')
                ],
                [
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Inactive')
                ]
            ],
        ];

        $this->fields_config = array_merge($this->fields_config, $active);
    }

    public function getFieldsFormConfig()
    {
        $this->setFieldsFormConfig();

        return [
            'form' => [
                'legend' => [
                    'title' => $this->shortname . $this->l('information'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $this->fields_config,
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Obtiene los datos de configuración
     *
     * @param array $fields
     * @return array
     */
    public function getValuesConfigFields()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = Shop::getContextShopID();
        $fields = [];

        foreach ($this->fields_config as $field) {
            if ($field['type'] === 'password') {
                $fields[$field['name']] = Tools::getValue($field['name'], Common::encrypt('decrypt', RjCarrierConfiguration::get($field['name'], $id_shop_group, $id_shop)));
            } else {
                $fields[$field['name']] = Tools::getValue($field['name'], RjCarrierConfiguration::get($field['name'], $id_shop_group, $id_shop));
            }
        }

        return $fields;
    }

    public function getAdditionalFormFieldsConfig()
    {
        return $this->fields_additional_config;
    }

    public function viewAddTypeShipment()
    {
        $this->context->smarty->assign([
            'link' => $this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->module,
                'tab_module' => $this->tab,
                'tab_form' => $this->shortname,
                'add_type_shipment' => '1',
                'carrier' => $this->shortname,
            ]),
            'company' => $this->shortname,
        ]);

        return $this->display($this->_path, '/views/templates/hook/create-type-shipment.tpl');
    }

    public function getListTypeShipmentFields()
    {
        return [
            'id_type_shipment' => [
                'title' => $this->l('Id'),
                'width' => 140,
                'type' => 'text',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'width' => 140,
                'type' => 'text',
            ],
            'carrier_company' => [
                'title' => $this->l('Company'),
                'width' => 140,
                'type' => 'text',
            ],
            'shortname' => [
                'title' => $this->l('Short name'),
                'width' => 140,
                'type' => 'text',
            ],
            'id_bc' => [
                'title' => $this->l('Type of service'),
                'width' => 140,
                'type' => 'text',
            ],
            'reference_carrier' => [
                'title' => $this->l('Carrier reference'),
                'width' => 140,
                'type' => 'text',
            ],
            'active' => [
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
            ],
        ];
    }

    public function getListTypeShipmentValues()
    {
        $carrier_company = RjcarrierCompany::getCarrierCompanyByShortname($this->shortname);
        return RjcarrierTypeShipment::getTypeShipmentsByIdCarrierCompany($carrier_company['id_carrier_company']);
    }

    public function formTypeShipment()
    {
        $carriers = Carrier::getCarriers((int) $this->context->language->id);
        $fieldsValuesTypeShipment = $this->getConfigFieldsValuesTypeShipment();

        $carrier_array[] =  [
            'id' => '',
            'name' => ''
        ];

        foreach ($carriers as $carrier) {
            if ($fieldsValuesTypeShipment['id_reference_carrier'] == $carrier['id_reference']
            || !RjcarrierTypeShipment::typeShipmentExistsByIdReference($carrier['id_reference'])) {
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
                        'label' => $this->l('Id type service'),
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
        $helper->submit_action = 'submitConfigTypeShipment';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->module . '&tab_module=' . $this->tab . '&tab_form=' . $this->shortname;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $fieldsValuesTypeShipment,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * Valida que los carries seleccionados hayan sido configurados
     *
     * @return void
     */
    public function validationConfiguration()
    {
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = (int)Context::getContext()->shop->id_shop_group;
        $warnings = [];

        $allFields = array_merge($this->fields_config, $this->fields_additional_config);

        foreach ($allFields as $field) {
            $value = RjCarrierConfiguration::get($field['name'], $id_shop_group, $id_shop);

            if (!empty($field['required']) && empty($value)) {
                $warnings[] = $this->l('Required configuration missing: ') . $field['name'];
            }
        }

        return $warnings;
    }

    public function saveCarrierConfiguration()
    {
        $res = true;
        $shop_context = Shop::getContext();
        $shop_groups_list = [];
        $shops = Shop::getContextListShopID();

        $this->setFieldsFormConfig();

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }

            foreach ($this->fields_config as $field) {
                $value = Tools::getValue($field['name']);
                if ($field['type'] === 'password' && $value) {
                    $value = Common::encrypt('encrypt', $value);
                }
                $res &= RjCarrierConfiguration::updateValue($field['name'], $value, (int)$this->carrier_company_id, $shop_group_id, $shop_id);
            }
        }

        if ($shop_context === Shop::CONTEXT_ALL) {
            $this->_updateConfigForAllShops($shop_groups_list);
        } elseif ($shop_context === Shop::CONTEXT_GROUP) {
            $this->_updateConfigForShopGroups($shop_groups_list);
        }

        if (!$res) {
            $this->_html .= $this->displayError($this->l('The Configuration could not be added.'));
        } else {
            $this->_redirectAfterProcess(6);
        }
    }

    protected function _updateConfigForAllShops($shop_groups_list)
    {
        foreach ($this->fields_config as $field) {
            if ($field['type'] === 'password' && Tools::getValue($field['name'])) {
                RjCarrierConfiguration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])));
            } else {
                RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']));
            }
        }

        foreach ($shop_groups_list as $shop_group_id) {
            foreach ($this->fields_config as $field) {
                if ($field['type'] === 'password' && Tools::getValue($field['name'])) {
                    RjCarrierConfiguration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])), $shop_group_id);
                } else {
                    RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']), $shop_group_id);
                }
            }
        }
    }

    protected function _updateConfigForShopGroups($shop_groups_list)
    {
        foreach ($shop_groups_list as $shop_group_id) {
            foreach ($this->fields_config as $field) {
                if ($field['type'] === 'password' && Tools::getValue($field['name'])) {
                    RjCarrierConfiguration::updateValue($field['name'], Common::encrypt('encrypt', Tools::getValue($field['name'])), false, $shop_group_id);
                } else {
                    RjCarrierConfiguration::updateValue($field['name'], Tools::getValue($field['name']), false, $shop_group_id);
                }
            }
        }
    }

    /**
     * Guarda la configuración de un tipo de envío
     *
     * @param array $data
     * @return void
     */
    public function saveConfigTypeShipment(array $data)
    {
        $typeShipment = new RjcarrierTypeShipment((int)($data['id_type_shipment'] ?? null));

        $typeShipment->id_carrier_company = (int)$data['id_carrier_company'];
        $typeShipment->name = (string)$data['name'];
        $typeShipment->id_bc = (string)$data['id_bc'];
        $typeShipment->id_reference_carrier = (int)$data['id_reference_carrier'];
        $typeShipment->active = (bool)$data['active'];

        if ($typeShipment->id) {
            $result = $typeShipment->update();
            $message = 'The Type Shipment could not be updated.';
            $confirmationCode = 6;
        } else {
            $result = $typeShipment->add();
            $message = 'The Type Shipment could not be added.';
            $confirmationCode = 3;
        }

        if (!$result) {
            $this->_html .= $this->displayError($this->l($message));
        } else {
            $this->_redirectAfterProcess($confirmationCode);
        }
    }

    public function updateStatusTypeShipment(int $id_type_shipment)
    {
        $typeShipment = new RjcarrierTypeShipment((int)$id_type_shipment);
        if ($typeShipment->id) {
            $typeShipment->active = (int) !$typeShipment->active;
            $typeShipment->save();
        }

        $this->_redirectAfterProcess(4);
    }

    public function deleteTypeShipment(int $id_type_shipment)
    {
        $typeShipment = new RjcarrierTypeShipment((int)$id_type_shipment);
        if (!$typeShipment->delete()) {
            $this->_html .= $this->displayError($this->l('Could not delete.'));
        } else {
            $this->_redirectAfterProcess(1);
        }
    }

    protected function _redirectAfterProcess($confirmationCode)
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => $this->name,
            'tab_module' => $this->tab,
            'conf' => $confirmationCode,
            'module_name' => $this->name,
            'tab_form' => $this->shortname
        ]));
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
        if ($type_shipment) {
            $carrier_company = new RjcarrierCompany((int)$type_shipment['id_carrier_company']);
            return  $carrier_company->getFields();
        } else {
            $carries_company = RjcarrierCompany::getAllCarrierCompany();
        }

        return $carries_company[0];
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
     * Crea un envío.
     *
     * @param array $shipment
     * @return bool
     */
    public function createShipment($shipment)
    {
        $infoShipment = $this->saveShipment($shipment);

        if (!$infoShipment) {
            return false;
        }

        $shipment['info_shipment'] = $infoShipment;
        $packages_qty = $shipment['info_package']['quantity'];

        for ($num_package = 1; $num_package <= $packages_qty; $num_package++) {

            $packageId = Common::getUUID();

            $pdf = $this->generatePdfLabel($shipment, $num_package);

            if (!$this->createFile($pdf, $packageId)) {
                return false;
            }

            $labelData = [
                'id_shipment'  => $infoShipment['id_shipment'],
                'package_id'   => $packageId,
                'label_type'   => $this->label_type,
                'tracker_code' => 'TC-' . $infoShipment['num_shipment'] . '-' . $num_package,
            ];

            if (!$this->saveLabel($labelData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Guarda los datos de un envío.
     *
     * @param array $info_shipment
     * @param object $response
     * @return array|bool
     */
    public function saveShipment($info_shipment, $response = null)
    {
        $info_shipment['num_shipment'] = isset($info_shipment['num_shipment']) && !empty($info_shipment['num_shipment']) ? $info_shipment['num_shipment'] : Common::getUUID();

        $id_order = $info_shipment['id_order'];
        $id_infopackage = $info_shipment['info_package']['id_infopackage'];
        $id_carrier_company = $info_shipment['info_company_carrier']['id_carrier_company'];

        if (!$id_order) {
            return false;
        }

        $id_shipment = RjcarrierShipment::getIdByIdOrder((int)$id_order);
        $order = new Order((int)$id_order);

        $carrierShipment = $id_shipment ? new RjcarrierShipment((int)$id_shipment) : new RjcarrierShipment();

        $carrierShipment->id_order = (int)$id_order;
        $carrierShipment->reference_order = $order->reference;
        $carrierShipment->num_shipment = $info_shipment['num_shipment'];
        $carrierShipment->id_infopackage = (int)$id_infopackage;
        $carrierShipment->id_carrier_company = (int)$id_carrier_company;
        $carrierShipment->product = $info_shipment['carrier_name'];
        $carrierShipment->request = json_encode($info_shipment);
        $carrierShipment->response = $response ? json_encode($response) : null;

        if (!$id_shipment) {
            return $carrierShipment->add() ? $carrierShipment->getFields() : false;
        }

        return $carrierShipment->update() ? $carrierShipment->getFields() : false;
    }

    /**
     * Genera una plantilla de etiqueta.
     *
     * @param array $shipment
     * @param integer $num_package
     * @return string
     */
    private function generatePdfLabel($shipment, $num_package = 1)
    {
        $template = new RjPDF($this->shortname, $shipment, RjPDF::TEMPLATE_LABEL, $num_package);
        return $template->render($this->display_pdf);
    }

    /**
     * Crea una etiqueta.
     *
     * @param string $pdf
     * @param string $packageId
     * @return bool
     */
    public function createFile($pdf, $packageId)
    {
        return Common::createFileLabel($pdf, $packageId);
    }

    /**
     * Guarda los datos de una etiqueta.
     *
     * @param array $infoLabel
     * @return bool
     */
    public function saveLabel($infoLabel)
    {
        if (!$infoLabel['id_shipment'] || !$infoLabel['package_id'] || !$infoLabel['label_type'] || !$infoLabel['tracker_code']) {
            return false;
        }

        $rj_carrier_label = new RjcarrierLabel();
        $rj_carrier_label->id_shipment = (int)$infoLabel['id_shipment'];
        $rj_carrier_label->package_id = $infoLabel['package_id'];
        $rj_carrier_label->label_type = $infoLabel['label_type'];
        $rj_carrier_label->tracker_code = $infoLabel['tracker_code'];

        if (!$rj_carrier_label->add()) {
            return false;
        }

        return true;
    }

    public static function saveInfoPackage(array $infopackage)
    {
        if ($infopackage['id_infopackage']) {
            $rj_carrier_infopackage = new RjcarrierInfoPackage((int)$infopackage['id_infopackage']);

            if (!Validate::isLoadedObject($rj_carrier_infopackage)) {
                return false;
            }
        } else {
            $rj_carrier_infopackage = new RjcarrierInfoPackage();
        }

        foreach ($infopackage as $field => $value) {
            if (isset($infopackage[$field])) {
                $rj_carrier_infopackage->$field = $infopackage[$field];
            }
        }

        if ($rj_carrier_infopackage->id) {
            return $rj_carrier_infopackage->update() ? $rj_carrier_infopackage->getFields() : false;
        } else {
            return $rj_carrier_infopackage->add() ? $rj_carrier_infopackage->getFields() : false;
        }
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
        if (preg_match("/(?:[01]\d|2[0-3]):(?:[0-5]\d):(?:[0-5]\d)/", $time)) {
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
