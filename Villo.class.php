<?php
/**
 * 
 * @package packages/Bikes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert <pieter aŧ iRail.be>
 */

include_once('gpoint.php');
 
class BikesVillo extends AReader{

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);	
        $this->lat = null;
        $this->long = null;
        $this->offset = 0;
        $this->rowcount = 1024;
    }

    public static function getParameters(){
        return array(   "lat" => "Latitude" 
                        ,"long" => "Longitude"
                        ,"offset" => "Offset"
                        ,"rowcount" => "Rowcount");
    }

    public static function getRequiredParameters(){
        return array();
    }

    public function setParameter($key,$val){
        if($key == "lat") {
            $this->lat = $val;
        } else if ($key == "long") {
            $this->long = $val;
        } else if ($key == "offset") {
            $this->offset = $val;
        } else if ($key == "rowcount") {
            $this->rowcount = $val;
        }
    }

    public function read(){
	    //        $data = TDT::HttpRequest("http://www.mobielbrussel.irisnet.be/villo/json/");
	$data = TDT::HttpRequest('https://api.jcdecaux.com/vls/v1/stations?apiKey=6d5071ed0d0b3b68462ad73df43fd9e5479b03d6&contract=Bruxelles-Capitale');
        $decoded = json_decode($data->data);
        //todo: convert to wished format
        
        $result = array();
        $gpoint = new gPoint();
        
        foreach($decoded as $feature) {
            $station = new Object();
            
            $station->name = $feature->name;
            $station->freebikes = $feature->available_bikes;
            $station->freespots = $feature->available_bike_stands;
            $station->state = $feature->status;
            
            // Configure the gPoint library to use the Lambert Projection for Belgium
//            $gpoint->configLambertProjection(150328,166262, 4.359216,50.797815, 49.833333, 51.166666); 
            //$gpoint->configLambertProjection(150000.013, 5400088.438, 4.367487, 90, 49.833333, 51.166666);
            //$x = $feature->geometry->coordinates[0];
            //$y = $feature->geometry->coordinates[1];
            
            //$gpoint->setLambert($x, $y);
            // Convert the Lambert Coordinates to Latitude and Longitude (using the gPoint Library)
            //$gpoint->convertLCCtoLL();
            
            $station->latitude = $feature->position->lat;
            $station->longitude = $feature->position->lng;
            
            if($this->lat != null && $this->long != null) {
                $station->distance = $gpoint->distanceFrom($this->long, $this->lat);
            }
            
            array_push($result, $station);
        }
        
        function compare($a, $b) {
            if ($a->distance == $b->distance) {
                return 0;
            }
            return ($a->distance < $b->distance) ? -1 : 1;
        }
        
        if($this->lat != null && $this->long != null) {
            usort($result, "compare");
        }
        
        return array_slice($result, $this->offset, $this->rowcount);
    }

    public static function getDoc(){
        return "This resource contains dynamic information about the availability of bikes in Brussels";
    }
}
