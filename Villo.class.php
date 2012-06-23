<?php
/**
 * 
 * @package packages/Bikes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert <pieter aŧ iRail.be>
 */

 
class BikesVillo extends AReader{

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);	
    }

    public static function getParameters(){
        return array();
    }

    public static function getRequiredParameters(){
        return array();
    }

    public function setParameter($key,$val){
    }

    public function read(){
        $data = TDT::HttpRequest("http://www.mobielbrussel.irisnet.be/villo/json/");
        $result = json_decode($data->data);
        //todo: convert to wished format
        return $result;
    }

    public static function getDoc(){
        return "This resource contains dynamic information about the availability of bikes in Brussels";
    }
}

?>
