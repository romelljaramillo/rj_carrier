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

/**
 * Class CarrierGoi.
 */
class CarrierGoi extends CarrierCompany
{

    public function __construct()
    {
        $this->show_create_label = true;

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
                'label' => $this->l('User Id'),
                'name' => 'RJ_GOI_USERID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('User Id DEV'),
                'name' => 'RJ_GOI_USERID_DEV',
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Store Id'),
                'name' => 'RJ_GOI_STOREID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Store Id DEV'),
                'name' => 'RJ_GOI_STOREID_DEV',
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key'),
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
                'name' => 'RJ_GOI_URL',
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
                'type' => 'text',
                'label' => $this->l('Endpoint login'),
                'name' => 'RJ_GOI_ENDPOINT_LOGIN',
                'required' => true,
                'desc' => $this->l('Example: /oauth/token'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint Shipment'),
                'name' => 'RJ_GOI_ENDPOINT_SHIPMENT',
                'required' => true,
                'desc' => $this->l('Example: /integrations/import'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint Label'),
                'name' => 'RJ_GOI_ENDPOINT_LABEL',
                'required' => true,
                'desc' => $this->l('Example: /integrations/labels'),
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
        $id_order = $shipment['id_order'];
        $service_goi = new ServiceGoi($id_order);
        $response = $service_goi->postShipment($shipment);

        if(!$response) {
            return false;
        }

        $info_shipment = $this->saveShipment($shipment, $response);

        if($info_shipment['id_shipment']){
            $pdf = $service_goi->getLabel($id_order);
            return $this->saveLabels($info_shipment['id_shipment'], $pdf);
        } 

        return false;
    }

    public function createLabel($id_shipment, $id_order)
    {
        $service_goi = new ServiceGoi($id_order);
        $pdf = $service_goi->getLabel($id_order);

        if(!$pdf){
            return false;
        }

        return $this->saveLabels($id_shipment, $pdf);
    }
}
