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
use Roanja\Module\RjCarrier\Carrier\Dhl\ServiceDhl;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\lib\Common;

/**
 * Class CarrierDhl.
 */
class CarrierDhl extends CarrierCompany
{

    public function __construct()
    {
        $this->show_create_label = false;

        $this->name_carrier = 'DHL';
        $this->shortname = 'DHL';
        
        /**
         * Names of fields config DHL carrier used
         */
        $this->setFielConfig();

        parent::__construct();
    }

    /**
     * Setea los fields config del plugin
     *
     * @return void
     */
    public function setFielConfig()
    {
        $this->fields_config = [
            [
                'type' => 'text',
                'label' => $this->l('accountId'),
                'name' => 'RJ_DHL_ACCOUNID',
                'required' => true,
                'class' => 'fixed-width-lg',
            ],
            [
                'type' => 'text',
                'label' => $this->l('user Id'),
                'name' => 'RJ_DHL_USERID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('user Id DEV'),
                'name' => 'RJ_DHL_USERID_DEV',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key'),
                'name' => 'RJ_DHL_KEY',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key DEV'),
                'name' => 'RJ_DHL_KEY_DEV',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Production'),
                'name' => 'RJ_DHL_URL',
                'required' => true,
                'desc' => $this->l('Format url http:// or https:// .'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Develop'),
                'name' => 'RJ_DHL_URL_DEV',
                'desc' => $this->l('Format url http:// or https:// .'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint login'),
                'name' => 'RJ_DHL_ENDPOINT_LOGIN',
                'required' => true,
                'desc' => $this->l('Example: /authenticate/api-key'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint refresh token'),
                'name' => 'RJ_DHL_ENDPOINT_REFRESH_TOKEN',
                'required' => true,
                'desc' => $this->l('Example: /authenticate/refresh-token'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint Shipment'),
                'name' => 'RJ_DHL_ENDPOINT_SHIPMENT',
                'required' => true,
                'desc' => $this->l('Example: /shipments'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint Label'),
                'name' => 'RJ_DHL_ENDPOINT_LABEL',
                'required' => true,
                'desc' => $this->l('Example: /labels'),
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Modo producción'),
                'name' => 'RJ_DHL_ENV',
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

    public function renderConfig()
    {
        $this->setFieldsFormConfig();
        
        return parent::renderConfig();
    }

    private function setFieldsFormConfig()
    {
        $this->fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('DHL information'),
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
     * Crea envío DHL
     *
     * @param array $shipment
     * @return void
     */
    public function createShipment($shipment)
    {
        $id_order = $shipment['id_order'];
        $shipment['num_shipment'] = Common::getUUID();
        $service_dhl = new ServiceDhl($id_order);
        $response = $service_dhl->postShipment($shipment);

        if(!$response) {
            return false;
        }

        $info_shipment = $this->saveShipment($shipment, $response);

        if($info_shipment['id_shipment']){
            $labels = $response->pieces;
            foreach ($labels as $label) {
                $label_response = $service_dhl->getLabel($label->labelId);
                $this->saveLabels($info_shipment['id_shipment'], $label_response);
            }

            return true;
        } 

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
