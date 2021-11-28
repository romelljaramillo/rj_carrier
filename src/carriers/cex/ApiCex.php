<?php

Class ApiCex {

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

    public function postShipment($body)
    {
        $url_shipment =  Configuration::get('RJ_CEX_WSURL', null, $this->id_shop_group, $this->id_shop);
        $body = json_encode($body);
        $resp = $this->request('POST', $this->urlShipments, $body);
        return $resp;
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