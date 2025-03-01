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

use Roanja\Module\RjCarrier\lib\Common;

use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Carrier\Cex\ServiceCex;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\lib\Pdf\RjPDF;
use Roanja\Module\RjCarrier\Carrier\CarrierInterface;

/**
 * Class CarrierCex.
 */
class CarrierCex extends CarrierCompany implements CarrierInterface
{
    public function __construct()
    {
        $this->carrier_name = 'Correo Express';
        $this->shortname = 'CEX';
        $this->show_create_label = false;
        parent::__construct();
    }

    public function setFieldsConfig()
    {
        $this->fields_config = [
            [
                'type' => 'text',
                'label' => $this->l('Código cliente'),
                'name' => $this->shortname . '_COD_CLIENT',
                'required' => true,
                'class' => 'fixed-width-lg'
            ],
            [
                'type' => 'text',
                'label' => $this->l('User'),
                'name' => $this->shortname . '_USER',
                'required' => true,
            ],
            [
                'type' => 'password',
                'label' => $this->l('Password'),
                'name' => $this->shortname . '_PASS',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url servicio web'),
                'name' => $this->shortname . '_URL',
                'required' => true,
                'desc' => $this->l('Format url http:// or https:// . defaul: https://www.cexpr.es/wspsc')
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint grabacionEnvio'),
                'name' => $this->shortname . '_WSURL',
                'required' => true,
                'desc' => $this->l('Example: /apiRestGrabacionEnviok8s/json/grabacionEnvio')
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint listaEnvios'),
                'name' => $this->shortname . '_WSURLSEG',
                'required' => true,
                'desc' => $this->l('Example: /apiRestListaEnvios/json/listaEnvios')
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint modificarRecogida'),
                'name' => $this->shortname . '_WSURLMOD',
                'required' => true,
                'desc' => $this->l('Example: /apiRestGrabacionRecogidaEnviok8s/json/modificarRecogida')
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint anularRecogida'),
                'name' => $this->shortname . '_WSURLANUL',
                'required' => true,
                'desc' => $this->l('Example: /apiRestGrabacionRecogidaEnviok8s/json/anularRecogida')
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Activar shipping track'),
                'name' => $this->shortname . '_ENABLESHIPPINGTRACK',
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
                'name' => $this->shortname . '_LABELSENDER',
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
                'name' => $this->shortname . '_LABELSENDER_TEXT',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Activar peso'),
                'name' => $this->shortname . '_ENABLEWEIGHT',
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
                'label' => $this->l('Nº Paquetes por defecto'),
                'name' => $this->shortname . '_QUANTITY',
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Peso por defecto'),
                'name' => $this->shortname . '_WEIGHT',
                'suffix' => 'kg',
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Hour from'),
                'name' => $this->shortname . '_HOUR_FROM',
                'class' => 'fixed-width-lg',
                'suffix' => '<',
                'desc' => $this->l('format 09:00'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('hour until'),
                'name' => $this->shortname . '_HOUR_UNTIL',
                'class' => 'fixed-width-lg',
                'suffix' => '>',
                'desc' => $this->l('format 18:00'),
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Production Mode'),
                'name' => $this->shortname . '_ENV',
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Production')
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Develop')
                    ]
                ],
            ],
        ];
    }

    public function createShipment($shipment)
    {
        $id_order = $shipment['id_order'];
        $shipment['num_shipment'] = Common::getUUID();
        $service_cex = new ServiceCex($id_order);
        $response = $service_cex->postShipment($shipment);

        if(!$response) {
            return false;
        }

        $response = $this->deletedEtiquetaResponse($response);

        $info_shipment = $this->saveShipment($shipment, $response);
        $shipment['info_shipment'] = $info_shipment;

        if($response->codigoRetorno == 0){

            $packages_qty = (int)$shipment['info_package']['quantity'];

            $shipment['response'] = $response;

            for($num_package = 1; $num_package <= $packages_qty; $num_package++) {
                $rjpdf = new RjPDF($this->shortname, $shipment, RjPDF::TEMPLATE_LABEL, $num_package);

                $pdf = $rjpdf->render($this->display_pdf);

                if ($pdf) {
                    $response->listaBultos[$num_package - 1]->pdf = $pdf;
                    $reponse_pdf = $response->listaBultos[$num_package - 1];
                    $this->saveLabels($info_shipment['id_shipment'], $reponse_pdf);
                }
            }

            return true;
        }

        return false;
    }

    public function saveLabels($id_shipment, $reponse_pdf, $num_package = 1)
    {
        $rj_carrier_label = new RjcarrierLabel();
        $rj_carrier_label->id_shipment = $id_shipment;
        $rj_carrier_label->package_id = $reponse_pdf->codUnico;
        $rj_carrier_label->tracker_code = $reponse_pdf->codUnico;
        $rj_carrier_label->label_type = $this->label_type;

        if(Common::createFileLabel($reponse_pdf->pdf, $reponse_pdf->codUnico)){
            $rj_carrier_label->pdf = $reponse_pdf->codUnico;
        }

        if (!$rj_carrier_label->add())
            return false;

        return true;
    }

    /**
     * Elimina la etiqueta que viene en el response
     *
     * @param obj $response
     * @return obj $response
     */
    public function deletedEtiquetaResponse($response)
    {
        if(isset($response->etiqueta)){
            $response->etiqueta = [];
            return $response;
        }

        return;
    }
}
