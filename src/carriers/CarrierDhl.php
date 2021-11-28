<?php
include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/CarrierRJ.php');

class CarrierDhl extends CarrierRj
{

    public function __construct()
    {
        $this->name_carrier = 'DHL';
        $this->shortname = 'DHL';
        /**
         * Names of fields config DHL carrier used
         */
        $this->fields_config = [
            'RJ_DHL_ACCOUNID',
            'RJ_DHL_USERID',
            'RJ_DHL_KEY',
            'RJ_DHL_KEY_DEV',
            'RJ_DHL_URL_PRO',
            'RJ_DHL_URL_DEV',
            'RJ_DHL_ENV'
        ];

        // $this->fields_multi_confi = [];

        parent::__construct();

    }

    public function renderConfig()
    {
        $this->setFieldsFormConfig();
        return parent::renderConfig();
    }

    private function setFieldsFormConfig()
    {
        $this->fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('DHL information'),
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
    }
}
