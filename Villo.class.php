<?php
/**
 *
 * @package packages/Bikes
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert <pieter aŧ iRail.be>
 */

include_once('gpoint.php');

class BikesVillo extends AReader
{

    public function __construct($package, $resource, $RESTparameters)
    {
        parent::__construct($package, $resource, $RESTparameters);
        $this->lat = null;
        $this->long = null;
        $this->offset = 0;
        $this->rowcount = 1024;
    }

    public static function getParameters()
    {
        return [
            "lat"      => "Latitude",
            "long"     => "Longitude",
            "offset"   => "Offset",
            "rowcount" => "Rowcount",
        ];
    }

    public static function getRequiredParameters()
    {
        return array();
    }

    public function setParameter($key, $val)
    {
        if ($key == "lat") {
            $this->lat = $val;
        } elseif ($key == "long") {
            $this->long = $val;
        } elseif ($key == "offset") {
            $this->offset = $val;
        } elseif ($key == "rowcount") {
            $this->rowcount = $val;
        }
    }

    public function read()
    {
        $data = TDT::HttpRequest("http://www.mobielbrussel.irisnet.be/villo/json/");
        $decoded = json_decode($data->data);
        //todo: convert to wished format

        $result = array();
        $gpoint = new gPoint();

        foreach ($decoded->features as $feature) {
            $station = new Object();

            $station->name = $feature->properties->NAME;
            $station->freebikes = $feature->properties->FREEBK;
            $station->freespots = $feature->properties->FREEBS;
            $station->state = $feature->properties->STATE;

            // Configure the gPoint library to use the Lambert Projection for Belgium
            $gpoint->configLambertProjection(150000.013, 5400088.438, 4.367487, 90, 49.833333, 51.166666);
            $x = $feature->geometry->coordinates[0];
            $y = $feature->geometry->coordinates[1];

            $gpoint->setLambert($x, $y);
            // Convert the Lambert Coordinates to Latitude and Longitude (using the gPoint Library)
            $gpoint->convertLCCtoLL();

            $station->latitude = $gpoint->lat;
            $station->longitude = $gpoint->long;

            if ($this->lat != null && $this->long != null) {
                $station->distance = $gpoint->distanceFrom($this->long, $this->lat);
            }

            array_push($result, $station);
        }

        function compare($a, $b)
        {
            if ($a->distance == $b->distance) {
                return 0;
            }
            return ($a->distance < $b->distance) ? -1 : 1;
        }

        if ($this->lat != null && $this->long != null) {
            usort($result, "compare");
        }

        return array_slice($result, $this->offset, $this->rowcount);
    }

    public static function getDoc()
    {
        return "This resource contains dynamic information about the availability of bikes in Brussels";
    }
}
