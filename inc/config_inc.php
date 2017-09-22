<?php

/**
 * Author: progmem
 * Date: 14.04.2016
 * Time: 18:50
 */
class Config
{
    public static $PACKAGE_TYPES = array("bin", "maps", "msc", "pic", "sfx", "sdllib", "shader");
    public static $URL_DL = "https://planets.progmem.de/gamefiles/dl/";

    public static function database()
    {
        $dsn = 'mysql:host=localhost;dbname=kopaka_planets;charset=UTF8';
        $dbuser = 'kopaka_planets';
        $dbpass = 'planets1234';
        
        return array($dsn, $dbuser, $dbpass);
    }
}