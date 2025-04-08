<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Logger;
use Artisaninweb\SoapWrapper\SoapWrapper;
use Exception;
use SoapClient;
use SoapFault;

class InterpolController extends Controller
{
    protected $soapWrapper;

    /**
     * SoapController constructor.
     *
     * @param SoapWrapper $soapWrapper
     */
    public function __construct(SoapWrapper $soapWrapper)
    {
        $this->soapWrapper = $soapWrapper;
    }

    public function show($dni, $country)
    {
        //request body
        $req = '
      <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:interpol:ws:wsp:nomtdsltd">
         <soapenv:Header>
            <urn:UsernameToken Version="1.1">
               <!--Optional:-->
               <urn:Username>ncb.asuncionfindsltdtdawnprod.py</urn:Username>
               <!--Optional:-->
               <urn:Password>pamldfr65bgre6</urn:Password>
               <!--pamldfr65bgre6 apmkdjrf25hg-->
            </urn:UsernameToken>
            <urn:UserInformation>
               <!--Optional:-->
               <urn:Username>ncb.asuncionfindsltdtdawntest.py</urn:Username>
               <!--Optional:-->
               <urn:ReferenceInCountry>Paraguay.py</urn:ReferenceInCountry>
            </urn:UserInformation>
            <urn:AdministrativeToken>
               <!--Optional:-->
               <urn:EnquiriesReference>Paraguay.py</urn:EnquiriesReference>
            </urn:AdministrativeToken>
         </soapenv:Header>
         <soapenv:Body>
            <urn:Search>
               <!--Optional:-->
               <urn:DIN>'.$dni.'</urn:DIN>
               <!--Optional:-->
               <urn:CountryOfRegistration>'.$country.'</urn:CountryOfRegistration>
               <!--Optional:-->
               <urn:TypeOfDocument></urn:TypeOfDocument>
               <urn:NbRecord>1</urn:NbRecord>
            </urn:Search>
         </soapenv:Body>
      </soapenv:Envelope>';

        //url variables.
        $location_URL = 'http://192.168.48.25:248/WSP/1.1/bc/tdawnsltd.asmx';
        $action_URL = 'urn:interpol:ws:wsp:nomtdsltd/Search';
        //init soap client
        $client = new \SoapClient(null, array('location' => $location_URL, 'uri' => "", 'trace' => 1));
        //methods
        $exception = null;
        try {
            $request = $client->__doRequest($req, $location_URL, $action_URL, 1);
        } catch (SoapFault $sf) {
            //this code was not reached
            $exception = $sf;
        } catch (Exception $e) {
            //nor was this code reached either
            $exception = $e;
        }
        //VALIDATE ERROR
        if((isset($client->__soap_fault)) && ($client->__soap_fault != null)) {
            //this is where the exception from __doRequest is stored
            $exception = $client->__soap_fault;
        }
        //decide what to do about the exception here or throw the exception
        if($exception != null) {
            //logger data
            $logger = array();
            $logger['name'] = 'INTERPOL_WS';
            $logger['descripcion'] = 'GET_REQUEST(DIN: '.$dni.', CountryOfRegistration: '.$country.'), CONNECTION_FAILED';
            $logger['status'] = 'FAILED';
            //call logger save
            $this->logger($logger);
            //result
            return response()->json($exception, 500);
            //throw $exception;
        }
        //Get response from here
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $request);
        //
        try{
            //parse xml
            $xml = new \SimpleXMLElement($response);
            $body = $xml->xpath('//soapBody')[0];
            //$array = json_encode((array)$body, JSON_PRETTY_PRINT);
            $array = json_decode(json_encode((array)$body));
            $data = $array->SearchResponse->SearchResult->resultCode;
            //validate
            $logger = array();
            $logger['name'] = 'INTERPOL_WS';
            //validation
            if($data === 'NO_ERROR'){
                //logger data
                $logger['descripcion'] = 'GET_REQUEST(DIN: '.$dni.', CountryOfRegistration: '.$country.'), ALERT: POSITIVE';
                $logger['status'] = 'OK';
                //call logger save
                $this->logger($logger);
                //result
                return response()->json(array("status" => "POSITIVO"), 200);
            } else {
                //logger data
                $logger['descripcion'] = 'GET_REQUEST(DIN: '.$dni.', CountryOfRegistration: '.$country.'), ALERT: NEGATIVE';
                $logger['status'] = 'OK';
                //call logger save
                $this->logger($logger);
                //result
                return response()->json(array("status" => "NEGATIVO"), 200);
            }
            //catch
        } catch(Exception $e){
            return response()->json(array("status" => $e->getMessage()), 200);
        }
    }

    /**
     * @throws Exception
     */
    private function logger($logger){
        //model
        $log = new Logger;
        $log->name = $logger['name'];
        $log->descripcion = $logger['descripcion'];
        $log->status = $logger['status'];
        //save
        $log->save();
        //end
    }
}
