<?php
include_once (_PS_MODULE_DIR_ . 'rj_carrier/src/carriers/CarrierCompany.php');
include_once (_PS_MODULE_DIR_ . 'rj_carrier/src/carriers/dhl/ApiDhl.php');
include_once(_PS_MODULE_DIR_. 'rj_carrier/classes/RjcarrierShipment.php');
include_once(_PS_MODULE_DIR_. 'rj_carrier/classes/RjcarrierLabel.php');

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
            'RJ_DHL_ACCOUNID',
            'RJ_DHL_USERID',
            'RJ_DHL_KEY',
            'RJ_DHL_KEY_DEV',
            'RJ_DHL_URL_PRO',
            'RJ_DHL_URL_DEV',
            'RJ_DHL_ENV',
        ];

        // $this->fields_multi_confi = [];

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
     * @param [type] $infoOrder
     * @return void
     */
    public function createShipment($infoOrder)
    {
        $shipmentId = RjcarrierShipment::getShipmentIdByIdOrder($infoOrder['order_id']);

        if (!$shipmentId) {
            $apidhl = new ApiDhl();

            $body = $apidhl->generateBodyShipment($infoOrder);
            $dataShipment = $apidhl->postShipment($body);

            if (!isset($dataShipment->shipmentId)) {
                $this->errors[] = $this->l('Algo esta mal en la información del envío.');
                return false;
            }

            $this->saveShipment($dataShipment, $infoOrder);
            $idShipment = RjcarrierShipment::getIdShipmentByIdOrder($infoOrder['order_id']);
            $this->saveLabels($idShipment, $dataShipment);
            // $dataShipment = $apidhl->getShipment($dataShipment->shipmentId);

        } else {
            $this->errors[] = $this->l('Ya existe un envío para este pedido.');
            return false;
        }
        $this->success[] = $this->l('Envio realizado con exito.');
        return true;

    }

    public function saveShipment($dataShipment, $infoOrder)
    {
        $shipment = new RjcarrierShipment();
        $shipment->shipmentid = $dataShipment->shipmentId;
        $shipment->id_infopackage = $infoOrder['infoPackage']['id'];
        $shipment->id_order = $infoOrder['order_id'];
        $shipment->product = $dataShipment->product;
        $shipment->order_reference = $dataShipment->orderReference;

        $shipment->add();
        return true;
    }

    public function saveLabels($idShipment, $dataShipment)
    {
        $infoLabels = $dataShipment->pieces;
        $apidhl = new ApiDhl();
        foreach ($infoLabels as $label) {
            $labelapi = $apidhl->getLabel($label->labelId);
            $carrierLabel = new RjcarrierLabel();
            $carrierLabel->id_shipment = $idShipment;
            $carrierLabel->labelid = $labelapi->labelId;
            $carrierLabel->label_type = $labelapi->labelType;
            $carrierLabel->parcel_type = $labelapi->parcelType;
            $carrierLabel->tracker_code = $labelapi->trackerCode;
            $carrierLabel->piece_number = $labelapi->pieceNumber;
            $carrierLabel->routing_code = $labelapi->routingCode;
            $carrierLabel->userid = $labelapi->userId;
            $carrierLabel->organizationid = $labelapi->organizationId;
            $carrierLabel->order_reference = $labelapi->orderReference;
            $carrierLabel->pdf = $labelapi->pdf;

            $carrierLabel->add();
        }
        return true;
    }
}
