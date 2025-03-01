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

namespace Roanja\Module\RjCarrier\Carrier\Dhl;

use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Carrier\CarrierInterface;
use Roanja\Module\RjCarrier\Carrier\Dhl\ServiceDhl;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\lib\Common;

/**
 * Class CarrierDhl
 * Manejo específico del transportista DHL.
 */
class CarrierDhl extends CarrierCompany implements CarrierInterface
{
    public function __construct()
    {
        $this->carrier_name = 'DHL';
        $this->shortname = 'DHL';
        $this->show_create_label = true;
        parent::__construct();
    }

    /**
     * Configuración de los campos específicos para DHL.
     */
    public function setFieldsConfig()
    {
        $this->fields_config = [
            [
                'type' => 'text',
                'label' => $this->l('Account ID'),
                'name' => $this->shortname . '_ACCOUNID',
                'required' => true,
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('User ID'),
                'name' => $this->shortname . '_USERID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key'),
                'name' => $this->shortname . '_KEY',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('URL Production'),
                'name' => $this->shortname . '_URL',
                'required' => true,
                'desc' => $this->l('Format: http:// or https://'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('User ID DEV'),
                'name' => $this->shortname . '_USERID_DEV',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key DEV'),
                'name' => $this->shortname . '_KEY_DEV',
            ],
            [
                'type' => 'text',
                'label' => $this->l('URL Develop'),
                'name' => $this->shortname . '_URL_DEV',
                'desc' => $this->l('Format: http:// or https://'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint login'),
                'name' => $this->shortname . '_ENDPOINT_LOGIN',
                'required' => true,
                'desc' => $this->l('Example: /authenticate/api-key'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint refresh token'),
                'name' => $this->shortname . '_ENDPOINT_REFRESH_TOKEN',
                'required' => true,
                'desc' => $this->l('Example: /authenticate/refresh-token'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint shipment'),
                'name' => $this->shortname . '_ENDPOINT_SHIPMENT',
                'required' => true,
                'desc' => $this->l('Example: /shipments'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint label'),
                'name' => $this->shortname . '_ENDPOINT_LABEL',
                'required' => true,
                'desc' => $this->l('Example: /labels'),
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Production Mode'),
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

    /**
     * Crea un envío con DHL.
     *
     * @param array $shipment
     * @return bool
     */
    public function createShipment($shipment)
    {
        $id_order = $shipment['id_order'];
        $shipment['num_shipment'] = Common::getUUID();
        $service_dhl = new ServiceDhl($id_order);

        // $response = $service_dhl->postShipment($shipment);

        // if (!$response) {
        //     return false;
        // }

        // $info_shipment = $this->saveShipment($shipment, $response);

        // if (isset($info_shipment['id_shipment'])) {
        //     foreach ($response->pieces as $label) {
        //         $label_response = $service_dhl->getLabel($label->labelId);
        //         $this->saveLabels($info_shipment['id_shipment'], $label_response);
        //     }
        //     return true;
        // }

        // return false;
    }

    /**
     * Guarda las etiquetas proporcionadas por DHL.
     *
     * @param int $id_shipment
     * @param object $response
     * @return bool
     */
    /* public function saveLabels(int $id_shipment, object $response): bool
     {
         $rj_carrier_label = new RjcarrierLabel();
         $rj_carrier_label->id_shipment = $id_shipment;
         $rj_carrier_label->package_id = $response->labelId;
         $rj_carrier_label->label_type = $response->labelType;
         $rj_carrier_label->tracker_code = $response->trackerCode;

         $pdf = base64_decode($response->pdf);

         if (Common::createFileLabel($pdf, $response->labelId)) {
             $rj_carrier_label->pdf = $response->labelId;
         }

         return $rj_carrier_label->add();
     } */

    public function saveLabels($id_shipment, $response, $num_package = 1): bool
    {
        $rj_carrier_label = new RjcarrierLabel();
        $rj_carrier_label->id_shipment = $id_shipment;
        $rj_carrier_label->package_id = $response->labelId;
        $rj_carrier_label->label_type = $response->labelType;
        $rj_carrier_label->tracker_code = $response->trackerCode;

        $pdf = base64_decode($response->pdf);

        if (Common::createFileLabel($pdf, $response->labelId)) {
            $rj_carrier_label->pdf = $response->labelId;
        }

        return $rj_carrier_label->add();
    }
}
