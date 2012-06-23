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
	
        return "A resource for bikes in a certain area";
    }

    public static function getDoc(){
        return "This resource contains dynamic information about the availability of bikes in Brussels";
    }
}

?>
