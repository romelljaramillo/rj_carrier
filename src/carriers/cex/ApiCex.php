<?php

Class ApiCex {

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

    public function generateBodyShipmentSoap($info_shipment)
    {
        $fecha          = date("d-m-Y");

        $info_shop = $info_shipment['info_shop'];
        $info_customer = $info_shipment['info_customer'];
        $info_package = $info_shipment['info_package'];
        $info_config = $info_shipment['info_config'];

        $countryCode = Country::getIsoById($info_customer['id_country']);
        $info_customer['countrycode'] = $countryCode;

        // rellenamos los codigos postales con 0 a la izquierda en caso de ser necesario
        if ($info_shipment['countrycode'] == 'ES') {
            $info_shop_postcode = rellenarCeros($info_shop['postcode'], 5);
        } else {
            $info_shop_postcode = $info_shop['postcode'];
        }

        $soap_request   = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mes="messages.envios.ws.chx.es" xmlns:xsd="http://pojo.envios.ws.chx.es/xsd">
        <soapenv:Header/>
        <soapenv:Body>';

        if ($info_shipment['entrega_oficina']=='true') {
            $soap_request .= '<mes:grabarEnvioEntregaOficina>';
        } else {
            $soap_request .= '<mes:grabarEnvio>';
        }
        
        $soap_request .='<mes:solicitante>'.$info_shipment['codigo_solicitante'].'</mes:solicitante>
        <mes:canalEntrada></mes:canalEntrada>
        <mes:ref>'.$info_shipment['ref_ship'].'</mes:ref>
        <mes:refCli></mes:refCli>
        <mes:fecha>'.$fecha.'</mes:fecha>
        <mes:codRte>'.$info_shipment['codigo_cliente'].'</mes:codRte>
        <mes:nomRte>'.$info_shipment['name_sender'].'</mes:nomRte>
        <mes:dirRte>'.$info_shipment['address_sender'].'</mes:dirRte>
        <mes:pobRte>'.$info_shipment['city_sender'].'</mes:pobRte>';

        if ($info_shipment['iso_code_remitente'] == 'ES') {
            // envios a portugal
            $soap_request .= '<mes:codPosNacRte>'.$info_shop_postcode.'</mes:codPosNacRte>';
            $soap_request .= '<mes:paisISORte>'.$info_shipment['iso_code_remitente'].'</mes:paisISORte>';
            $soap_request .= '<mes:codPosIntRte></mes:codPosIntRte>';
        } elseif ($info_shipment['iso_code_remitente'] == 'PT') {
            // envios a portugal
            $soap_request .= '<mes:codPosNacRte></mes:codPosNacRte>';
            $soap_request .= '<mes:paisISORte>'.$info_shipment['iso_code_remitente'].'</mes:paisISORte>';
            $soap_request .= '<mes:codPosIntRte>'.$info_shop_postcode.'</mes:codPosIntRte>';
        } else {
            // internacionales
            $soap_request .= '<mes:codPosIntRte>'.$info_shop_postcode.'</mes:codPosIntRte>';
            $soap_request .= '<mes:paisISORte>'.$info_shipment['iso_code_remitente'].'</mes:paisISORte>';
        }

        $soap_request .= '<mes:contacRte>'.$info_shipment['contact_sender'].'</mes:contacRte>
        <mes:telefRte>'.$info_shipment['phone_sender'].'</mes:telefRte>
        <mes:emailRte>'.$info_shipment['email_sender'].'</mes:emailRte>
        <mes:nomDest>'.$info_shipment['name_receiver'].'</mes:nomDest>
        <mes:dirDest>'.$info_shipment['address_receiver'].'</mes:dirDest>
        <mes:pobDest>'.$info_shipment['city_receiver'].'</mes:pobDest>';

        // envios a españa
        if ($info_customer['iso_code'] == 'ES') {
            $soap_request .= '<mes:codPosNacDest>'.rellenarCeros($info_customer['postcode'], 5).'</mes:codPosNacDest>';
            $soap_request .= '<mes:paisISODest>'.$info_customer['countrycode'].'</mes:paisISODest>';
            $soap_request .= '<mes:codPosIntDest></mes:codPosIntDest>';
            // envios a portugal
        } elseif ($info_customer['iso_code'] == 'PT') {
            $soap_request .= '<mes:codPosNacDest></mes:codPosNacDest>';
            $soap_request .= '<mes:paisISODest>'.$info_customer['countrycode'].'</mes:paisISODest>';
            $soap_request .= '<mes:codPosIntDest>'.rellenarCeros($info_customer['postcode'], 4).'</mes:codPosIntDest>';
        } else {
            $soap_request .= '<mes:codPosNacDest></mes:codPosNacDest>';
            $soap_request .= '<mes:paisISODest>'.$info_customer['countrycode'].'</mes:paisISODest>';
            $soap_request .= '<mes:codPosIntDest>'.$info_customer['postcode'].'</mes:codPosIntDest>';
        }

        $soap_request .= '<mes:contacDest>'.$info_shipment['contact_receiver'].'</mes:contacDest>
        <mes:telefDest>'.$info_shipment['phone_receiver1'].'</mes:telefDest>
        <mes:emailDest>'.$info_shipment['email_receiver'].'</mes:emailDest>
        <mes:telefOtrs>'.$info_shipment['phone_receiver2'].'</mes:telefOtrs>
        <mes:observac>'.$info_shipment['note_deliver'].'</mes:observac>
        <mes:numBultos>'.$info_shipment['bultos'].'</mes:numBultos>
        <mes:kilos>'.retornarPesoPedido($info_shipment['id'], $info_shipment['kilos']).'</mes:kilos>
        <mes:producto>'.$info_shipment['selCarrier'].'</mes:producto>
        <mes:portes>P</mes:portes>';

        if (!empty($info_shipment['payback_val'])) {
            $soap_request .= '<mes:reembolso>'.$info_shipment['payback_val'].'</mes:reembolso>';
        }

        if ($info_shipment['deliver_sat']=='true') {
            $soap_request .= '<mes:entrSabado>S</mes:entrSabado>';
        }

        // Valor Asegurado
        $soap_request .='<mes:seguro>'.$info_shipment['insured_value'].'</mes:seguro>';

        for ($i=1; $i<=$info_shipment['bultos']; $i++) {
            $soap_request .= '<mes:listaBultos>
                <xsd:alto></xsd:alto>
                <xsd:ancho></xsd:ancho>
                <xsd:codBultoCli>'.$i.'</xsd:codBultoCli>
                <xsd:codUnico></xsd:codUnico>
                <xsd:descripcion></xsd:descripcion>
                <xsd:kilos></xsd:kilos>
                <xsd:largo></xsd:largo>
                <xsd:observaciones></xsd:observaciones>
                <xsd:orden>'.$i.'</xsd:orden>
                <xsd:referencia></xsd:referencia>
                <xsd:volumen></xsd:volumen>
                </mes:listaBultos>';
        }

        if ($info_shipment['entrega_oficina']=='true') {
            $soap_request .= '<mes:coddirecDestino>'.$info_shipment['codigo_oficina'].'</mes:coddirecDestino>';
            $soap_request .= '</mes:grabarEnvioEntregaOficina></soapenv:Body></soapenv:Envelope>';
        } else {
            $soap_request .= '</mes:grabarEnvio></soapenv:Body></soapenv:Envelope>';
        }

        $soap_request   =  $this->sanearString($soap_request);
        return $soap_request;
    }

    private function generateBodyShipment($info_shipment)
    {

        $fecha          = date("dmY");

        $info_shop = $info_shipment['info_shop'];
        $info_customer = $info_shipment['info_customer'];
        $info_package = $info_shipment['info_package'];
        $info_config = $info_shipment['info_config'];


        // rellenamos los codigos postales con 0 a la izquierda en caso de ser necesario
        $countryCode = Country::getIsoById($info_customer['id_country']);
        $info_customer['countrycode'] = $countryCode;

        // rellenamos los codigos postales con 0 a la izquierda en caso de ser necesario
        if ($info_shop['countrycode'] == 'ES') {
            $info_shop_postcode = rellenarCeros($info_shop['postcode'], 5);
        } else {
            $info_shop_postcode = $info_shop['postcode'];
        }

        $postcode_receiver  = '';

        $data = array(
            "solicitante"   => $info_config['RJ_CEX_COD_CLIENT'],
            "canalEntrada"  => "",
            "numEnvio"      => "",
            "ref"           => $info_shipment['order_id'],
            "refCliente"    => $info_shipment['order_id'],
            "fecha"         => $fecha,
            "codRte"        => $info_config['RJ_CEX_COD_CLIENT'],
            "nomRte"        => $info_shop['firstname'] . ' ' . $info_shop['lastname'],
            "nifRte"        => $info_shop['vatnumber'],
            "dirRte"        => $info_shop['street'] . ' ' . $info_shop['number'] . ' ' . $info_shop['state'],
            "pobRte"        => $info_shop['city'],
            "codPosNacRte"  => $info_shop_postcode,
            "paisISORte"    => $info_shop['countrycode'],
            "codPosIntRte"  => "",
            "contacRte"     => $info_shop['firstname'],
            "telefRte"      => $info_shop['phone'],
            "emailRte"      => $info_shop['email'],
            "codDest"       => "",
            "nomDest"       => $info_customer['firstname'] . ' ' . $info_customer['lastname'],
            "nifDest"       => $info_customer['vat_number'],
            "dirDest"       => $info_customer['address1'],
            "pobDest"       => $info_customer['city'],
            "codPosNacDest" => $info_customer['postcode'],
            "paisISODest"   => $info_customer['countrycode'],
            "codPosIntDest" => "",
            "contacDest"    => $info_customer['lastname'],
            "telefDest"     => $info_customer['phone_mobile'],
            "emailDest"     => $info_customer['email'],
            "contacOtrs"    => "",
            "telefOtrs"     => $info_customer['phone'],
            "emailOtrs"     => "",
            "observac"      => $info_package['other'],
            "numBultos"     => $info_package['packages'],
            "kilos"         => $info_package['weight'],
            "volumen"       => "",
            "alto"          => $info_package['height'],
            "largo"         => $info_package['length'],
            "ancho"         => $info_package['width'],
            "producto"      => $info_package['type_delivery'],
            "portes"        => "P",
            "reembolso"     => "",
            "entrSabado"    => "",
            "seguro"        => $info_package['insured_value'],
            "numEnvioVuelta" => "",
            "listaBultos"   => [],
            "codDirecDestino" => "",
            "password"      => "",
            "listaInformacionAdicional" => []
        );

        //CP e iso_code Remitentes
        if ($info_shop['countrycode'] == 'ES') {
            $data['codPosNacRte'] = $info_shop_postcode;
            $data['paisISORte'] = $info_shop['countrycode'];
            $data['codPosIntRte'] = "";
        } elseif ($info_shop['countrycode'] == 'PT') {
            // envios a portugal
            $data['codPosNacRte'] = "";
            $data['paisISORte'] = $info_shop['countrycode'];
            $data['codPosIntRte'] = $info_shop_postcode;
        } else {
            // internacionales
            $data['codPosNacRte'] = "";
            $data['paisISORte'] = $info_shop['countrycode'];
            $data['codPosIntRte'] = $info_shop_postcode;
        }
        //CP e iso_code Destinatarios
        if ($info_customer['countrycode'] == 'ES') {
            $data['codPosNacDest'] = $info_customer['postcode'];
            $data['paisISODest'] = $info_customer['countrycode'];
            $data['codPosIntDest'] = "";
        } elseif ($info_customer['countrycode'] == 'PT') {
            // envios a portugal
            $data['codPosNacDest'] = "";
            $data['paisISODest'] = $info_customer['countrycode'];
            $data['codPosIntDest'] = $info_customer['postcode'];
        } else {
            // internacionales
            $data['codPosNacDest'] = "";
            $data['paisISODest'] = $info_customer['countrycode'];
            $data['codPosIntDest'] = $info_customer['postcode'];
        }

        if ($info_package['price_contrareembolso'] > 0) {
            $data['reembolso'] = $info_package['price_contrareembolso'];
        }
        if ($info_package['deliver_sat'] == 'true') {
            $data['entrSabado'] = 'S';
        }

        //if recogida en oficina
        if ($info_package['delivery_office'] == 'true') {
            $data['codDirecDestino'] = $info_package['cod_office'];
        }


        //Lista adicional de bultos
        for ($i = 1; $i <= $info_package['packages']; $i++) {
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
            $data["listaBultos"][] = $interior;
        }

        $data["listaInformacionAdicional"][] = $this->obtenerListaAdicional($info_shipment);

        return json_encode($data);
    }

    public function obtenerListaAdicional($datos, $esMasiva=false){
        $fecha          = $datos['datepicker'];
        $fechaformat    = explode('-', $fecha);
        $lista = new stdClass(); 
        $valorHideSender=$this->valorHideSender();
        if($esMasiva==true){
            $lista->tipoEtiqueta = "";
            $lista->posicionEtiqueta = "";
            $lista->hideSender = $valorHideSender['hideSender'];
            $lista->codificacionUnicaB64 = "1";
            $lista->logoCliente = codificarLogo();
            $lista->idioma=$this->obtenerIdioma($datos);
            $lista->textoRemiAlternativo = $valorHideSender['textoRemiAlternativo'];
            $lista->etiquetaPDF =  "";
            $lista->creaRecogida = 'N';
           
        }else{
            switch ($datos['tipoEtiqueta']) {
            //ETIQUETA ADHESIVA
                case '1':
                $lista->tipoEtiqueta = "3";
                $lista->posicionEtiqueta = $this->obtenerPosicionEtiqueta($datos['posicionEtiqueta']);
                break;
            //ETIQUETA MEDIO FOLIO
                case '2':
                $lista->tipoEtiqueta = "4";
                $lista->posicionEtiqueta = $this->obtenerPosicionEtiqueta($datos['posicionEtiqueta']);
                break;
            //ETIQUETA TERMICA
                case '3':
                $lista->tipoEtiqueta = "5";
                break;
                
                default:
                $lista->tipoEtiqueta = "5";
                break;
            }

            $lista->hideSender = $valorHideSender['hideSender'];
            $lista->codificacionUnicaB64 = "1";
            $lista->logoCliente = codificarLogo();
            $lista->idioma= $this->obtenerIdioma($datos);
            $lista->textoRemiAlternativo = $valorHideSender['textoRemiAlternativo'];
            $lista->etiquetaPDF =  "";
            if(strcmp('true', $datos['grabar_recogida'])==0){
                $lista->creaRecogida = 'S';
                $lista->fechaRecogida = $fechaformat[2].$fechaformat[1].$fechaformat[0];
                $lista->horaDesdeRecogida = $datos['fromHH_sender'].':'.$datos['fromMM_sender'];
                $lista->horaHastaRecogida = $datos['toHH_sender'].':'.$datos['toMM_sender'];
                $lista->referenciaRecogida = $datos['id'];
            }else{
                $lista->creaRecogida  = 'N';
            }
        }        
        return $lista;
    }

    /**
     * Undocumented function
     *
     * @param array $body
     * @return obj
     */
    public function postShipment($info_shipment)
    {
        $info_config = $info_shipment['info_config'];
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

        $ch         = curl_init();
        $options    = array(
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