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
        $this->name_carrier = 'DHL';
        $this->shortname = 'DHL';
        /**
         * Names of fields config DHL carrier used
         */

        $this->fields_config = [
            [
                'name' => 'RJ_DHL_ACCOUNID',
                'require' => true,
                'type' => 'string'
            ],
            [
                'name' => 'RJ_DHL_USERID',
                'require' => true,
                'type' => 'string'
            ],
            [
                'name' => 'RJ_DHL_KEY',
                'require' => true,
                'type' => 'string'
            ],
            [
                'name' => 'RJ_DHL_KEY_DEV',
                'require' => false,
                'type' => 'string'
            ],
            [
                'name' => 'RJ_DHL_URL_PRO',
                'require' => true,
                'type' => 'string'
            ],
            [
                'name' => 'RJ_DHL_URL_DEV',
                'require' => true,
                'type' => 'string'
            ],
            [
                'name' => 'RJ_DHL_ENV',
                'require' => false,
                'type' => 'boolean'
            ],
        ];

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
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('accountId'),
                        'name' => 'RJ_DHL_ACCOUNID',
                        'required' => true,
                        'class' => 'fixed-width-lg',
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
                        'desc' => $this->l('Format url http:// or https:// .'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Url Develop'),
                        'name' => 'RJ_DHL_URL_DEV',
                        'required' => true,
                        'desc' => $this->l('Format url http:// or https:// .'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Modo producción'),
                        'name' => 'RJ_DHL_ENV',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Production'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Develop'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Crea envío DHL
     *
     * @param array $infoOrder
     * @return void
     */
    public function createShipment($shipment)
    {

        $id_shipment = $shipment['info_shipment']['id_shipment'];

        $service_dhl = new ServiceDhl();
        $body_shipment = $service_dhl->getBodyShipment($shipment);
        $response = $service_dhl->postShipment($body_shipment);

        if(!$response) {
            return false;
        }

        $this->saveRequestShipment($id_shipment, $body_shipment);

        $this->saveResponseShipment($id_shipment, $response);
        
        if($id_shipment){
            return $this->saveLabels($id_shipment, $response);
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
