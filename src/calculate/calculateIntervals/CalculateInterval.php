<?php


namespace Fdvice\calculate\calculateIntervals ;

use Exception;
use Fdvice\calculate\calculateIntervals\CalculateIntervalInterface;
use Fdvice\CurlHelper ;

final class CalculateInterval  implements CalculateIntervalInterface
{

  function getIntervalesOfDevice($dataQuery , $credentials) : array {

      $calcsSelector = $dataQuery["calcs"] ;
      $calcDeviceIntervalsSelector  = [] ;
      $calcDevicesSelector  = $dataQuery["calc.devices"] ;
      $filter  = $dataQuery["data"] ;
      $filter["fields"] = array_key_exists("fields" ,$dataQuery) ? join("," ,$dataQuery["fields"]):"" ;
      // $url = CurlHelper::getEndpointUrl(__DIR__."/../../../config/endpoints.json" , "calcs") ;
      $url = "https://flespi.io/gw/calcs/".($calcsSelector != null ?join(",",$calcsSelector) : "all")
      ."/devices/".($calcDevicesSelector != null ?join(",",$calcDevicesSelector) : "all")
      ."/intervals/".($calcDeviceIntervalsSelector != null ?join(",",$calcDeviceIntervalsSelector) : "all")
      ."?data=".urlencode(json_encode($filter));

      
      // var_dump($url) ;
      // die() ;
      $curl = CurlHelper::get($url , $credentials);
      $response = CurlHelper::excuteCurl($curl) ;

      return $response ;
      // return [$url] ;
  }

  /**
   * 
   * @V2
   */

  function getIntervalesOfDevice_V2(CalculateIntervalDto $dto , $credentials) : array {

    // $calcsSelector = $dataQuery["calcs"] ;
    // $calcDeviceIntervalsSelector  = [] ;
    // $calcDevicesSelector  = $dataQuery["calc.devices"] ;
    // $filter  = $dataQuery["data"] ;
    // $filter["fields"] = array_key_exists("fields" ,$dataQuery) ? join("," ,$dataQuery["fields"]):"" ;
    // $url = CurlHelper::getEndpointUrl(__DIR__."/../../../config/endpoints.json" , "calcs") ;

    // $_filter = $dto->getFilter() ;
    // $_filter["fields"] = $dto->getFields();
    // $dto->setFilter($_filter) ;

    $url = "https://flespi.io/gw/calcs/".($dto->getClcs() != null ?join(",",$dto->getClcs()) : "all")
    ."/devices/".($dto->getUnits() != null ?join(",",$dto->getUnits()) : "all")
    ."/intervals/".($dto->getIds() != null ?join(",",$dto->getIds()) : "all")
    ."?data=".urlencode(json_encode($dto->getData()));

    
    // var_dump($url) ;
    // die() ;
    $curl = CurlHelper::get($url , $credentials);
    $response = CurlHelper::excuteCurl($curl) ;

    return $response ;
    // return [$url] ;
}

  function getLastIntervaleOfDevice($dataQuery , $credentials) : array{
      $calcsSelector = $dataQuery["calcs"] ;
      // $calcDeviceIntervalsSelector  = [] ;
      $calcDevicesSelector  = $dataQuery["calc.devices"] ;
      // $filter  = $dataQuery["data"] ;


      $url = CurlHelper::getEndpointUrl(__DIR__."/../../../config/endpoints.json" , "calcs") ;
      $url = $url."/".($calcsSelector != null ?join(",",$calcsSelector) : "/all")
      ."/devices/".($calcDevicesSelector != null ?join(",",$calcDevicesSelector) : "/all")
      ."/intervals/last" ;
      // ."/intervals/".($calcDeviceIntervalsSelector != null ?join(",",$calcDeviceIntervalsSelector) : "last")
      // ."?data=".urlencode(json_encode($filter));

      $curl = CurlHelper::get($url , $credentials);
      $response = CurlHelper::excuteCurl($curl) ;


      return $response ;
      // return [$url] ;
  }

