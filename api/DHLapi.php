<?php
use Ramsey\Uuid\Uuid;

Class DHLapi {
    protected $userId;
    protected $key;
    protected $baseUrl = 'https://api-gw.dhlparcel.nl';
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
            $this->baseUrl = Configuration::get('RJ_DHL_URL_PRO', null, $this->id_shop_group, $this->id_shop);
        } else {
            $this->key = Configuration::get('RJ_DHL_KEY_DEV', null, $this->id_shop_group, $this->id_shop);
            $this->baseUrl = Configuration::get('RJ_DHL_URL_DEV', null, $this->id_shop_group, $this->id_shop);
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
        $res = setcookie(
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

        return $res;
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
                if($this->setCookies($resp)){
                    $this->accessToken = $resp->{'accessToken'};
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    public function postShipment($body)
    {
        $body = json_encode($body);
        $resp = $this->request('POST', $this->urlShipments, $body);
        return $resp;
    }

    public function getShipment($shipmentId)
    {
        $urlShipments = $this->urlShipments . '/' . $shipmentId;
        $resp = $this->request('GET', $urlShipments);
        return $resp;
    }

    public function getLabel($labelId)
    {
        $urlLabel = $this->urlLabels . '/' . $labelId;
        $resp = $this->request('GET', $urlLabel);
        return $resp;
    }

    private function headerRequest(){
        if(!$this->accessToken){
            $this->accessToken = $_COOKIE['accessToken'];
        }

        return array(
            'Content-Type: application/json',
            'Accept:application/json',
            'Authorization: Bearer ' . $this->accessToken
        );
    }

    public function generateBodyShipment($infoShipment)
    {
        $infoReceiver = $infoShipment['infoCustomer'];
        $countryCode = Country::getIsoById($infoReceiver['id_country']);
        $customer = new Customer((int)$infoReceiver['id_customer']);
        $infoReceiver['countryCode'] = $countryCode;
        $infoReceiver['email'] = $customer->email;


        $infoShipper = $infoShipment['infoShop'];

        $infoPackage = $infoShipment['infoPackage'];
        $uuid = $this->generateUUID();
        $receiver = $this->getReceiver($infoReceiver);
        $shipper = $this->getShipper($infoShipper);
        $pieces = $this->getPieces($infoPackage);

        if($infoPackage['price_contrareembolso'] > 0){
            $options = [
                "key"   => "COD_CASH",
                "input" => $infoPackage['price_contrareembolso']
            ];
        } else {
            $options = [
                "key"   => "DOOR"
            ];
        }

        $accountId = Configuration::get('RJ_DHL_ACCOUNID', null, $this->id_shop_group, $this->id_shop);

        return [
            "shipmentId" => $uuid,
            "orderReference" => "DHL2",
            "receiver" => $receiver,
            "shipper" => $shipper,
            "accountId" => $accountId,
            "options" => [$options],
            "returnLabel" => false,
            "pieces" => $pieces
        ];
    }

    public function getPieces($info)
    {
        return [
            [
                "parcelType" => "SMALL",
                "quantity" => (int)$info['packages'],
                "weight" => (float)$info['weight'],
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
        return [
            "name" => [
                "firstName"=> $info['firstname'],
                "lastName"=> $info['lastname'],
                "companyName"=> $info['company'],
                "additionalName"=> $info['firstname']
            ],
            "address"=> [
                "countryCode"=> $info['countryCode'],
                "postalCode"=> $info['postcode'],
                "city"=> $info['city'],
                "street"=> $info['address1'],
                "additionalAddressLine"=> $info['address2'],
                "number"=> '',
                "isBusiness"=> ($info['company'])?true:false,
                "addition"=> $info['other']
            ],
            "email"=> $info['email'],
            "phoneNumber"=> $info['phone'],
            "vatNumber"=> $info['vat_number'],
            "eoriNumber"=> $info['dni']
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
        return [
            "name" => [
                "firstName"=> $info['firstname'],
                "lastName"=> $info['lastname'],
                "companyName"=> $info['company'],
                "additionalName"=> $info['additionalname']
            ],
            "address"=> [
                "countryCode"=> $info['countrycode'],
                "postalCode"=> $info['postcode'],
                "city"=> $info['city'],
                "street"=> $info['street'],
                "additionalAddressLine"=> $info['additionaladdress'],
                "number"=> $info['number'],
                "isBusiness"=> ($info['company'])?true:false,
                "addition"=> $info['addition']
            ],
            "email"=> $info['email'],
            "phoneNumber"=> $info['phone'],
            "vatNumber"=> $info['vatnumber'],
            "eoriNumber"=> $info['eorinumber']
        ];
    }

    public function generateUUID(){
        $uuid = Uuid::uuid4();
        return $uuid->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
    }

    /**
     * Undocumented function
     *
     * @param [type] $requestMethod
     * @param [type] $urlparam
     * @param [type] $body
     * @return void
     */
    public function request($requestMethod, $urlparam, $body = null)
    {
        $header = $this->headerRequest();
        $url = $this->baseUrl . $urlparam;
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL            => $url,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_HTTPHEADER     => $header,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_CUSTOMREQUEST  => $requestMethod,
            )
        );
        
        $response = utf8_encode(curl_exec($curl));

        if ($response === false) {
            return false;
        }
        
        $curlInfo = curl_getinfo($curl);
        $curlError = curl_errno($curl);
        
        if (!in_array($curlInfo['http_code'], array(200, 201))) {
            return false;
        }
        if ($curlError) {
            return false;
        }

        $responseJson = json_decode($response);

        if ($responseJson) {
            return $responseJson;
        } else {
            return false;
        }
    }
}