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

use Country;

Class ServiceDhl {
    private string $userId;
    private string $accountId;
    private string $apiKey;
    private string $baseUrl;
    private string $endpointLogin;
    private string $endpointRefreshToken;
    private string $endpointShipments;
    private string $endpointLabels;

    private ?string $accessToken = null;
    private ?string $refreshToken = null;

    private $requiredKeys = [
        'DHL_ENV',
        'DHL_ACCOUNID',
        'DHL_USERID_DEV',
        'DHL_KEY_DEV',
        'DHL_URL_DEV',
        'DHL_ENDPOINT_LOGIN',
        'DHL_ENDPOINT_REFRESH_TOKEN',
        'DHL_ENDPOINT_SHIPMENT',
        'DHL_ENDPOINT_LABEL',
    ];

    public function __construct(array $config)
    {
        $this->validateConfiguration($config);
        $this->configure($config);
        $this->authenticate();
    }

    private function configure(array $config): void
    {
        $devSuffix = !$config['DHL_ENV'] ? '_DEV' : '';

        $this->accountId          = $config['DHL_ACCOUNID'];
        $this->userId            = $config['DHL_USERID' . $devSuffix];
        $this->apiKey            = $config['DHL_KEY' . $devSuffix];
        $this->baseUrl           = $config['DHL_URL' . $devSuffix];
        $this->endpointLogin     = $config['DHL_ENDPOINT_LOGIN'];
        $this->endpointRefreshToken = $config['DHL_ENDPOINT_REFRESH_TOKEN'];
        $this->endpointShipments = $config['DHL_ENDPOINT_SHIPMENT'];
        $this->endpointLabels    = $config['DHL_ENDPOINT_LABEL'];
    }

    private function validateConfiguration(array $config): void
    {
        $missing = [];

        foreach ($this->requiredKeys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            $keysStr = implode(', ', $missing);
            throw new \InvalidArgumentException(
                'Faltan datos de configuración obligatorios: ' . $keysStr
            );
        }
    }


    /**
     * Maneja la autenticación inicial o el refresco del token si ya existe.
     */
    private function authenticate(): void
    {
        if ($this->loadTokensFromCookies()) {
            return;
        }

        $this->login();
    }

    private function login(): void
    {
        $body = [
            'userId' => $this->userId,
            'key'    => $this->apiKey
        ];

        $response = $this->request('POST', $this->endpointLogin, json_encode($body));
        if ($response) {
            $this->setCookies($response);
        }
    }

    /**
     * Intenta cargar el token desde las cookies.
     *
     * @return bool True si se encontró un token válido.
     */
    private function loadTokensFromCookies(): bool
    {
        // Lógica para ver si existe el token en cookie
        if (isset($_COOKIE['access_token_dhl'])) {
            $this->accessToken = $_COOKIE['access_token_dhl'];
            return true;
        } elseif (isset($_COOKIE['refresh_token_dhl'])) {
            // Intentar refrescar el token
            $this->refreshToken = $_COOKIE['refresh_token_dhl'];
            return $this->refreshAccessToken();
        }
        return false;
    }

     /**
     * Intenta refrescar el access token usando el refresh token.
     *
     * @return bool True si se refrescó el token correctamente.
     */
    private function refreshAccessToken(): bool
    {
        if (!$this->refreshToken) {
            return false;
        }

        $body = json_encode(['refresh_token_dhl' => $this->refreshToken]);
        $response = $this->request('POST', $this->endpointRefreshToken, $body);

        if (!$response) {
            return false;
        }

        $this->setCookies($response);
        return true;
    }

    /**
     * Establece las cookies con los tokens obtenidos.
     *
     * @param object $data
     */
    private function setCookies($data): void
    {
        if (isset($data->accessToken)) {
            $this->accessToken = $data->accessToken;
            setcookie(
                'access_token_dhl',
                $data->accessToken,
                $data->accessTokenExpiration ?? 0
            );
        }

        if (isset($data->refreshToken)) {
            $this->refreshToken = $data->refreshToken;
            setcookie(
                'refresh_token_dhl',
                $data->refreshToken,
                $data->refreshTokenExpiration ?? 0
            );
        }
    }

    /**
     * Envía una solicitud para crear un envío.
     *
     * @param array $shipment
     * @return object|null Respuesta de la API o null en caso de error.
     */
    public function postShipment($shipment)
    {
        $body = $this->getBodyShipment($shipment);
        return $this->request('POST', $this->endpointShipments, $body);
    }

    /**
     * Prepara el cuerpo del envío.
     *
     * @param array $info_shipment
     * @return string JSON codificado.
     */
    public function getBodyShipment($info_shipment)
    {
        $num_shipment = $info_shipment['num_shipment'];
        $info_receiver = $info_shipment['info_customer'];
        $info_shipper = $info_shipment['info_shop'];
        $info_package = $info_shipment['info_package'];
        $info_receiver['referenceClient'] = $info_package['message'];

        $receiver = $this->getReceiver($info_receiver);
        $shipper = $this->getShipper($info_shipper);
        $pieces = $this->getPieces($info_package);

        $options[] = [
            "key"   => "REFERENCE",
            "input" => (string)$info_shipment['id_order'],
        ];

        if($info_package['cash_ondelivery'] > 0){
            $options[] = [
                "key"   => "COD_CASH",
                "input" => $info_package['cash_ondelivery']
            ];
        }

        $data = [
            "shipmentId" => $num_shipment,
            "orderReference" => (string)$info_shipment['id_order'],
            "receiver" => $receiver,
            "shipper" => $shipper,
            "accountId" =>  $this->accountId,
            "options" => $options,
            "returnLabel" => false,
            "pieces" => $pieces
        ];

        return json_encode($data);
    }

    /**
     * Prepara los datos de las piezas del envío.
     *
     * @param array $info
     * @return array
     */
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
     * Prepara el formato del receptor.
     *
     * @param array $info
     * @return array
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
                "firstName"      => $info['firstname'],
                "lastName"       => $info['lastname'],
                "companyName"    => $info['company'],
                "additionalName" => $info['firstname']
            ],
            "address"=> [
                "countryCode"           => $info['countrycode'],
                "postalCode"            => $info['postcode'],
                "city"                  => $info['city'],
                "street"                => $info['address1'],
                "additionalAddressLine" => $info['address2'],
                "number"                => '',
                "isBusiness"            => ($info['company'])?true:false,
                "addition"              => $info['other']
            ],
            "email"       => $info['email'],
            "phoneNumber" => $phone,
            "vatNumber"   => $info['vat_number'],
            "eoriNumber"  => $info['dni'],
            "reference"   => $info['referenceClient']
        ];
    }

    /**
     * Prepara el formato del remitente.
     *
     * @param array $info
     * @return array
     */
    public function getShipper($info)
    {
        $countryCode = Country::getIsoById($info['id_country']);
        return [
            "name" => [
                "firstName"=> $info['firstname'],
                "lastName"=> $info['lastname'],
                "companyName"=> $info['company'],
                "additionalName"=> $info['additionalname']
            ],
            "address"=> [
                "countryCode"=> $countryCode,
                "postalCode"=> $info['postcode'],
                "city"=> $info['state'],
                "street"=> $info['street'] . ' ' . $info['city'],
                "additionalAddressLine"=> $info['additionaladdress'],
                "number"=> $info['number'],
                "isBusiness"=> !empty($info['company']),
                "addition"=> ''
            ],
            "email"=> $info['email'],
            "phoneNumber"=> $info['phone'],
            "vatNumber"=> $info['vatnumber'],
            "eoriNumber"=> ''
        ];
    }

    /**
     * Obtiene la etiqueta del envío.
     *
     * @param string $id_label
     * @return object|null
     */
    public function getLabel($id_label)
    {
        $endpointLabel = $this->endpointLabels . '/' . $id_label;
        return $this->request('GET', $endpointLabel);
    }

    /**
     * Realiza la solicitud a la API de DHL.
     *
     * @param string      $method   Método HTTP (GET, POST, etc.)
     * @param string      $endpoint Endpoint de la API.
     * @param string|null $body     Cuerpo de la solicitud en JSON.
     *
     * @return object|null Respuesta decodificada o null en caso de error.
     * @throws \RuntimeException Si no se puede obtener un access token.
     */
    private function request($method, string $endpoint, $body = null)
    {
        if (!$this->accessToken) {
            if (!$this->refreshAccessToken()) {
                throw new \RuntimeException('No access token available and refresh failed');
            }
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => $method
        ]);

        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $rawResponse = curl_exec($ch);
        $error       = curl_errno($ch);
        $info        = curl_getinfo($ch);
        curl_close($ch);

        if ($error || !in_array($info['http_code'], [200, 201])) {
            CarrierDhl::saveLog($url, 0, $body, $rawResponse);
            return null;
        }

        return json_decode($rawResponse);
    }
}
