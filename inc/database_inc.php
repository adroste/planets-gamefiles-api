<?php

/**
 * Author: progmem
 * Date: 14.04.2016
 * Time: 13:27
 */
require_once "config_inc.php";

class Database
{
    public static function connectDB()
    {
        $config = Config::database();

        try
        {
            return new PDO($config[0], $config[1], $config[2]);
        }
        catch (PDOException $e)
        {
            return false;
        }
    }
}