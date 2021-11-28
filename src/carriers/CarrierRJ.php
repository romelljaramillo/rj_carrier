<?php

class CarrierRj extends Module
{

    /** @var string Nombre unico de transportista */
    public $name_carrier = 'name carrier';

    
    /** @var string Nombre corto del transportista siglas ejemp: CEX Correo Express */
    public $shortname = 'shortname carrier';

    /** @var array Campos de configuración */
    public $fields_config = [];

    public $fields_multi_confi = [];

    /** @var array Campos del formulario configuración */
    public $fields_form;

    public $context;
    public $_html;

    public function __construct()
    {
        $this->module = 'rj_carrier';
        $this->name = 'rj_carrier';
        parent::__construct();

        // $this->fields_multi_confi = ['RJ_'.$this->shortname.'_ID_REFERENCE_CARRIER'];

    }

    public function renderConfig() 
    {
        // $companys = $this->getCarriersCompany();
        
        if (Tools::isSubmit('submitConfig'. $this->shortname)) {
            if($this->validationConfig())
                $this->_postProcess();
        }

        $this->_html .= $this->renderFormConfig();

        // foreach ($variable as $key => $value) {
        //     # code...
        // }

        return $this->_html;
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
            $carries_company = $this->getCarriersCompany();
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

    private function addFieldsMultiConfi()
    {
        if($this->shortname)
            $this->fields_multi_confi[] = 'RJ_'.$this->shortname.'_ID_REFERENCE_CARRIER';
    }

    protected function _postProcess()
	{
        $res = true;
        $shop_context = Shop::getContext();

        $shop_groups_list = array();
        $shops = Shop::getContextListShopID();

        $this->addFieldsMultiConfi();

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

    private function getCarriersCompany($shortname = null)
    {
        $where = '';

        if($shortname){
            $where = ' WHERE c.shortname = ' . $shortname;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT c.`name`, c.`shortname`  FROM '._DB_PREFIX_.'rj_carrier_company c '
            .$where
        );
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

        $this->addFieldsMultiConfi();

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

}
