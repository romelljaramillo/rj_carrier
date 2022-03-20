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

namespace Roanja\Module\RjCarrier\Carrier\Cex;

use Roanja\Module\RjCarrier\Model\RjcarrierTypeShipment;

use Configuration;
use Shop;
use Country;

Class ServiceCex {

    public function __construct()
    {
        $this->id_shop_group = Shop::getContextShopGroupID();
		$this->id_shop = Shop::getContextShopID();
    }

    private function getUserCredentials()
    {
        return [
            'user'  => Configuration::get('RJ_CEX_USER', null, $this->id_shop_group, $this->id_shop),
            'password' => Configuration::get('RJ_CEX_PASS', null, $this->id_shop_group, $this->id_shop)
        ];
    }

    public function rellenarCeros($valor, $longitud)
    {
        $res = str_pad($valor, $longitud, '0', STR_PAD_LEFT);
        return $res;
    }

    public function transformCodeClient($cod_client)
    {
        return 'P' . $cod_client;
    }

    public function getBodyShipment($info_shipment)
    {
        $info_config = $info_shipment['info_config'];

        $data = [
            "solicitante"   => $this->transformCodeClient($info_config['RJ_CEX_COD_CLIENT']),
            "canalEntrada"  => "",
            "numEnvio"      => "",
            "ref"           => (string)$info_shipment['id_order'],
            "refCliente"    => (string)$info_shipment['id_order'],
            "fecha"         => date("dmY"),
            "codRte"        => $info_config['RJ_CEX_COD_CLIENT']
        ];

        $shipper = $this->getShipper($info_shipment['info_shop']);
        $receiver = $this->getReceiver($info_shipment['info_customer']);
        $pieces = $this->getPieces($info_shipment['info_package']);
        $informacionAdicional["listaInformacionAdicional"][] = $this->obtenerListaAdicional($info_shipment);

        $data = array_merge($data, $shipper, $receiver, $pieces, $informacionAdicional);

        return json_encode($data);
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

        // rellenamos los codigos postales con 0 a la izquierda en caso de ser necesario
        if ($countrycode == 'ES') {
            $codPosNacRte = $this->rellenarCeros($info['postcode'], 5);
            $codPosIntRte = '';
        } else {
            $codPosNacRte = '';
            $codPosIntRte = $info['postcode'];
        }

        return [
            "nomRte" => $info['firstname'] . ' ' . $info['lastname'],
            "nifRte" => $info['vatnumber'],
            "dirRte" => $info['street'] . ' ' . $info['city'],
            "pobRte" => $info['city'],
            "codPosNacRte" => $codPosNacRte,
            "paisISORte" => $countrycode,
            "codPosIntRte" => $codPosIntRte,
            "contacRte" => $info['firstname'] . ' ' . $info['lastname'],
            "telefRte" => $info['phone'],
            "emailRte" => $info['email']
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

        if ($info['countrycode'] == 'ES') {
            $codPosNacDest = $this->rellenarCeros($info['postcode'], 5);
            $codPosIntDest = '';
        } else {
            $codPosNacDest = '';
            $codPosIntDest = $info['postcode'];
        }

        return [
            "codDest"       => "",
            "nomDest"       => $info['firstname'] . ' ' . $info['lastname'],
            "nifDest"       => $info['vat_number'],
            "dirDest"       => $info['address1'],
            "pobDest"       => $info['city'],
            "codPosNacDest" => $codPosNacDest,
            "paisISODest"   => $info['countrycode'],
            "codPosIntDest" => $codPosIntDest,
            "contacDest"    => $info['firstname'] . ' ' . $info['lastname'],
            "telefDest"     => $phone,
            "emailDest"     => $info['email'],
            "contacOtrs"    => "",
            "telefOtrs"     => $info['phone'],
            "emailOtrs"     => "",
        ];
    }

    public function getPieces($info)
    {
        $weight = (string)$info['weight'] / (float)$info['quantity'];
        
        //Lista adicional de bultos
        for ($i = 1; $i <= $info['quantity']; $i++) {
            $interior = new \stdClass();
            $interior->alto = "";
            $interior->ancho = "";
            $interior->codBultoCli = $i;
            $interior->codUnico = "";
            $interior->descripcion = "";
            $interior->kilos = "";
            $interior->largo = "";
            $interior->observaciones = "";
            $interior->orden = $i;
            $interior->referencia = "";
            $interior->volumen = "";
            $list_packages[] = $interior;
        }

        $type_shipment = new RjcarrierTypeShipment((int)$info['id_type_shipment']);
        
        $reembolso = "";
        if(doubleval($info['cash_ondelivery'])){
            $reembolso = (string)round($info['cash_ondelivery'],2);
        }

        return [
            "observac"      => $info['message'],
            "numBultos"     => (string)$info['quantity'],
            "kilos"         => (string)round($weight,2),
            "volumen"       => "",
            "alto"          => (string)round($info['height'],2),
            "largo"         => (string)round($info['length'],2),
            "ancho"         => (string)round($info['width'],2),
            "producto"      => (string)$type_shipment->id_bc,
            "portes"        => "P",
            "reembolso"     => $reembolso,
            "entrSabado"    => ($info['deliver_sat'])?"S":"",
            "seguro"        => (string)$info['insured_value'],
            "numEnvioVuelta" => "",
            "listaBultos"   => $list_packages,
            "codDirecDestino" => ($info['delivery_office'])?$info['cod_office']:"",
            "password"      => "",
        ];

    }

    public function obtenerListaAdicional($info_shipment, $esMasiva=false)
    {
        $fecha = date("d-m-Y");

        $iso_lang = \Context::getContext()->language->iso_code;
        $lang = $this->obtenerIdioma($iso_lang);

        $config_extra_info = $info_shipment['config_extra_info'];
        $lista = new \stdClass(); 

        if($esMasiva==true){
            $lista->tipoEtiqueta = "";
            $lista->posicionEtiqueta = "";
            $lista->hideSender = $config_extra_info['RJ_LABELSENDER'];
            $lista->codificacionUnicaB64 = "1";
            $lista->logoCliente = '';
            $lista->idioma=$lang;
            $lista->textoRemiAlternativo = ($config_extra_info['RJ_LABELSENDER'] == '1')? $config_extra_info['RJ_LABELSENDER'] : '';
            $lista->etiquetaPDF =  "";
            $lista->creaRecogida = 'N';
           
        }else{
            switch ($info_shipment['tipoEtiqueta']) {
            //ETIQUETA ADHESIVA
                case '1':
                $lista->tipoEtiqueta = "3";
                $lista->posicionEtiqueta = "0";
                break;
            //ETIQUETA MEDIO FOLIO
                case '2':
                $lista->tipoEtiqueta = "4";
                $lista->posicionEtiqueta = "0";
                break;
            //ETIQUETA TERMICA
                case '3':
                $lista->tipoEtiqueta = "5";
                break;
                
                default:
                $lista->tipoEtiqueta = "5";
                break;
            }

            $lista->hideSender = $config_extra_info['RJ_LABELSENDER'];
            $lista->codificacionUnicaB64 = "1";
            $lista->logoCliente = '';
            $lista->idioma= $lang;
            $lista->textoRemiAlternativo = ($config_extra_info['RJ_LABELSENDER'] == '1')? $config_extra_info['RJ_LABELSENDER'] : '';
            $lista->etiquetaPDF =  "";
            if(strcmp('true', $info_shipment['grabar_recogida'])==0){
                $lista->creaRecogida = 'S';
                $lista->fechaRecogida = $fecha;
                $lista->horaDesdeRecogida = $info_shipment['fromHH_sender'].':'.$info_shipment['fromMM_sender'];
                $lista->horaHastaRecogida = $info_shipment['toHH_sender'].':'.$info_shipment['toMM_sender'];
                $lista->referenciaRecogida = $info_shipment['id'];
            }else{
                $lista->creaRecogida  = 'N';
            }
        }        
        return $lista;
    }

    public function obtenerIdioma($iso_lang)
    {
        if (strcmp($iso_lang, 'pt') == 0) {
            return 'PT';
        }

        return 'ES';
    }

    /**
     * Undocumented function
     *
     * @param array $body
     * @return obj
     */
    public function postShipment($url_ws, $body_shipment)
    {
        return $this->request('POST', $url_ws, $body_shipment);
    }

    private function headerRequest($body)
    {
        return [
            'Content-Type: application/json',
            'Accept:application/json',
            'Content-Length: ' . \Tools::strlen($body)
        ];
    }

    /* public function request($method, $url, $body = null)
    {
 
        $credenciales   = $this->getUserCredentials();
        $header = $this->headerRequest($body);
        
        $ch = curl_init();

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_SSL_VERIFYHOST  => false,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0)',
                CURLOPT_URL             => $url ,
                CURLOPT_USERPWD         => $credenciales['user'].":".$credenciales['password'],
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_POSTFIELDS      => mb_convert_encoding($body, mb_detect_encoding($body), "UTF-8"),
                CURLOPT_HTTPHEADER      => $header,
            )
        );

        $response = utf8_encode(curl_exec($ch));

        $error = curl_error($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // get status code
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
    } */

    // pruebas

    public function request($method, $url, $body = null)
    {
        $response = '{
            "codigoRetorno": 0,
            "mensajeRetorno": "",
            "datosResultado": "3230004958671121",
            "listaBultos": [
                {
                    "orden": "01",
                    "codUnico": "32300049586711201150075"
                },
                {
                    "orden": "02",
                    "codUnico": "32300049586711202150074"
                }
            ],
            "etiqueta": [
                {
                    "etiqueta1": "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IDI3NyAwIDY2NiA1NTYgODMzIDcyMiA3NzcgNjY2IDc3NyA3MjIgNjY2IDYxMCA3MjIgNjY2IDAgNjY2IDAgNjEwIDAgMCAwIDAgMCAwIDAgMCAwIDU1NiA1NTYgMCA1NTYgMCAwIDAgMCAwIDAgNTU2IDU1NiAwIDAgMzMzIDUwMCAyNzcgMCA1MDAgMCAwIDAgMCAwIDAgMCAwIDAgNTU2IDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCA3MjIgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMjc3XS9Gb250RGVzY3JpcHRvciAxMiAwIFI+PgplbmRvYmoKMTMgMCBvYmoKPDwvVHlwZS9Gb250RGVzY3JpcHRvci9Bc2NlbnQgNjEyL0NhcEhlaWdodCA2OTkvRGVzY2VudCAtMTg4L0ZvbnRCQm94Wy0yMSAtNjc5IDYzNyAxMDIwXS9Gb250TmFtZS9Db3VyaWVyTmV3UFNNVC9JdGFsaWNBbmdsZSAwL1N0ZW1WIDgwL0ZsYWdzIDMzPj4KZW5kb2JqCjMgMCBvYmoKPDwvVHlwZS9Gb250L1N1YnR5cGUvVHJ1ZVR5cGUvQmFzZUZvbnQvQ291cmllck5ld1BTTVQvRW5jb2RpbmcvV2luQW5zaUVuY29kaW5nL0ZpcnN0Q2hhciAzMi9MYXN0Q2hhciAyMDkvV2lkdGhzWzYwMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgNjAwIDYwMCA2MDAgMCA2MDAgNjAwIDYwMCA2MDAgNjAwIDYwMCA2MDAgNjAwIDYwMCAwIDYwMCAwIDAgMCAwIDAgMCA2MDAgNjAwIDYwMCA2MDAgNjAwIDYwMCAwIDAgNjAwIDYwMCA2MDAgNjAwIDYwMCA2MDAgNjAwIDYwMCA2MDAgNjAwIDYwMCA2MDAgNjAwIDYwMCAwIDAgMCA2MDAgMCAwIDAgMCAwIDAg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"
                }
            ],
            "numRecogida": null,
            "fechaRecogida": null,
            "horaRecogidaDesde": null,
            "horaRecogidaHasta": null,
            "direccionRecogida": null,
            "poblacionRecogida": null
        }';

        return json_decode($response);
    }

}