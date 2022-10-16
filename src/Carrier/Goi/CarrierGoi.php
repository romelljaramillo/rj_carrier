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

namespace Roanja\Module\RjCarrier\Carrier\Goi;

use Roanja\Module\RjCarrier\Carrier\CarrierCompany;
use Roanja\Module\RjCarrier\Carrier\Goi\ServiceGoi;
use Roanja\Module\RjCarrier\Model\RjcarrierLabel;
use Roanja\Module\RjCarrier\lib\Common;

/**
 * Class CarrierGoi.
 */
class CarrierGoi extends CarrierCompany
{

    public function __construct()
    {
        $this->name_carrier = 'GOI';
        $this->shortname = 'GOI';
        /**
         * Names of fields config GOI carrier used
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
                'label' => $this->l('user Id'),
                'name' => 'RJ_GOI_USERID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key PRO'),
                'name' => 'RJ_GOI_KEY',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key DEV'),
                'name' => 'RJ_GOI_KEY_DEV',
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Production'),
                'name' => 'RJ_GOI_URL_PRO',
                'required' => true,
                'desc' => $this->l('Format url http:// or https:// .'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Develop'),
                'name' => 'RJ_GOI_URL_DEV',
                'required' => false,
                'desc' => $this->l('https://test-api-jaw.letsgoi.com'),
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Modo producción'),
                'name' => 'RJ_GOI_ENV',
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
                    'title' => $this->l('GOI information'),
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
     * Crea envío GOI
     *
     * @param array $infoOrder
     * @return void
     */
    public function createShipment($shipment)
    {
        $id_shipment = $shipment['info_shipment']['id_shipment'];

        $service_goi = new ServiceGoi();

        $body_shipment = $service_goi->getBodyShipment($shipment);

        $this->saveRequestShipment($id_shipment, $body_shipment);
        
        $response = $service_goi->postShipment($body_shipment);

        if(!isset($response->shipmentId)) {
            return false;
        }

        $this->saveResponseShipment($id_shipment, $response);
        
        if($id_shipment){
            return $this->saveLabels($id_shipment, $response);
        } 

        return false;
    }

    public function saveLabels($id_shipment, $response, $num_package = 1)
    {
        $info_labels = $response->pieces;
        $service_goi = new ServiceGoi();
        foreach ($info_labels as $label) {
            $label_service = $service_goi->getLabel($label->labelId);
            $rj_carrier_label = new RjcarrierLabel();
            $rj_carrier_label->id_shipment = $id_shipment;
            $rj_carrier_label->package_id = $label_service->labelId;
            $rj_carrier_label->tracker_code = $label_service->trackerCode;
            $rj_carrier_label->label_type = $label_service->labelType;
            
            $pdf = base64_decode($label_service->pdf);

            if(Common::createFileLabel($pdf, $label_service->labelId)){
                $rj_carrier_label->pdf = $label_service->labelId;
            }
            
            if (!$rj_carrier_label->add())
                return false;
        }
        return true;
    }
}
