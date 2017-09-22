<?php

/**
 * Author: progmem
 * Date: 14.04.2016
 * Time: 18:50
 */

class Config
{
    public static $API_KEY = 's4MH1dEi62x9u3WlD1FKsT6tz47dAV7l';

    public static $PACKAGE_TYPES = array("full", "cumulative", "single");
    public static $URL_DL = "https://planets.progmem.de/gamefiles/dl/";

    public static function database()
    {
        $dsn = 'mysql:host=localhost;dbname=progmem_planets;charset=UTF8';
        $dbuser = 'progmem';
        $dbpass = '4vZ2kfQfFg2XWego';
        
        return array($dsn, $dbuser, $dbpass);
    }
}