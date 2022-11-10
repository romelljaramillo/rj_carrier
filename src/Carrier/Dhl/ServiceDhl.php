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

use Roanja\Module\RjCarrier\Carrier\Dhl\CarrierDhl;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

use Configuration;
use Shop;
use Country;

Class ServiceDhl {
    protected $user_id;
    protected $account_id;
    protected $key;
    protected $base_url;
    protected $endpoint_login;
    protected $endpoint_refresh_token;
    protected $endpoint_shipments;
    protected $endpoint_labels;

    protected $body;
    protected $id_order;
    protected $token = null;
    protected $access_token = 'access_token_dhl';
    protected $refresh_token = 'refresh_token_dhl';

    public function __construct($shipment)
    {
        $this->id_shop_group = Shop::getContextShopGroupID();
		$this->id_shop = Shop::getContextShopID();

        $this->id_order = $shipment['id_order'];
        
        $this->getConfiguration();
        $this->postLogin();
        $this->getBodyShipment($shipment);
    }

    private function getConfiguration()
    {
        $dev = '';

        $carrierCex = new CarrierDhl();
        $this->configuration = $carrierCex->getConfigFieldsValues();

        if(!$this->configuration['RJ_DHL_ENV']){
            $dev = '_DEV';
        }

        $this->account_id = $this->configuration['RJ_DHL_ACCOUNID'];
        $this->user_id = $this->configuration['RJ_DHL_USERID'. $dev];
        $this->key = $this->configuration['RJ_DHL_KEY'. $dev];
        $this->base_url = $this->configuration['RJ_DHL_URL'. $dev];
        $this->endpoint_login = $this->configuration['RJ_DHL_ENDPOINT_LOGIN'];
        $this->endpoint_refresh_token = $this->configuration['RJ_DHL_ENDPOINT_REFRESH_TOKEN'];
        $this->endpoint_shipments = $this->configuration['RJ_DHL_ENDPOINT_SHIPMENT'];
        $this->endpoint_labels = $this->configuration['RJ_DHL_ENDPOINT_LABEL'];
    }

    public function postLogin()
    {
        if(!$this->getCookieToken()){
            $body = $this->bodyLogin();
            $resp = $this->request('POST', $this->endpoint_login , $body);
            if($resp){
                $this->setCookies($resp);
            }
        }
    }

    private function bodyLogin()
    {
        $body = array(
            "userId"=> $this->user_id, 
            "key"=> $this->key
        );

        return json_encode($body);
    }

    private function setCookies($cookies)
    {
        setcookie(
            $this->access_token, 
            $cookies->accessToken, 
            $cookies->accessTokenExpiration
        );

        setcookie(
            $this->refresh_token, 
            $cookies->refreshToken, 
            $cookies->refreshTokenExpiration
        );

        $this->token = $cookies->accessToken;
    }

    public function getCookieToken()
    {
        if(isset($_COOKIE[$this->access_token])) {
            $this->token = $_COOKIE[$this->access_token];
            return true;
        } elseif (isset($_COOKIE[$this->refresh_token])) {
            $refresh_token = json_encode(array($this->refresh_token => $_COOKIE[$this->refresh_token]));
            $resp = $this->request('POST', $this->endpoint_refresh_token, $refresh_token);
            
            if($resp){
                return $this->setCookies($resp);
            }

            return false;
        }
        return false;
    }

    /**
     * Crea envío y retorna respuesta de la API
     *
     * @param array $body
     * @return obj
     */
    public function postShipment()
    {
        $resp = $this->request('POST', $this->endpoint_shipments, $this->body);
        return $resp;
    }

    public function getShipment($id_shipment)
    {
        $endpoint_shipments = $this->endpoint_shipments . '/' . $id_shipment;
        return $this->request('GET', $endpoint_shipments);
    }

    /**
     * Devuelve la respuesta de las etiquetas del servicio DHL
     *
     * @param string $labelId
     * @return obj
     */
    public function getLabel($id_label)
    {
        $endpoint_label = $this->endpoint_labels . '/' . $id_label;
        return $this->request('GET', $endpoint_label);
    }

    public function getBodyShipment($info_shipment)
    {
        $num_shipment = $info_shipment['info_shipment']['num_shipment'];
        $info_receiver = $info_shipment['info_customer'];
        $info_shipper = $info_shipment['info_shop'];
        $info_package = $info_shipment['info_package'];
        $info_receiver['referenceClient'] = $info_package['message'];

        $receiver = $this->getReceiver($info_receiver);
        $shipper = $this->getShipper($info_shipper);
        $pieces = $this->getPieces($info_package);

        $options[] = [
            "key"   => "REFERENCE",
            "input" => $this->id_order
        ];

        if($info_package['cash_ondelivery'] > 0){
            $options[] = [
                "key"   => "COD_CASH",
                "input" => $info_package['cash_ondelivery']
            ];
        }

        // $type_shipment = new RjcarrierTypeShipment((int)$info_package['id_type_shipment']);
        
        $data = [
            "shipmentId" => $num_shipment,
            "orderReference" => $this->id_order,
            "receiver" => $receiver,
            "shipper" => $shipper,
            "accountId" =>  $this->account_id,
            "options" => $options,
            "returnLabel" => false,
            // 'product' => $type_shipment->id_bc,
            "pieces" => $pieces
        ];

        $this->body = json_encode($data);
    }

    public function getPieces($info)
    {
        $weight = (float)$info['weight'] / (float)$info['quantity'];
        return [
            [
                "parcelType" => "SMALL",
                "quantity" => (int)$info['quantity'],
                "weight" => (float)$weight,
                "dimensions" => [
                    "length" => (float)$info['length'],
                    "width" => (float)$info['width'],
                    "height" => (float)$info['height']
                ]
            ]
        ];
    }

    /**
     * Crea el formato de quien recibe
     *
     * @param [array] $infoReceiver nota: hacer una interface
     * @return void
     */
    public function getReceiver($info)
    {
        $phone = '';

        if($info['phone_mobile']) {
            $phone = $info['phone_mobile'];
        } elseif($info['phone']){
            $phone = $info['phone'];
        }

        return [
            "name" => [
                "firstName"=> $info['firstname'],
                "lastName"=> $info['lastname'],
                "companyName"=> $info['company'],
                "additionalName"=> $info['firstname']
            ],
            "address"=> [
                "countryCode"=> $info['countrycode'],
                "postalCode"=> $info['postcode'],
                "city"=> $info['city'],
                "street"=> $info['address1'],
                "additionalAddressLine"=> $info['address2'],
                "number"=> '',
                "isBusiness"=> ($info['company'])?true:false,
                "addition"=> $info['other']
            ],
            "email"=> $info['email'],
            "phoneNumber"=> $phone,
            "vatNumber"=> $info['vat_number'],
            "eoriNumber"=> $info['dni'],
            "reference"=> $info['referenceClient']
        ];
    }

    /**
     * Crea el formato de quien envia
     *
     * @param [array] $infoReceiver nota: hacer una interface
     * @return void
     */
    public function getShipper($info)
    {
        $countrycode = Country::getIsoById($info['id_country']);
        return [
            "name" => [
                "firstName"=> $info['firstname'],
                "lastName"=> $info['lastname'],
                "companyName"=> $info['company'],
                "additionalName"=> $info['additionalname']
            ],
            "address"=> [
                "countryCode"=> $countrycode,
                "postalCode"=> $info['postcode'],
                "city"=> $info['state'],
                "street"=> $info['street'] . ' ' . $info['city'],
                "additionalAddressLine"=> $info['additionaladdress'],
                "number"=> $info['number'],
                "isBusiness"=> ($info['company'])?true:false,
                "addition"=> ''
            ],
            "email"=> $info['email'],
            "phoneNumber"=> $info['phone'],
            "vatNumber"=> $info['vatnumber'],
            "eoriNumber"=> ''
        ];
    }

    private function headerRequest()
    {
        if(!$this->token){
            if(isset($_COOKIE[$this->access_token]))
                $this->token = $_COOKIE[$this->access_token];
        }

        return array(
            'Content-Type: application/json',
            'Accept:application/json',
            'Authorization: Bearer ' . $this->token
        );
    }

    /**
     * request DHL
     *
     * @param string $requestMethod
     * @param string $urlparam
     * @param json $body
     * @return array
     */
    private function request($method, $endpoin, $body = null)
    {
        $header = $this->headerRequest();
        $url = $this->base_url . $endpoin;
        
        $ch = curl_init();

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST  => false,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_URL            => $url,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_HTTPHEADER     => $header,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_CUSTOMREQUEST  => $method,
            )
        );
        
        $response = utf8_encode(curl_exec($ch));

        if ($response === false) {
            return false;
        }
        
        $curl_info = curl_getinfo($ch);
        $curl_error = curl_errno($ch);

        curl_close($ch);
        
        if (!in_array($curl_info['http_code'], array(200, 201)) || $curl_error) {
            CarrierDhl::saveLog($url, $this->id_order, $body, $response);
            return false;
        }

        return json_decode($response);
    }
}