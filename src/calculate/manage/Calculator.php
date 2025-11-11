<?php

namespace Fdvice\calculate\manage ;
use Fdvice\CurlHelper ;
use Fdvice\device\manage\DeviceDto ;


final class Calculator implements CalculatorInterface
{
    function addCalculators($calculators, $credentials): array
    {
        $url = CurlHelper::getEndpointUrl(__DIR__."/../../../config/endpoints.json" , "calcs") ;
        // $url = "https://flespi.io/platform/subaccounts" ;
        $curl = CurlHelper::post($url , $calculators ,$credentials);
        $response = CurlHelper::excuteCurl($curl);
        
        return $response ;
    }

    function assigneDevices(DeviceDto $deviceDto , $credentials): array {
        $url = CurlHelper::getEndpointUrl(__DIR__."/../../../config/endpoints.json" , "calcs") ;
        $url = $url."/".join("%2C" , $deviceDto->getCalcs())."/devices/".join("%2C" , $deviceDto->getIds()) ;

        $curl = CurlHelper::post($url , null ,$credentials);
        $response = CurlHelper::excuteCurl($curl);
        
        return $response ;
    }

        function unassigneDevices(DeviceDto $deviceDto , $credentials): array {
        $url = CurlHelper::getEndpointUrl(__DIR__."/../../../config/endpoints.json" , "calcs") ;
        $url = $url."/".join("%2C" , $deviceDto->getCalcs())."/devices/".join("%2C" , $deviceDto->getIds()) ;

        $curl = CurlHelper::delete($url,$credentials);
        $response = CurlHelper::excuteCurl($curl);
        
        return $response ;
    }

}