  function buildFilter($filter_List) :String {

    $filter = "";
    $filters = array();

    foreach ($filter_List as $key => $value) {
      switch ($key) {

        case 'range':
          # code...
          $dates = explode(' - ', trim($filter_List["range"], '"'));

          $formattedDates = array_map(function($date) {
              $dateParts = explode('/', trim($date));
              $formattedDate = $dateParts[1] . '-' . $dateParts[0] . '-' . $dateParts[2];
              return $formattedDate;
          }, $dates);

          if (array_key_exists("time_range", $filter_List)) {
            # code...
            $start = $formattedDates[0] . " " . $filter_List["time_range"]["start"] . ":00";
            $end = $formattedDates[1] . " " . $filter_List["time_range"]["end"] . ":00";
          }
          else {
            $start = $formattedDates[0] . " " ."00:00:00";
            $end = $formattedDates[1] . " " . "23:59:00";
          };

          // var_dump($start) ;
          // var_dump($end) ;
          // die() ;

          $start = strtotime($start);
          $end = strtotime($end);

          $start = str_replace("X" , $start , "begin >= X ");
          $end = str_replace("X" , $end , "begin <= X ");

          // if(begin >= strftime(1712077680) && end <= strftime(1712078820), 1, 0)
          $dates = "( ".$start ." && ".$end ." , 1 , error() )" ;
          $dates = "if".$dates  ;



          array_push($filters , $dates);
        break;


        case 'duration':
          // {"filter":"if(duration >= 368 && duration <= 400, true, error())","reverse":false}
          
          
          try {
            $min = $filter_List["duration"]["min"] * 60 ;
            $max = $filter_List["duration"]["max"] * 60 ;

            $min = str_replace("X" , $min , "duration >= X") ;
            $max = str_replace("X" , $max , "duration <= X") ;

            $duration = "if( ".$min ." && ".$max ." , true , error() )" ;
            
          } catch (\Throwable $th) {
            if(array_key_exists("min" , $filter_List["duration"]) )
            {
              $min = $filter_List["duration"]["min"] * 60 ;
              $min = str_replace("X" , $min , "duration >= X") ;
              $duration = "if( ".$min ." , true , error() )" ;
            }
            else {
              $max = $filter_List["duration"]["max"] * 60 ;
              $max = str_replace("X" , $max , "duration <= X") ;
              $duration = "if( ".$max ." , true , error() )" ;

            }
          }
          
          array_push($filters  , $duration) ;

        break;

        case 'mileage':
          # do somthing .
          // distance
            try {
              $min = $filter_List["mileage"]["min"] ;
              $max = $filter_List["mileage"]["max"];
            
              $min = str_replace("X" , $min , "distance >= X") ;
              $max = str_replace("X" , $max , "distance <= X") ;

              $mileage = "if( ".$min ." && ".$max ." , true , error() )" ;

            } catch (\Throwable $th) {
              if(array_key_exists("min" , $filter_List["mileage"]) )
              {
                $min = $filter_List["mileage"]["min"] ;
                $min = str_replace("X" , $min , "distance >= X") ;
                $mileage = "if( ".$min ." , true , error() )" ;
              }
              else {
                $max = $filter_List["mileage"]["max"] ;
                $max = str_replace("X" , $max , "distance <= X") ;
                $mileage = "if( ".$max ." , true , error() )" ;

              }
            }
            array_push($filters  , $mileage) ;
        break;

        // case 'quantity':
        //   # code...
        //   try {
        //     $qte = $filter_List["quantity"];
          
        //     // $min = str_replace("X" , $min , "distance >= X") ;
        //     // $max = str_replace("X" , $max , "distance <= X") ;

        //     $qte = "if( ".$qte ."<= fuel_used"." , true , error() )" ;

        //   } catch (\Throwable $th) {
        //   }
        //   array_push($filters  , $qte) ;
        // break;

        case 'vehicle_is_stationary':
          # code...
          try {
            // If vehicle_is_stationary is "true", we want stationary (movement == 0 or null)
            // If vehicle_is_stationary is "false", we want moving (movement == true/1)
            
            if ($filter_List["vehicle_is_stationary"] == "true") {
              // Want stationary: movement == 0 OR movement == null (null treated as false/stationary)
              $vehicle_stationary = "if( (movement == 0 || movement == null) , true , error() )";
            } else {
              // Want moving: movement == true/1 (exclude null and 0)
              $vehicle_stationary = "if( movement == true , true , error() )";
            }

          } catch (\Throwable $th) {
            
          }
          array_push($filters, $vehicle_stationary);
        break;

        case 'ignition_is_off':
          # code...
          try {
            $ignition_is_off = $filter_List["ignition_is_off"] == "true" ? 0 : true;
            $qte = "if( "."ignition ==".$ignition_is_off." , true , error() )" ;

          } catch (\Throwable $th) {
            
          }
          array_push($filters  , $qte) ;
        break;

        case 'early_end':
          # code...
          try {
            $early_end = $filter_List["early_end"];
            $qte = "if( "."end < ".$early_end." , true , error() )" ;

          } catch (\Throwable $th) {
            
          }
          array_push($filters  , $qte) ;
        break;
        
        //? we used it to give the user the poisblite to get the unauthorized use of the vehicls for a given date range and given time range . 
        // case 'timer':
        //   try {
        //       $timer1 = $filter_List["timer"]["timer1"];
        //       $timer2 = $filter_List["timer"]["timer2"];

        //       // Get local timezone offset in seconds
        //       $timezone_offset = date('Z'); // This gets the server's timezone offset from UTC in seconds

        //       // Convert hours and minutes to seconds and adjust for timezone
        //       $R_start = ($timer1["start_hour"] * 3600 + $timer1["start_minute"] * 60) - $timezone_offset;
        //       $R_end = ($timer2["end_hour"] * 3600 + $timer2["end_minute"] * 60) - $timezone_offset;

        //       // Get the time component of the begin timestamp in UTC
        //       $daily_offset = "floor(begin/86400)*86400";
              
        //       // Add timezone adjustment to the comparison
        //       $qte = "if((begin >= " . $daily_offset . "+" . $R_start . 
        //             ") && (begin <= " . $daily_offset . "+" . $R_end . 
        //             "), true, error())";

        //   } catch (\Throwable $th) {
        //       if (array_key_exists("timer1", $filter_List["timer"])) {
        //           $timer1 = $filter_List["timer"]["timer1"];
        //           $tim = "(start_hour >= ".$timer1["start_hour"]
        //               ." && ("."start_hour !=".$timer1["start_hour"].
        //               " || start_minute > ".$timer1["start_minute"]
        //               ."))";
        //       } else {
        //           $timer2 = $filter_List["timer"]["timer2"];
        //           $tim = "(end_hour <= ".$timer2["end_hour"]
        //               ." && ("."end_hour !=".$timer2["end_hour"].
        //               " || end_minute < ".$timer2["end_minute"]
        //               ."))";
        //       }
        //       $qte = "if( ". $tim .", true , error())" ;
        //   }
        //   array_push($filters, $qte);
        //   break;


        //new timer case to handle overnight

        case 'timer':
          try {
              $timer1 = $filter_List["timer"]["timer1"];
              $timer2 = $filter_List["timer"]["timer2"];

              

              // Get local timezone offset in seconds
              $timezone_offset = date('Z'); // This gets the server's timezone offset from UTC in seconds

              // Convert hours and minutes to seconds
              $R_start = $timer1["start_hour"] * 3600 + $timer1["start_minute"] * 60 - $timezone_offset;
              $R_end = $timer2["end_hour"] * 3600 + $timer2["end_minute"] * 60 - $timezone_offset;

              // Get time component and daily offset
              $time_component = "(begin - floor(begin/86400)*86400)";
              
              // Check if it's an overnight range (end time is less than start time)
              if ($R_end < $R_start) {
                  // For overnight ranges, we need to check if time is either:
                  // 1. After the start time on day 1 OR
                  // 2. Before the end time on day 2
                  $qte = "if(" . $time_component . " >= " . $R_start . 
                        " || " . $time_component . " <= " . $R_end . 
                        ", true, error())";
              } else {
                  // Normal same-day range
                  $qte = "if(" . $time_component . " >= " . $R_start . 
                        " && " . $time_component . " <= " . $R_end . 
                        ", true, error())";
              }

          } catch (\Throwable $th) {
              if (array_key_exists("timer1", $filter_List["timer"])) {
                  $timer1 = $filter_List["timer"]["timer1"];
                  $tim = "(start_hour >= ".$timer1["start_hour"]
                      ." && ("."start_hour !=".$timer1["start_hour"].
                      " || start_minute > ".$timer1["start_minute"]
                      ."))";
              } else {
                  $timer2 = $filter_List["timer"]["timer2"];
                  $tim = "(end_hour <= ".$timer2["end_hour"]
                      ." && ("."end_hour !=".$timer2["end_hour"].
                      " || end_minute < ".$timer2["end_minute"]
                      ."))";
              }
              $qte = "if( ". $tim .", true , error())" ;
          }
          array_push($filters, $qte);
        break;

        case 'geofence_name':
          # code...
          try {
            $geofence_name = $filter_List["geofence_name"];
            $geofence_name = 'geofence_name == "'.$geofence_name.'"' ;
            $qte = "if( ". $geofence_name.", true , error())" ;


          } catch (\Throwable $th) {
            
            return new Exception($th->getMessage()) ;
          }
          array_push($filters  , $qte) ;
        break;



        // case 'end_hour':
        //   # code...
        //   try {
        //     $end_hour = $filter_List["end_hour"];
        //     $qte = "if( "."end_hour <= ".$end_hour." , true , error() )" ;

        //   } catch (\Throwable $th) {
            
        //   }
        //   array_push($filters  , $qte) ;
        // break;

        default:

        break;
      }
    }

    $filter = implode(" && " , $filters);

    // var_dump($filter) ;
    // die();
    return $filter ;

  }


}
