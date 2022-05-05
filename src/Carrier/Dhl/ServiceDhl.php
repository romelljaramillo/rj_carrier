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

use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

use Configuration;
use Shop;
use Country;

Class ServiceDhl {
    protected $userId;
    protected $key;
    protected $base_url = 'https://api-gw.dhlparcel.nl';
    protected $urllogin = '/authenticate/api-key';
    protected $urlShipments = '/shipments';
    protected $urlLabels = '/labels';
    protected $urlRefresToken = '/authenticate/refresh-token';
    protected $accessToken = null;

    public function __construct()
    {
        
        $this->id_shop_group = Shop::getContextShopGroupID();
		$this->id_shop = Shop::getContextShopID();
        
        $this->getConfigurationDHL();
        $this->postLogin();
    }

    private function getConfigurationDHL()
    {
        $this->userId = Configuration::get('RJ_DHL_USERID', null, $this->id_shop_group, $this->id_shop);
        $env = Configuration::get('RJ_DHL_ENV', null, $this->id_shop_group, $this->id_shop);
        if($env){
            $this->key = Configuration::get('RJ_DHL_KEY', null, $this->id_shop_group, $this->id_shop);
            $this->base_url = Configuration::get('RJ_DHL_URL_PRO', null, $this->id_shop_group, $this->id_shop);
        } else {
            $this->key = Configuration::get('RJ_DHL_KEY_DEV', null, $this->id_shop_group, $this->id_shop);
            $this->base_url = Configuration::get('RJ_DHL_URL_DEV', null, $this->id_shop_group, $this->id_shop);
        }
    }

    public function postLogin()
    {
        if(!$this->getCookieToken()){
            $body = $this->bodyLogin();
            $resp = $this->request('POST', $this->urllogin, $body);
            if($resp){
                return $this->setCookies($resp);
            }
        }
    }

    private function bodyLogin()
    {
        $body = array(
            "userId"=> $this->userId, 
            "key"=> $this->key
        );

        return json_encode($body);
    }

    private function setCookies($cookies)
    {
        setcookie(
            "accessToken", 
            $cookies->{'accessToken'}, 
            $cookies->{'accessTokenExpiration'}
        );

        setcookie(
            "refreshToken", 
            $cookies->{'refreshToken'}, 
            $cookies->{'refreshTokenExpiration'}
        );

        $this->accessToken = $cookies->{'accessToken'};

        return true;
    }

    public function getCookieToken()
    {
        if(isset($_COOKIE['accessToken'])) {
            $this->accessToken = $_COOKIE['accessToken'];
            return true;
        } elseif (isset($_COOKIE['refreshToken'])) {
            $refreshToken = json_encode(array('refreshToken' => $_COOKIE['refreshToken']));
            $resp = $this->request('POST', $this->urlRefresToken, $refreshToken);
            if($resp){
                return $this->setCookies($resp);
                // if($this->setCookies($resp)){
                //     $this->accessToken = $resp->{'accessToken'};
                //     return true;
                // }
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
    public function postShipment($body_shipment)
    {
        $resp = $this->request('POST', $this->urlShipments, $body_shipment);
        return $resp;
    }

    public function getShipment($shipmentId)
    {
        $urlShipments = $this->urlShipments . '/' . $shipmentId;
        return $this->request('GET', $urlShipments);
    }

    /**
     * Devuelve la respuesta de las etiquetas del servicio DHL
     *
     * @param string $labelId
     * @return obj
     */
    public function getLabel($labelId)
    {
        $urlLabel = $this->urlLabels . '/' . $labelId;
        return $this->request('GET', $urlLabel);
    }

    public function getBodyShipment($info_shipment)
    {
        $num_shipment = $info_shipment['info_shipment']['num_shipment'];
        $id_order = (string)$info_shipment['id_order'];
        $info_receiver = $info_shipment['info_customer'];
        $info_shipper = $info_shipment['info_shop'];
        $info_package = $info_shipment['info_package'];
        $info_receiver['referenceClient'] = $info_package['message'];

        $receiver = $this->getReceiver($info_receiver);
        $shipper = $this->getShipper($info_shipper);
        $pieces = $this->getPieces($info_package);

        if($info_package['cash_ondelivery'] > 0){
            $typeDelivery = [
                "key"   => "COD_CASH",
                "input" => $info_package['cash_ondelivery']
            ];
        } /* else {
            $typeDelivery = [
                "key"   => "DOOR"
            ];
        } */

        $options = [
            "key"   => "REFERENCE",
            "input" => $id_order
        ];

        $accountId = Configuration::get('RJ_DHL_ACCOUNID', null, $this->id_shop_group, $this->id_shop);
        
        // $type_shipment = new RjcarrierTypeShipment((int)$info_package['id_type_shipment']);
        
        $data = [
            "shipmentId" => $num_shipment,
            "orderReference" => $id_order,
            "receiver" => $receiver,
            "shipper" => $shipper,
            "accountId" => $accountId,
            "options" => [$typeDelivery, $options],
            "returnLabel" => false,
            // 'product' => $type_shipment->id_bc,
            "pieces" => $pieces
        ];

        return json_encode($data);
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
        if(!$this->accessToken){
            if(isset($_COOKIE['accessToken']))
                $this->accessToken = $_COOKIE['accessToken'];
        }

        return array(
            'Content-Type: application/json',
            'Accept:application/json',
            'Authorization: Bearer ' . $this->accessToken
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
    private function request($method, $urlparam, $body = null)
    {

        $header = $this->headerRequest();
        $url = $this->base_url . $urlparam;
        
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
            return false;
        }

        $responses = json_decode($response);

        if ($responses) {
            return $responses;
        } else {
            return false;
        }
    }
}