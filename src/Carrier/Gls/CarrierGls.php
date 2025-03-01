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

namespace Roanja\Module\RjCarrier\Carrier\Gls;

use Carrier;
use PaymentModule;
use Module;
use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Carrier\Gls\ServiceGls;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\lib\Common;
use Roanja\Module\RjCarrier\Carrier\CarrierInterface;

/**
 * Class CarrierGls.
 */
class CarrierGls extends CarrierCompany implements CarrierInterface
{

    public function __construct()
    {
        $this->carrier_name = 'GLS';
        $this->shortname = 'GLS';
        $this->show_create_label = false;
        parent::__construct();

    }

    /**
     * Setea los fields config del plugin
     *
     * @return void
     */
    public function setFieldsConfig()
    {
        $this->fields_config = [
            [
                'type' => 'text',
                'label' => $this->l('GUID PROD'),
                'name' => $this->shortname . '_GUID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('GUID DEV'),
                'name' => $this->shortname . '_GUID_DEV',
                'desc' => $this->l('El GUID por defecto (15F9A8B5-82AC-4094-99F7-9FD58FD43E9E) es para hacer pruebas. Cuando tenga el módulo y sus opciones corretamente configurado y testado solicite su GUID a su Agencia GLS.'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Webservices URL:'),
                'name' => $this->shortname . '_URL',
                'required' => true,
                'desc' => $this->l('URL del servicio web de GLS. Por defecto: https://www.asmred.com/websrvs/ecm.asmx?wsdl'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Peso por defecto:'),
                'name' => $this->shortname . '_WEIGHT',
                'suffix' => 'kg',
                'class' => 'fixed-width-lg',
                'desc' => $this->l('Si desea que el cálculo del peso del envío se calcule de forma automática en base al sumatorio del peso de los productos que lo conforman según su definición en la base de datos de productos, deje este campo vacío. Si quiere poder modificar el peso de un pedido previo a su envío, defina un peso por defecto (en Kg).'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Nº Paquetes por defecto'),
                'name' => $this->shortname . '_QUANTITY',
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'select',
                'label' => $this->l('Retorno:'),
                'desc' => $this->l('Indique Retorno.'),
                'name' => $this->shortname . '_RETORNO',
                'options' => [
                    'query' => $this->optsRetorno(),
                    'id' => 'id_option',
                    'name' => 'name'
                ]
            ],
            [
                'type' => 'switch',
                'label' => $this->l('RCS:'),
                'desc' => $this->l('Indique RCS (Retorno Copia Sellada).'),
                'name' => $this->shortname . '_RCS',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'gls_rcs_1',
                        'value' => 1,
                        'label' => $this->l('Si')
                    ],
                    [
                        'id' => 'gls_rcs_0',
                        'value' => 0,
                        'label' => $this->l('No')
                    ],

                ]
            ],
            [
                'type' => 'text',
                'label' => $this->l('Dpto. origen:'),
                'name' => $this->shortname . '_DORIG',
                'desc' => $this->l('Indique el departamento de origen.'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Valor asegurado:'),
                'name' => $this->shortname . '_VSEC',
                'desc' => $this->l('Indique el valor asegurado.'),
                'class' => 'fixed-width-lg',
                'suffix' => '€',
            ],
            [
                'type' => 'select',
                'label' => $this->l('Incoterm:'),
                'desc' => $this->l('Incoterm que será usado en caso de envíos destino fuera de la UE con el servicio Eurobusiness Parcel.<b>No tiene efecto para envíos nacionales e internacionales destino UE.</b>'),
                'name' => $this->shortname . '_INCOTERM',
                'options' => [
                    'query' => $this->optsIncoterm(),
                    'id' => 'id_option',
                    'name' => 'name'
                ]
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Modo producción'),
                'name' => $this->shortname . '_ENV',
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Production'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Develop'),
                    ],
                ],
            ],

        ];
    }

    private function optsRetorno()
    {
        return [
            [
                'id_option' => 0,
                'name' => $this->l('Sin retorno')
            ],
            [
                'id_option' => 1,
                'name' => $this->l('Retorno obligatorio')
            ],
            [
                'id_option' => 2,
                'name' => $this->l('Retorno opcional')
            ]
        ];
    }

    private function optsIncoterm()
    {
        return [
            [
                'id_option' => 0,
                'name' => '-'
            ],
            [
                'id_option' => 10,
                'name' => $this->l('Incoterm 10 DDP. COSTES REMITENTE: transporte,despacho,aranceles, impuestos. COSTES DESTINATARIO: no tiene costes')
            ],
            [
                'id_option' => 20,
                'name' => $this->l('Incoterm 20 DAP. COSTES REMITENTE: transporte. COSTES DESTINATARIO: despacho, aranceles e impuestos')
            ],
            [
                'id_option' => 30,
                'name' => $this->l('Incoterm 30 DDP, I.V.A. no pagado . COSTES REMITENTE: transporte,despacho, aranceles. COSTES DESTINATARIO: impuestos')
            ],
            [
                'id_option' => 40,
                'name' => $this->l('Incoterm 40 DAP, despachado. COSTES REMITENTE: transporte, despacho. COSTES DESTINATARIO: aranceles, impuestos')
            ],
            [
                'id_option' => 50,
                'name' => $this->l('Incoterm 50c DDP, bajo valor . COSTES REMITENTE: transporte, despacho. COSTES DESTINATARIO: no tiene costes')
            ],
            [
                'id_option' => 18,
                'name' => $this->l('Incoterm 18 (DDP, VAT pre-registration). Delivered free, duty paid, VAT paid - the shipper does pay all costs, for importers no cost ocurr.')
            ]
        ];
    }

    /**
     * Crea envío GLS
     *
     * @param array $shipment
     * @return void
     */
    public function createShipment($shipment)
    {
        $id_order = $shipment['id_order'];
        $shipment['num_shipment'] = Common::getUUID();
        $service_gls = new ServiceGls($id_order);
        // dump($shipment);
        // $response = $service_gls->postShipment($shipment);

        // if (!$response) {
        //     return false;
        // }

        // $info_shipment = $this->saveShipment($shipment, $response);

        /* if ($info_shipment['id_shipment']) {
            $labels = $response->pieces;
            foreach ($labels as $label) {
                $label_response = $service_gls->getLabel($label->labelId);
                $this->saveLabels($info_shipment['id_shipment'], $label_response);
            }

            return true;
        } */

        return false;
    }

    public function saveLabels($id_shipment, $response, $num_package = 1)
    {
        $rj_carrier_label = new RjcarrierLabel();
        $rj_carrier_label->id_shipment = $id_shipment;
        $rj_carrier_label->package_id = $response->labelId;
        $rj_carrier_label->label_type = $response->labelType;
        $rj_carrier_label->tracker_code = $response->trackerCode;

        $pdf = base64_decode($response->pdf);

        if(Common::createFileLabel($pdf, $response->labelId)){
            $rj_carrier_label->pdf = $response->labelId;
        }

        if (!$rj_carrier_label->add())
            return false;

        return true;
    }
}
