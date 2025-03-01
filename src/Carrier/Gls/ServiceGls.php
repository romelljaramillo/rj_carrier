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

namespace Roanja\Module\RjCarrier\Carrier\Gls;

use Roanja\Module\RjCarrier\Carrier\Gls\CarrierGls;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

use Configuration;
use Shop;
use Country;

Class ServiceGls {
    protected $user_id;
    protected $account_id;
    protected $key;
    protected $base_url;
    protected $endpoint_login;
    protected $endpoint_refresh_token;
    protected $endpoint_shipments;
    protected $endpoint_labels;

    protected $id_order;
    protected $token = null;
    protected $access_token = 'access_token_gls';
    protected $refresh_token = 'refresh_token_gls';
    protected $configuration = [];

    public function __construct($id_order)
    {
        $this->id_order = $id_order;

        $this->getConfiguration();
    }

    private function getConfiguration()
    {
        $dev = '';
        $carrier = new CarrierGls();
        $this->configuration = $carrier->getValuesConfigFields();

        if(!$this->configuration['RJ_GLS_ENV']){
            $dev = '_DEV';
        }

        $this->user_id = $this->configuration['RJ_GLS_GUID'. $dev];
        $this->base_url = $this->configuration['RJ_GLS_URL'];
    }

    /**
     * Crea envío y retorna respuesta de la API
     *
     * @param array $body
     * @return obj
     */
    public function postShipment($shipment)
    {
        $body = $this->getBodyShipment($shipment);
        return $this->request($body);
    }

    /**
     * Devuelve la respuesta de las etiquetas del servicio GLS
     *
     * @param string $labelId
     * @return obj
     */
    public function getLabel($id_label)
    {
        $endpoint_label = $this->endpoint_labels . '/' . $id_label;
        return $this->request($endpoint_label);
    }

    public function getBodyShipment($info_shipment)
    {
        $num_shipment = $info_shipment['num_shipment'];
        $info_receiver = $info_shipment['info_customer'];
        $info_shipper = $info_shipment['info_shop'];
        $info_package = $info_shipment['info_package'];
        $info_type_shipment = $info_shipment['info_type_shipment'];
        $config_extra_carrier = $info_shipment['config_extra_info'];
        $info_receiver['message'] = $info_package['message'];


        $receiver = $this->getReceiver($info_receiver);
        $shipper = $this->getShipper($info_shipper);
        $pieces = $this->getPieces($info_shipment);
        $reference = $this->getReference($info_shipment);


        // $type_shipment = new RjcarrierTypeShipment((int)$info_package['id_type_shipment']);

        /* $data = [
            "shipmentId" => $num_shipment,
            "orderReference" => (string)$this->id_order,
            "receiver" => $receiver,
            "shipper" => $shipper,
            "accountId" =>  $this->account_id,
            "options" => $options,
            "returnLabel" => false,
            // 'product' => $type_shipment->id_bc,
            "pieces" => $pieces
        ]; */

        // return json_encode($data);
    }


    public function getReference($info_shipment)
    {
        $reference = sprintf('%010d', $info_shipment['id_order']);
        $reference3 = '';

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $reference3 = '<Referencia tipo="C">' . $info_shipment['reference'] . '</Referencia>';
        }

        return '<Referencias>
                    <Referencia tipo="0">' . $reference . '</Referencia>
                    ' . $reference3 . '
                </Referencias>';
    }

    public function getCashOnDelivery($cash_ondelivery)
    {
        $cashOndelivery = '';

        if($cash_ondelivery != 0){
            $cashOndelivery = '<Importes><Reembolso>' . $cash_ondelivery . '</Reembolso></Importes>';
        }

        return $cashOndelivery;
    }

    public function getInsuredValue($vsec_user)
    {
        $insuredValue = '';

        if (!empty($vsec_user) && $vsec_user > 0) {
            $insuredValue = '<Seguro tipo=\"1\">
                                <Descripcion></Descripcion>
                                <Importe>' . $vsec_user . '</Importe>
                            </Seguro>';
        }

        return $insuredValue;
    }

    public function getPieces($info_shipment)
    {
        $info_package = $info_shipment['info_package'];
        $info_type_shipment = $info_shipment['info_type_shipment'];

        $service = $this->assignServiceGLS($info_type_shipment['name']);

        $weight = floatval($info_package['weight'] * $info_package['quantity']);

        $XML = '<Pod>' . ($info_package['rcs_user'] == 0 ? 'N' : 'S') . '</Pod>
                <Portes>P</Portes>
                <Servicio>' . (int)$service['service'] . '</Servicio>
                <Horario>' . $service['schedule'] . '</Horario>
                <Bultos>' .  (int)$info_package['quantity'] . '</Bultos>
                <Peso>' . $weight . '</Peso>';

        if (($service['service'] == 74 || $service['service'] == 76) &&  $info_package['incoterm'] > 0) {
            $XML .=  '<Aduanas><Incoterm>' .(int)$info_package['incoterm'] . '</Incoterm></Aduanas>';
        } else {
            $XML .= '<Retorno>' . (int)$info_package['retorno'] . '</Retorno>';
        }

        return $XML;
    }

    /**
     * Asigna el servicio de GLS
     *
     * @param [string] $service
     * @return array
     */
    private function assignServiceGLS($service)
    {
        $service = strtoupper($service);

        $servicioMapping = [
            'GLS10' => ['service_type' => 'GLS10', 'service' => 1, 'schedule' => 0],
            'GLS14' => ['service_type' => 'GLS14', 'service' => 1, 'schedule' => 2],
            'GLS24' => ['service_type' => 'GLS24', 'service' => 1, 'schedule' => 3],
            'BUSPAR' => ['service_type' => 'BUSPAR', 'service' => 96, 'schedule' => 18],
            'ECONOMY' => ['service_type' => 'ECONOMY', 'service' => 37, 'schedule' => 18],
            'EUROBUSINESSPARCEL' => ['service_type' => 'EUROBUSINESSPARCEL', 'service' => 74, 'schedule' => 3]
        ];

        return $servicioMapping[$service] ?? null;
    }

    /**
     * Crea el formato de quien recibe
     *
     * @param [array] $infoReceiver nota: hacer una interface
     * @return xml
     */
    public function getReceiver($info_receiver)
    {
        $countrycode = strtoupper(Country::getIsoById($info_receiver['id_country']));

        if ($countrycode == 'ES') {
            $countrycode = '34';
        } elseif ($countrycode == 'PT') {
            $countrycode = '351';
        }

        $mobile = str_replace(" ", "", $info_receiver['phone_mobile']);
        $phone = str_replace(" ", "", $info_receiver['phone']);

        $phone = $phone ?: $mobile;
        $mobile = $mobile ?: $phone;

        $nif = $info_receiver['vat_number'] ?? $info_receiver['dni'];

        return '<destinatario>
                    <Codigo></Codigo>
                    <Plaza></Plaza>
                    <Nombre><![CDATA[' . $info_receiver['firstname'] . ' ' . $info_receiver['lastname'] . ']]></Nombre>
                    <Direccion><![CDATA[' . $info_receiver["address1"] . ']]></Direccion>
                    <Poblacion><![CDATA[' . $info_receiver["city"] . ']]></Poblacion>
                    <Provincia><![CDATA[' . $info_receiver["state"] . ']]></Provincia>
                    <Pais>' . $countrycode. '</Pais>
                    <CP>' . $info_receiver["postcode"] . '</CP>
                    <Telefono>' . $phone . '</Telefono>
                    <Movil>' . $mobile . '</Movil>
                    <Email>' . $info_receiver["email"] . '</Email>
                    <NIF>' . $nif . '</NIF>
                    <Observaciones><![CDATA[' . $info_receiver["message"] . ']]></Observaciones>
                </destinatario>';
    }

    /**
     * Crea el formato de quien envia
     *
     * @param [array] $infoReceiver nota: hacer una interface
     * @return xml
     */
    public function getShipper($info)
    {
        $countrycode = strtoupper(Country::getIsoById($info['id_country']));

        if ($countrycode == 'ES') {
            $countrycode = '34';
        } elseif ($countrycode == 'PT') {
            $countrycode = '351';
        }

        return '<Remite>
                    <Plaza></Plaza>
                    <Nombre><![CDATA[' . $info["company"] . ']]></Nombre>
                    <Direccion><![CDATA[' .  $info['street'] . ']]></Direccion>
                    <Poblacion><![CDATA[' . $info["city"] . ']]></Poblacion>
                    <Provincia><![CDATA[' . $info["state"] . ']]></Provincia>
                    <Pais>' . $countrycode . '</Pais>
                    <CP>' . $info["postcode"] . '</CP>
                    <Telefono><![CDATA[' . $info["company"] . ']]></Telefono>
                    <Movil><![CDATA[]]></Movil>
                    <Email><![CDATA[' . $info["email"] . ']]></Email>
                    <Observaciones><![CDATA[]]></Observaciones>
                </Remite>';
    }

    private function headerRequest()
    {
        return ["Content-Type: text/xml; charset=UTF-8"];
    }

    /**
     * request GLS
     *
     * @param string $method
     * @param string $endpoin
     * @param json $body
     * @return array
     */
    private function request($body = null)
    {
        $header = $this->headerRequest();
        $url = $this->base_url;

        $ch = curl_init();

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_HEADER         => false,
                CURLOPT_FORBID_REUSE    => true,
                CURLOPT_FRESH_CONNECT   => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_URL            => $url,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_HTTPHEADER     => $header,
            )
        );

        $response = curl_exec($ch);

        if ($response === false) {
            return false;
        }

        $curl_info = curl_getinfo($ch);
        $curl_error = curl_errno($ch);

        curl_close($ch);

        if (!in_array($curl_info['http_code'], array(200, 201)) || $curl_error) {
            CarrierGls::saveLog($url, $this->id_order, $body, $response);
            return false;
        }

        return $response;
    }
}
