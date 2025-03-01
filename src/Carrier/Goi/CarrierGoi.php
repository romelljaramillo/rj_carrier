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
use Roanja\Module\RjCarrier\Carrier\CarrierInterface;

/**
 * Class CarrierGoi.
 */
class CarrierGoi extends CarrierCompany implements CarrierInterface
{

    public function __construct()
    {
        $this->carrier_name = 'GOI';
        $this->shortname = 'GOI';
        $this->show_create_label = true;
        parent::__construct();

    }

    public function setFieldsConfig()
    {
        $this->fields_config = [
            [
                'type' => 'text',
                'label' => $this->l('User Id'),
                'name' => 'USERID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('User Id DEV'),
                'name' => 'USERID_DEV',
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Store Id'),
                'name' => 'STOREID',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Store Id DEV'),
                'name' => 'STOREID_DEV',
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key'),
                'name' => 'KEY',
                'required' => true,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Key DEV'),
                'name' => 'KEY_DEV',
                'required' => false,
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Production'),
                'name' => 'URL',
                'required' => true,
                'desc' => $this->l('Format url http:// or https:// .'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Url Develop'),
                'name' => 'URL_DEV',
                'required' => false,
                'desc' => $this->l('https://test-api-jaw.letsgoi.com'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint login'),
                'name' => 'ENDPOINT_LOGIN',
                'required' => true,
                'desc' => $this->l('Example: /oauth/token'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint Shipment'),
                'name' => 'ENDPOINT_SHIPMENT',
                'required' => true,
                'desc' => $this->l('Example: /integrations/import'),
            ],
            [
                'type' => 'text',
                'label' => $this->l('Endpoint Label'),
                'name' => 'ENDPOINT_LABEL',
                'required' => true,
                'desc' => $this->l('Example: /integrations/labels'),
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Modo producción'),
                'name' => 'ENV',
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
     * Crea envío GOI
     *
     * @param array $infoOrder
     * @return void
     */
    public function createShipment($shipment)
    {
        $id_order = $shipment['id_order'];
        $service_goi = new ServiceGoi($id_order);
        // $response = $service_goi->postShipment($shipment);

        // if(!$response) {
        //     return false;
        // }

        // $info_shipment = $this->saveShipment($shipment, $response);

        // if($info_shipment['id_shipment']){
        //     $pdf = $service_goi->getLabel($id_order);
        //     return $this->saveLabels($info_shipment['id_shipment'], $pdf);
        // }

        // return false;
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
