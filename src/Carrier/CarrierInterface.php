<?php

namespace Roanja\Module\RjCarrier\Carrier;

interface CarrierInterface
{
    public function setFieldsConfig();
    public function createShipment($shipment);
}
