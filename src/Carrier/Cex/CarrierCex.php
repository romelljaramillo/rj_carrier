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

namespace Roanja\Module\RjCarrier\Carrier\Cex;

use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Carrier\Cex\ServiceCex;
use Roanja\Module\RjCarrier\Model\Pdf\RjPDF;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;

// include_once(_PS_MODULE_DIR_.'rj_carrier/src/carriers/CarrierCompany.php');
// include_once (_PS_MODULE_DIR_ . 'rj_carrier/src/carriers/cex/ServiceCex.php');
// include_once(_PS_MODULE_DIR_. 'rj_carrier/classes/RjcarrierShipment.php');

/**
 * Class CarrierCex.
 */
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
            'RJ_CEX_ACTIVE',
            'RJ_CEX_WSURL',
            'RJ_CEX_WSURLSEG',
            'RJ_CEX_WSURLMOD',
            'RJ_CEX_WSURLANUL'
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
                        'desc' => $this->l('Format url http:// or https:// . defaul: https://www.cexpr.es/wspsc/apiRestGrabacionEnviok8s/json/grabacionEnvio')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url servicio web'),
                        'name' => 'RJ_CEX_WSURLSEG',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// . defaul: https://www.cexpr.es/wspsc/apiRestListaEnvios/json/listaEnvios')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url servicio web'),
                        'name' => 'RJ_CEX_WSURLMOD',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// . defaul: https://www.cexpr.es/wspsc/apiRestGrabacionRecogidaEnviok8s/json/modificarRecogida')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url servicio web'),
                        'name' => 'RJ_CEX_WSURLANUL',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// . defaul: https://www.cexpr.es/wspsc/apiRestGrabacionRecogidaEnviok8s/json/anularRecogida')
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

    public function createShipment($shipment)
    {
        $id_shipment = $shipment['info_shipment']['id_shipment'];

        if(!$id_shipment){
            return false;
        }

        $shipment['info_config'] = $this->getConfigFieldsValues();

        $service_cex = new ServiceCex();
        $body_shipment = $service_cex->getBodyShipment($shipment);

        $this->saveRequestShipment($id_shipment, $body_shipment);
        
        $response = $service_cex->postShipment($shipment['info_config']['RJ_CEX_WSURL'], $body_shipment);

        if(!isset($response)) {
            return false;
        }

        $this->saveResponseShipment($id_shipment, $response);
        
        if($response->codigoRetorno == 0){

            $packages_qty = (int)$shipment['info_package']['quantity'];

            $shipment['response'] = $response;

            for($num_package = 1; $num_package <= $packages_qty; $num_package++) { 
                $rjpdf = new RjPDF($this->shortname, $shipment, RjPDF::TEMPLATE_LABEL, $num_package);
                
                $pdf = $rjpdf->render($this->display_pdf);

                if ($pdf) {
                    $response->listaBultos[$num_package - 1]->pdf = $pdf;
                }
            }

            $this->saveLabels($id_shipment, $response);

            return true;
        }

        return false;
    }

    public function saveLabels($id_shipment, $response, $num_package = 1)
    {
        $info_labels = $response->listaBultos;
        foreach ($info_labels as $label) {
            $rj_carrier_label = new RjcarrierLabel();
            $rj_carrier_label->id_shipment = $id_shipment;
            $rj_carrier_label->package_id = $response->datosResultado;
            $rj_carrier_label->tracker_code = $label->codUnico;
            $rj_carrier_label->label_type = $this->label_type;
            $rj_carrier_label->pdf = base64_encode($label->pdf);
            
            if (!$rj_carrier_label->add())
                return false;
        }
        return true;
    }
}