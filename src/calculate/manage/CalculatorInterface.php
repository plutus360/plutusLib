<?php

namespace Fdvice\calculate\manage ;

use Fdvice\device\manage\DeviceDto ;

interface CalculatorInterface {

    function addCalculators($calculators , $credentials) : array;
    function assigneDevices(DeviceDto $deviceDto , $credentials): array  ;
    function unassigneDevices(DeviceDto $deviceDto , $credentials): array  ;

    // function buildFilter($filter_asJson) :String ;

}