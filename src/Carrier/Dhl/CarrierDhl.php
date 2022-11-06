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
                'label' => $this->l('user Id PRO'),
                'name' => 'RJ_DHL_USERID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('user Id DEV'),
                'name' => 'RJ_DHL_USERID_DEV',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key PRO'),
                'name' => 'RJ_DHL_KEY',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key DEV'),
                'name' => 'RJ_DHL_KEY_DEV',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Production'),
                'name' => 'RJ_DHL_URL_PRO',
                'required' => true,
                'desc' => $this->l('Format url http:// or https:// .'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Develop'),
                'name' => 'RJ_DHL_URL_DEV',
                'required' => true,
                'desc' => $this->l('Format url http:// or https:// .'),
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
     * @param array $infoOrder
     * @return void
     */
    public function createShipment($shipment)
    {
        $service_dhl = new ServiceDhl();
        $body_shipment = $service_dhl->getBodyShipment($shipment);
        $response = $service_dhl->postShipment($body_shipment);

        if(!$response) {
            return false;
        }

        $info_shipment = $this->saveShipment($shipment, $response);
        $shipment['info_shipment'] = $info_shipment;

        if($info_shipment['id_shipment']){
            return $this->saveLabels($info_shipment['id_shipment'], $response);
        } 

        return false;
    }

    public function saveLabels($id_shipment, $response, $num_package = 1)
    {
        $info_labels = $response->pieces;
        $service_dhl = new ServiceDhl();
        foreach ($info_labels as $label) {
            $label_service = $service_dhl->getLabel($label->labelId);
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
