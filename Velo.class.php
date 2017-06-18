<?php
/**
 * 
 * @package packages/Bikes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert <pieter aŧ iRail.be>
 */

class BikesVelo extends AReader{

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
        $data = TDT::HttpRequest("https://www.velo-antwerpen.be/availability_map/getJsonObject");
        $decoded = json_decode($data->data);
        //todo: convert to wished format
        
        $result = array();
        $gpoint = new gPoint();
        
        foreach($decoded as $sourceStation) {
            $station = new Object();
            
            $station->name = $sourceStation->name;
            $station->freebikes = $sourceStation->bikes;
            $station->freespots = $sourceStation->slots;
            $station->state = $sourceStation->status;
            $station->latitude = $sourceStation->lat;
            $station->longitude = $sourceStation->lon;
            
            $gpoint->setLongLat($station->longitude, $station->latitude);
            
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
        return "This resource contains dynamic information about the availability of bikes in Antwerp";
    }
}

?>
