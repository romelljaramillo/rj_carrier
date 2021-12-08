<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/CarrierCompany.php');
include_once (_PS_MODULE_DIR_ . 'rj_carrier/src/carriers/cex/ApiCex.php');
include_once(_PS_MODULE_DIR_. 'rj_carrier/classes/RjcarrierShipment.php');


class CarrierCex extends CarrierCompany
{
    public function __construct()
    {
        $this->name_carrier = 'Correo Express';
        $this->shortname = 'CEX';
        /**
         * Names of fields config DHL carrier used
         */
        $this->fields_config = [
            'RJ_CEX_COD_CLIENT',
            'RJ_CEX_USER',
            'RJ_CEX_PASS',
            'RJ_CEX_WSURL',
            'RJ_CEX_ACTIVE',
        ];

        // $this->fields_multi_confi = [ ];

        $this->fields_config_info_extra = [
            'RJ_ENABLESHIPPINGTRACK',
            'RJ_LABELSENDER',
            'RJ_LABELSENDER_TEXT',
            'RJ_ENABLEWEIGHT',
            'RJ_DEFAULTKG',
            'RJ_HOUR_FROM',
            'RJ_HOUR_UNTIL'
        ];

        parent::__construct();

    }

    public function renderConfig()
    {
        $this->setFieldsFormConfig();
        return parent::renderConfig();
    }

    public function getFieldsFormConfigExtra()
    {
        return  [
            [
                'type' => 'switch',
                'label' => $this->l('Activar shipping track'),
                'name' => 'RJ_ENABLESHIPPINGTRACK',
                'desc' => $this->l('Activar enlace de seguimiento en el historial de compras del cliente'),
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('yes')
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('no')
                    ]
                ],
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Activar quitar remitente'),
                'name' => 'RJ_LABELSENDER',
                'desc' => $this->l('Quitar remitente de las etiquetas'),
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('yes')
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('no')
                    ]
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Remitente alternativo'),
                'name' => 'RJ_LABELSENDER_TEXT',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Activar peso'),
                'name' => 'RJ_ENABLEWEIGHT',
                'desc' => $this->l('Activar peso por defecto'),
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('yes')
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('no')
                    ]
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->l('Peso por defecto'),
                'name' => 'RJ_DEFAULTKG',
                'suffix' => 'kg',
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Hour from'),
                'name' => 'RJ_HOUR_FROM',
                'class' => 'fixed-width-lg',
                'suffix' => '<',
                'desc' => $this->l('format 09:00'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('hour until'),
                'name' => 'RJ_HOUR_UNTIL',
                'class' => 'fixed-width-lg',
                'suffix' => '>',
                'desc' => $this->l('format 18:00'),
            ]
        ];
    }

    private function setFieldsFormConfig()
    {

        $this->fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Correo Express information'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Código cliente'),
                        'name' => 'RJ_CEX_COD_CLIENT',
                        'required' => true,
                        'class' => 'fixed-width-lg'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Usuario'),
                        'name' => 'RJ_CEX_USER',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Contraseña'),
                        'name' => 'RJ_CEX_PASS',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url servicio web'),
                        'name' => 'RJ_CEX_WSURL',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// . defaul: https://www.correosexpress.com/wpsc/services')
                    ),
                    array(
						'type' => 'switch',
						'label' => $this->l('Activar'),
						'name' => 'RJ_CEX_ACTIVE',
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
					),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
    }

    public function createShipment($info_shipment)
    {
        $num_shipment = RjcarrierShipment::getNumShipmentByIdOrder($info_shipment['id_order']);

        if (!$num_shipment) {
            $apiCex = new ApiCex();

            $info_shipment['info_config'] = $this->getConfigFieldsValues();
            dump($info_shipment);
            $data_shipment = $apiCex->postShipment($info_shipment);

            if (!isset($data_shipment->num_shipment)) {
                $this->errors[] = $this->l('Algo esta mal en la información del envío.');
                return false;
            }

            // $this->saveShipment($data_shipment, $info_shipment);
            // $idShipment = RjcarrierShipment::getIdShipmentByIdOrder($info_shipment['id_order']);
            // $this->saveLabels($idShipment, $data_shipment);
            // $data_shipment = $apidhl->getShipment($data_shipment->num_shipment);

        } else {
            $this->errors[] = $this->l('Ya existe un envío para este pedido.');
            return false;
        }
        $this->success[] = $this->l('Envio realizado con exito.');
        return true;
    }
}