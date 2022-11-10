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

use Roanja\Module\RjCarrier\Carrier\Goi\CarrierGoi;
use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

use Configuration;
use Shop;
use Country;
use Order;

Class ServiceGoi {
    protected $userId;
    protected $store_id;
    protected $key;
    protected $base_url = 'https://api-jaw.letsgoi.com';
    protected $urllogin = '/oauth/token';
    protected $urlShipments = '/integrations/import';
    protected $urlLabels = '/integrations/labels';
    protected $urlRefresToken = '/oauth/token';
    protected $accessToken = null;
    protected $access_token = 'access_token_goi';
    protected $refresh_token = 'refresh_token_goi';
    protected $count = 0;
    protected $repetir_request = 10;
    protected $body;
    protected $id_order;

    public function __construct()
    {
        $this->id_shop_group = Shop::getContextShopGroupID();
		$this->id_shop = Shop::getContextShopID();

        $this->getConfiguration();
        $this->getCookieToken();
    }

    private function getConfiguration()
    {
        $env = Configuration::get('RJ_GOI_ENV', null, $this->id_shop_group, $this->id_shop);
        if($env){
            $this->userId = Configuration::get('RJ_GOI_USERID', null, $this->id_shop_group, $this->id_shop);
            $this->key = Configuration::get('RJ_GOI_KEY', null, $this->id_shop_group, $this->id_shop);
            $this->base_url = Configuration::get('RJ_GOI_URL_PRO', null, $this->id_shop_group, $this->id_shop);
            $this->store_id = Configuration::get('RJ_GOI_STOREID', null, $this->id_shop_group, $this->id_shop);
        } else {
            $this->userId = Configuration::get('RJ_GOI_USERID_DEV', null, $this->id_shop_group, $this->id_shop);
            $this->key = Configuration::get('RJ_GOI_KEY_DEV', null, $this->id_shop_group, $this->id_shop);
            $this->base_url = Configuration::get('RJ_GOI_URL_DEV', null, $this->id_shop_group, $this->id_shop);
            $this->store_id = Configuration::get('RJ_GOI_STOREID_DEV', null, $this->id_shop_group, $this->id_shop);
        }
    }

    public function postLogin()
    {
        $body = $this->bodyLogin();
        $resp = $this->request('POST', $this->urllogin, $body);

        if($resp){
            return $this->setCookies($resp);
        }
        return false;
    }

    private function bodyLogin()
    {
        $body = array(
            "client_id"=> $this->userId, 
            "client_secret"=> $this->key,
            "grant_type"=> 'client_credentials'
        );

        return json_encode($body);
    }

    private function setCookies($cookies)
    {
        setcookie(
            $this->access_token, 
            $cookies->access_token,
            $cookies->expires_in
        );

        $this->accessToken = $cookies->access_token;

        return true;
    }

    public function getCookieToken()
    {
        if(isset($_COOKIE[$this->access_token])) {
            $this->accessToken = $_COOKIE[$this->access_token];
            return true;
        } else {
            return $this->postLogin();
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
        $resp = $this->request('POST', $this->urlShipments, $this->body);
        return $resp;
    }

    public function getShipment($shipmentId)
    {
        $urlShipments = $this->urlShipments . '/' . $shipmentId;
        return $this->request('GET', $urlShipments);
    }

    /**
     * Devuelve la respuesta de las etiquetas del servicio GOI
     *
     * @param string $labelId
     * @return obj
     */
    public function getLabel($id_order)
    {
        $urlLabel = $this->urlLabels . '/' . $this->store_id . '/' . $id_order;
        return $this->request('GET', $urlLabel);
    }

    public function getBodyShipment($info_shipment)
    {
        // $num_shipment = (string)$info_shipment['info_shipment']['num_shipment'];
        $id_order = (string)$info_shipment['id_order'];
        $products = $this->getProductsOrder($id_order);

        $info_receiver = $info_shipment['info_customer'];
        $info_package = $info_shipment['info_package'];
        $info_receiver['notes'] = $info_package['message'];

        $receiver = $this->getReceiver($info_receiver);
        $pieces = $this->getPieces($info_package);

        $services = '';
        if(isset($info_shipment['info_package']['id_type_shipment'])) {
            $type_shipment = new RjcarrierTypeShipment((int)$info_shipment['info_package']['id_type_shipment']);
            $services = explode(",", $type_shipment->id_bc);
        }

        $metadata = [
            'id_order' => $id_order
        ];
        
        $data = [
            "order_id" => $id_order,
            "store_id" => $this->store_id,
            "metadata" => json_encode($metadata),
            "services" => $services
        ];

        $array_data = array_merge($data,$receiver,$pieces,$products);

        return json_encode($array_data);
    }

    public function getProductsOrder($id_order)
    {
        $order = new Order((int)$id_order);
        $products = $order->getProductsDetail();

        $array_products["retail_price"] = (float)$order->total_paid;

        foreach ($products as $product) {
            $volume = (float)$product['depth'] * (float)$product['width'] * (float)$product['height'];
            $array_products["articles"][] = [
                "id"=> $product["product_id"],
                "name"=> $product["product_name"],
                "quantity"=> (int)$product["product_quantity"],
                "volume"=> (float)$volume,
                "weight"=> (float)$product["weight"]
            ];
        }
        
        return $array_products;
    }

    public function getPieces($info)
    {
        $volume = (float)$info['length'] * (float)$info['width'] * (float)$info['height'];

        return  [
            "weight" => (float)$info['weight'],
            "volume" => (float)$volume,
            "packages" => (int)$info['quantity']
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
            "customer_firstname"=> $info['firstname'],
            "customer_lastname"=> $info['lastname'],
            "customer_phone"=> $phone,
            "customer_email"=> $info['email'],
            "address"=> $info['address1'],
            "additional_address"=> $info['address2'],
            "city"=>$info['city'],
            "zip"=> $info['postcode'],
            "country_code"=> $info['countrycode'],
            "notes"=> $info['notes']
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
            if(isset($_COOKIE[$this->access_token]))
                $this->accessToken = $_COOKIE[$this->access_token];
        }

        return array(
            'Content-Type: application/json',
            'Accept:application/json',
            'Authorization: Bearer ' . $this->accessToken
        );
    }

    /**
     * request GOI
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
        
        $response = curl_exec($ch);

        if ($response === false) {
            return false;
        }
        
        $curl_info = curl_getinfo($ch);
        $curl_error = curl_errno($ch);

        curl_close($ch);
        
        $res = strpos($urlparam, 'label');
        
        if($res) {
            if($curl_info['content_type'] == "application/pdf") {
                $this->count = 0;
                return $response;
            } else {
                if($this->count == $this->repetir_request){
                    // $id_shipment = RjcarrierShipment::getIdByIdOrder($this->id_order);
                    CarrierGoi::saveLog($url $this->id_order,, $body, $response);
                    return false;
                }
                
                $this->count++;
                $response = $this->request($method, $urlparam);
            }

            return $response;
        }

        if (!in_array($curl_info['http_code'], array(200, 201)) || $curl_error) {
            CarrierGoi::saveLog($url, $this->id_order, $body, $response);
            return false;
        }

        return json_decode($response);
    }
}