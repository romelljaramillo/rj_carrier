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
        $shipment['num_shipment'] = Common::getUUID();
        $configuration = $this->getValuesConfigFields();
        $serviceDhl = new ServiceDhl($configuration);
        $response = $serviceDhl->postShipment($shipment);

        if (!$response) {
            return false;
        }

        $infoShipment = $this->saveShipment($shipment, $response);

        if (!$infoShipment) {
            return false;
        }

        foreach ($response->pieces as $piece) {
            $labelResponse = $serviceDhl->getLabel($piece->labelId);

            if (!$labelResponse) {
                return false;
            }

            $pdf = base64_decode($labelResponse->pdf);

            if (!$this->createFile($pdf, $labelResponse->labelId)) {
                return false;
            }

            $labelData = [
                'id_shipment'  => $infoShipment['id_shipment'],
                'package_id'   => $labelResponse->labelId,
                'label_type'   => $labelResponse->labelType,
                'tracker_code' => $labelResponse->trackerCode,
            ];

            if (!$this->saveLabel($labelData)) {
                return false;
            }
        }
        return true;
    }
}
