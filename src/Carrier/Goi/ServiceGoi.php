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
use Product;
use Validate;

Class ServiceGoi {
    protected $user_id;
    protected $store_id;
    protected $key;
    protected $base_url;
    protected $endpoint_login;
    protected $endpoint_shipments;
    protected $endpoint_labels;
    
    protected $id_order;
    protected $token = null;
    protected $access_token = 'access_token_goi';
    protected $refresh_token = 'refresh_token_goi';
    protected $count = 0;
    protected $repetir_request = 10;

    public function __construct($id_order)
    {
        $this->id_order = $id_order;

        $this->getConfiguration();
        $this->postLogin();
    }

    private function getConfiguration()
    {
        $dev = '';
        $carrier = new CarrierGoi();
        $this->configuration = $carrier->getConfigFieldsValues();

        if(!$this->configuration['RJ_GOI_ENV']){
            $dev = '_DEV';
        }

        $this->user_id = $this->configuration['RJ_GOI_USERID'. $dev];
        $this->store_id = $this->configuration['RJ_GOI_STOREID'. $dev];
        $this->key = $this->configuration['RJ_GOI_KEY'. $dev];
        $this->base_url = $this->configuration['RJ_GOI_URL'. $dev];
        $this->endpoint_login = $this->configuration['RJ_GOI_ENDPOINT_LOGIN'];
        $this->endpoint_shipments = $this->configuration['RJ_GOI_ENDPOINT_SHIPMENT'];
        $this->endpoint_labels = $this->configuration['RJ_GOI_ENDPOINT_LABEL'];
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
            "client_id"=> $this->user_id, 
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

        $this->token = $cookies->access_token;

        return true;
    }

    public function getCookieToken()
    {
        if(isset($_COOKIE[$this->access_token])) {
            $this->token = $_COOKIE[$this->access_token];
            return true;
        }

        return false;
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
        return $this->request('POST', $this->endpoint_shipments, $body);
    }

    /**
     * Devuelve la respuesta de las etiquetas del servicio GOI
     *
     * @param string $labelId
     * @return obj
     */
    public function getLabel($id_order)
    {
        $endpoint_labels = $this->endpoint_labels . '/' . $this->store_id . '/' . $id_order;
        return $this->request('GET', $endpoint_labels);
    }

    public function getBodyShipment($info_shipment)
    {
        $info_receiver = $info_shipment['info_customer'];
        $info_package = $info_shipment['info_package'];
        $info_receiver['notes'] = $info_package['message'];
        
        $products = $this->getProductsOrder();
        $receiver = $this->getReceiver($info_receiver);
        $pieces = $this->getPieces($info_package);

        $services = '';
        if(isset($info_shipment['info_package']['id_type_shipment'])) {
            $type_shipment = new RjcarrierTypeShipment((int)$info_shipment['info_package']['id_type_shipment']);
            $services = explode(",", $type_shipment->id_bc);
        }

        $metadata = [
            'id_order' => (string)$this->id_order
        ];
        
        $data = [
            "order_id" => (string)$this->id_order,
            "store_id" => $this->store_id,
            "metadata" => json_encode($metadata),
            "services" => $services
        ];

        $array_data = array_merge($data, $receiver, $pieces, $products);

        return json_encode($array_data);
    }

    public function getProductsOrder()
    {
        $order = new Order((int)$this->id_order);
        $products = $order->getProductsDetail();

        $array_products["retail_price"] = (float)$order->total_paid;

        foreach ($products as $product) {
			$product_name = $this->getProducName($product["product_id"]);
	
			$volume = (float)$product['depth'] * (float)$product['width'] * (float)$product['height'];
            $array_products["articles"][] = [
                "id"=> $product["product_id"],
                "name"=> substr($product_name,0,128),
                "quantity"=> (int)$product["product_quantity"],
                "volume"=> (float)$volume,
                "weight"=> (float)$product["weight"]
            ];
        }

        return $array_products;
    }
	
	private function getProducName($product_id) 
	{
		$product = new Product($product_id);

		if (Validate::isLoadedObject($product)) {
			$product_name = $product->name[Configuration::get('PS_LANG_DEFAULT')];
			return $product_name;
		}

		return '';
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
     * request GOI
     *
     * @param string $method
     * @param string $endpoin
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
        
        $response = curl_exec($ch);

        if ($response === false) {
            return false;
        }
        
        $curl_info = curl_getinfo($ch);
        $curl_error = curl_errno($ch);

        curl_close($ch);
        
        $res = strpos($endpoin, 'label');
        
        if($res) {
            if($curl_info['content_type'] == "application/pdf") {
                $this->count = 0;
                return $response;
            } else {
                if($this->count == $this->repetir_request){
                    CarrierGoi::saveLog($url, $this->id_order, $body, $response);
                    return false;
                }
                
                $this->count++;
                $response = $this->request($method, $endpoin);
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