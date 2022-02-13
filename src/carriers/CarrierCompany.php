<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use Ramsey\Uuid\Uuid;

include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierCompany.php');
include_once _PS_MODULE_DIR_ . 'rj_carrier/controllers/admin/AdminRJLabelController.php';
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/pdf/RjPDF.php');
include_once(_PS_MODULE_DIR_.'rj_carrier/classes/RjcarrierShipment.php');

class CarrierCompany extends Module
{

    /** @var string Nombre unico de transportista */
    public $name_carrier = 'name carrier';

    
    /** @var string Nombre corto del transportista siglas ejemp: CEX Correo Express */
    public $shortname = 'rjcarrier';
    public $display_pdf = 'S';
    public $label_type = 'B2X_Generic_A4_Third';

    /** @var array Campos de configuración */
    public $fields_config = [];

    public $fields_multi_confi = [];

    public $fields_config_info_extra = [];

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
        if (Tools::isSubmit('submitConfig'. $this->shortname)) {
            if($this->validationConfig())
                $this->_postProcess();
        }

        $this->_html .= $this->renderFormConfig();

        return $this->_html;
    }

    public function getConfigFieldsExtra()
    {
        return $this->fields_config_info_extra;
    }

    /**
     * Valida que los carries seleccionados no hayan sido antes configurados por otros
     *
     * @return void
     */
    protected function validationConfig()
    {
        $id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $carries_used = [];
        $ids_carries_used = [];

        $carriers = Carrier::getCarriers((int) $this->context->language->id);
        foreach ($carriers as $carrier) {
            $carrier_array[$carrier['id_reference']] = $carrier['name'];
        }

        if (Tools::isSubmit('submitConfig'. $this->shortname)) {

            // tener presente para cuando hay mas transportistas modificar
            $carries_company = self::getCarriersCompany();
            foreach ($carries_company as $company) {
                if($company['shortname'] != $this->shortname){
                    $array_carriers = Tools::unSerialize(Configuration::get('RJ_'.$company['shortname'].'_ID_REFERENCE_CARRIER', null, $id_shop_group, $id_shop));
                    $ids_carries_used = array_merge($ids_carries_used, $array_carriers);
                }
            }

            $ids_carries_save = Tools::getValue('RJ_'.$this->shortname.'_ID_REFERENCE_CARRIER');

            $carries_used = array_intersect($ids_carries_save,$ids_carries_used);

            if(count($carries_used)){
                foreach ($carries_used as $value) {
                    $this->_html .= $this->displayError(
                        $this->l('Está siendo usado por otra configuración el transportista: ') .'<b>'. $carrier_array[$value].'</b>'
                    );
                }
                return false;
            }
        }

        return true;
    }

    protected function _postProcess()
	{
        $res = true;
        $shop_context = Shop::getContext();

        $shop_groups_list = array();
        $shops = Shop::getContextListShopID();

        $this->setFieldsMultiConfi();

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }
            
            foreach ($this->fields_config as $field) {
                $res &=  Configuration::updateValue($field, Tools::getValue($field), false, $shop_group_id, $shop_id);
            }

            foreach ($this->fields_multi_confi as $field) {
                $toString_field =  serialize(Tools::getValue($field));
                $res &=  Configuration::updateValue($field, $toString_field, false, $shop_group_id, $shop_id);
            }
        }

        /* Update global shop context if needed*/
        switch ($shop_context) {
            case Shop::CONTEXT_ALL:
                foreach ($this->fields_config as $field) {
                    $res &= Configuration::updateValue($field, Tools::getValue($field));
                }
                foreach ($this->fields_multi_confi as $field) {
                    $toString_field =  serialize(Tools::getValue($field));
                    $res &= Configuration::updateValue($field, $toString_field);
                }
                
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        foreach ($this->fields_config as $field) {
                            $res &= Configuration::updateValue($field, Tools::getValue($field), false, $shop_group_id);
                        }
                        foreach ($this->fields_multi_confi as $field) {
                            $toString_field =  serialize(Tools::getValue($field));
                            $res &= Configuration::updateValue($field, $toString_field, false, $shop_group_id);
                        }
                    }
                }
                break;
            case Shop::CONTEXT_GROUP:
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        foreach ($this->fields_config as $field) {
                            $res &= Configuration::updateValue($field, Tools::getValue($field), false, $shop_group_id);
                        }
                        foreach ($this->fields_multi_confi as $field) {
                            $toString_field =  serialize(Tools::getValue($field));
                            $res &= Configuration::updateValue($field, $toString_field, false, $shop_group_id);
                        }
                    }
                }
                break;
        }

        /* Display errors if needed */
		if (!$res)
            $this->_html .= $this->displayError($this->l('Error al guardar la configuración.'));
        else {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        }
    }

    private function setFieldsMultiConfi()
    {
        if($this->shortname)
            $this->fields_multi_confi[] = 'RJ_'.$this->shortname.'_ID_REFERENCE_CARRIER';
    }

    static function getCarriersCompany($shortname = null)
    {
        $where = '';

        if($shortname){
            $where = ' WHERE c.shortname = ' . $shortname;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM '._DB_PREFIX_.'rj_carrier_company c '
            .$where
        );
    }

    /**
     * Devuelve shortname comapany a partir del id_refernce_carrier
     *
     * @param string $id_carrier
     * @return string
     */
    static function getShortnameCompanyByIdReferenceCarrier($id_refernce_carrier)
    {
        $id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $array_carriers_company = [];

        $carries_company = self::getCarriersCompany();

        foreach ($carries_company as $company) {
            $array_carriers_company = Tools::unSerialize(Configuration::get('RJ_'.$company['shortname'].'_ID_REFERENCE_CARRIER', null, $id_shop_group, $id_shop));
            if(in_array($id_refernce_carrier,$array_carriers_company)) {
                return $company;
            }
        }

        return $carries_company[0];
    }

    public function renderFormConfig()
    {
        $carriers = Carrier::getCarriers((int) $this->context->language->id);
        foreach ($carriers as $carrier) {
            $carrier_array[] =  array(
                'id' => $carrier['id_reference'],
                'name' => $carrier['name']
            );
        }

        $this->fields_form['form']['input'][] = [
            'type' => 'select',
            'label' => $this->l('Select carrier') . $this->name_carrier,
            'name' => 'RJ_'.$this->shortname.'_ID_REFERENCE_CARRIER[]',
            'desc' => $this->l('Seleccione varias categorias con la tecla CTRL + click.'),
            'multiple' => true,
            'options' => [
                'query' => $carrier_array,
                'id' => 'id',
                'name' => 'name'
            ]
        ];

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
     * Obtiene los datos de configuración
     *
     * @param array $fields
     * @return array
     */
    public function getConfigFieldsValues()
	{
		$id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();
        $arry_fields = [];

        $this->setFieldsMultiConfi();

        foreach ($this->fields_config as $field) {
            if(in_array($field, $this->fields_multi_confi)){
                $arry_fields[$field . '[]'] = Tools::getValue($field, Tools::unSerialize(Configuration::get($field, null, $id_shop_group, $id_shop)));
            } else {
                $arry_fields[$field] = Tools::getValue($field, Configuration::get($field, null, $id_shop_group, $id_shop));
            }
        }

        foreach ($this->fields_multi_confi as $field) {
            $arry_fields[$field . '[]'] = Tools::getValue($field, Tools::unSerialize(Configuration::get($field, null, $id_shop_group, $id_shop)));
        }

        return $arry_fields;
	}

    public function createShipment($shipment)
    {
        $id_shipment = $shipment['info_shipment']['id_shipment'];
        
        $this->saveRequestShipment($id_shipment, $shipment);

        if($id_shipment){
            $packages_qty = $shipment['info_package']['quantity'];
            
            for($num_package = 1; $num_package <= $packages_qty; $num_package++) { 
                $rjpdf = new RjPDF($shipment, RjPDF::TEMPLATE_TAG_TD, Context::getContext()->smarty);
                $pdf = $rjpdf->render($this->display_pdf, $num_package);

                if ($pdf) {
                    $this->saveLabels($id_shipment, $pdf, $num_package);
                }
            }
            return true;
        }

        return false;
    }

    public function saveLabels($id_shipment, $pdf, $num_package = 1)
    {
        $uuid = self::getUUID();

        $rj_carrier_label = new RjcarrierLabel();
        $rj_carrier_label->id_shipment = $id_shipment;
        $rj_carrier_label->package_id = $uuid;
        $rj_carrier_label->label_type = $this->label_type;
        $rj_carrier_label->tracker_code = 'TC' .$uuid . '-' . $num_package;;
        $rj_carrier_label->pdf = base64_encode($pdf);

        if (!$rj_carrier_label->add())
            return false;

        return true;
    }

    public function saveRequestShipment($id_shipment, $request)
    {
        $rj_carrier_shipment = new RjcarrierShipment((int)$id_shipment);
        
        $rj_carrier_shipment->request = $request;

        if (!$id_shipment){
            if (!$rj_carrier_shipment->add())
                return false;
        }elseif (!$rj_carrier_shipment->update()){
            return false;
        } 

        return true;
    }

    public function saveResponseShipment($id_shipment, $response)
    {
        $rj_carrier_shipment = new RjcarrierShipment((int)$id_shipment);

        $rj_carrier_shipment->request = json_encode($response);

        if (!$id_shipment){
            if (!$rj_carrier_shipment->add())
                return false;
        }elseif (!$rj_carrier_shipment->update()){
            return false;
        } 

        return true;
    }

    /**
     * save data db table rj_carrier_shipment
     *
     * @param array $info_shipment
     * @return obj || boolean
     */
    public static function saveShipment($info_shipment)
    {
        $id_order = $info_shipment['id_order'];
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
        $rj_carrier_shipment->num_shipment = self::getUUID();
        $rj_carrier_shipment->id_infopackage = (int)$id_infopackage;
        $rj_carrier_shipment->id_carrier_company = (int)$id_carrier_company;

        if (!$id_shipment)
        {
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
        $rj_carrier_infopackage->quantity = (int)Tools::getValue('rj_quantity');
        $rj_carrier_infopackage->weight = Tools::getValue('rj_weight');
        $rj_carrier_infopackage->length = Tools::getValue('rj_length');
        $rj_carrier_infopackage->cash_ondelivery = Tools::getValue('rj_cash_ondelivery');
        $rj_carrier_infopackage->message = Tools::getValue('rj_message');
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

    public static function getUUID()
    {
        $uuid = Uuid::uuid4();
        return $uuid->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
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

}
