<?php

Class ServiceCex {

    protected $urlShipments = '/GrabacionEnvio?wsdl';
    protected $urlRecogida = '/GrabacionRecogida?wsdl';
    protected $urlSeguimiento = '/SeguimientoEnvio?wsdl';

    public function __construct()
    {
        $this->id_shop_group = Shop::getContextShopGroupID();
		$this->id_shop = Shop::getContextShopID();
    }

    public function obtenerProductosCex($id_order = 0)
    {
        /*
        Compruebo desde donde recibo la peticion
            Pedido
            Utilidades
        */
        if (Tools::getValue("id_customer_code")) {
            $id_customer_code   = Tools::getValue("id_customer_code");
        } else {
            
            $sql                = "SELECT cod.id FROM "._DB_PREFIX_."orders o
                                    LEFT JOIN "._DB_PREFIX_."cex_customer_codes cod 
                                        ON cod.id_shop = o.id_shop 
                                            AND cod.id_shop_group = o.id_shop_group
                                    WHERE o.id_order = $id_order";
            $id_customer_code   = Db::getInstance()->getValue($sql);
        }

        $update         = false;
        $contenido      = false;
        
        $sql            = "SELECT checked FROM "._DB_PREFIX_."cex_savedmodeships
                            WHERE id_customer_code = $id_customer_code AND id_bc='63'";
        $results        = Db::getInstance()->getValue($sql);

        /*
            En caso de que ya este checkado el producto
        */
        if (strcmp($results, "1") == 0) {
            $contenido  = true;
        } else {
            $sql        = "UPDATE "._DB_PREFIX_."cex_savedmodeships SET checked = 1
                            WHERE id_customer_code = $id_customer_code AND id_bc='63'";
            $results    = Db::getInstance()->Execute($sql);

            if ($results) {
                $update = true;
            }
        }
        $retorno = array(
            'contenido' => $contenido,
            'update'    => $update,
        );

        /*
            Tratamiento del metodo de retorno diferente si se
            pide la informacion desde utilidades o desde el pedido
         */
        if (Tools::getValue("id_customer_code")) {
            echo json_encode($retorno);
            return;
        } else {
            return $retorno;
        }
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
            $interior = new stdClass();
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

        return [
            "observac"      => $info['message'],
            "numBultos"     => (string)$info['quantity'],
            "kilos"         => (string)round($weight,2),
            "volumen"       => "",
            "alto"          => (string)round($info['height'],2),
            "largo"         => (string)round($info['length'],2),
            "ancho"         => (string)round($info['width'],2),
            "producto"      => (string)$info['type_delivery'],
            "portes"        => "P",
            "reembolso"     => (string)round($info['cash_ondelivery'],2),
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

        $iso_lang = Context::getContext()->language->iso_code;
        $lang = $this->obtenerIdioma($iso_lang);

        $config_extra_info = $info_shipment['config_extra_info'];
        $lista = new stdClass(); 

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
    public function postShipment($body_shipment)
    {
        $info_config = $body_shipment['info_config'];
        $url_ws = $info_config['RJ_CEX_WSURL'];
        $url = $url_ws . $this->urlShipments;
        // $body = $this->generateBodyShipment($info_shipment);
        // $body = json_encode($info_shipment);
        // $resp = $this->request('POST', $url, $body);
        // return $resp;
    }

    private function headerRequest($url)
    {
        return [
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"".$url."\""
        ];
    }

    public function request($method, $url, $body = null)
    {
 
        $credenciales   = $this->getUserCredentials();
            
        // iniciamos y componemos la peticion curl
        $header = $this->headerRequest($url);

        $ch = curl_init();
        $options = array(
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_SSL_VERIFYHOST  => false,
                    CURLOPT_SSL_VERIFYPEER  => false,
                    CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0)',
                    CURLOPT_URL             => $url ,
                    CURLOPT_USERPWD         => $credenciales['user'].":".$credenciales['password'],
                    CURLOPT_CUSTOMREQUEST  => $method,
                    CURLOPT_POSTFIELDS      => mb_convert_encoding($body, mb_detect_encoding($body), "UTF-8"),
                    CURLOPT_HTTPHEADER      => $header,
                );

        curl_setopt_array($ch, $options);

        $output         = curl_exec($ch);
        $codigo_error   = curl_errno($ch);
        $error          = curl_error($ch);

        switch ($codigo_error) {
            case 0:
                break;
            case 7:
                $output = "<?xml version='1.0' encoding='UTF-8'?>
                            <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'>
                                <soapenv:Body>
                                    <ns3:grabarEnvioResponse xmlns:ns3='messages.envios.ws.chx.es'>
                                        <ns3:return xmlns:ns2='http://pojo.envios.ws.chx.es/xsd' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:type='ns2:RetornoGrabacionEnvio'>
                                            <s634:codigoRetorno xmlns:s634='http://ws.chx.es/xsd'>7</s634:codigoRetorno>
                                            <s635:mensajeRetorno xmlns:s635='http://ws.chx.es/xsd'>WebService Temporalmente no accesible</s635:mensajeRetorno>
                                            <ns2:datosResultado />
                                            <ns2:resultado />
                                        </ns3:return>
                                    </ns3:grabarEnvioResponse>
                                </soapenv:Body>
                            </soapenv:Envelope>";
                break;
        }
        curl_close($ch);

        return $output;
    }

}