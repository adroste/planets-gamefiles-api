<?php

/**
 * Author: progmem
 * Date: 14.04.2016
 * Time: 16:25
 */
require_once 'config_inc.php';

class PackageHelper
{
    public static function convertArrayToString($arr)
    {
        /*$str = '';
        $len = count($arr);

        //stop loop before last item
        $len2 = $len - 1;
        for($i = 0; $i < $len2; $i++)
        {
            $str .= $arr[$i] . ':';
        }

        $str .= $arr[$len2];
        return $str;*/
        return implode(':', $arr);
    }

    /**
     * @param string $ptype
     * @param string $pdate
     * @param string $pversion
     * @param bool $withExtension (optional) false by default
     * @return string full (unsplitted) name of package
     */
    public static function createPackageName($ptype, $pdate, $pversion, $withExtension = false)
    {
        $packageName = $ptype . '_' . $pdate . '_' . sprintf("%02d", $pversion);
        if($withExtension)
            $packageName .= '.zip';
        return  $packageName;
    }

    /**
     * @param $pdate
     * @param $pversion
     * @return string package version: e.g. 20160401_02
     */
    public static function createPackageVersion($pdate, $pversion)
    {
        return $pdate . '_' . sprintf("%02d", $pversion);
    }

    /**
     * @param $ptype package type
     * @param $pdate package date
     * @param $pversion package version
     * @return bool true if valid
     */
    public static function validatePackage($ptype, $pdate, $pversion)
    {
        if(self::validatePackageType($ptype) && self::validatePackageDate($pdate) && self::validatePackageVersion($pversion))
            return true;
        return false;
    }

    /**
     * @param $ptype package type
     * @return bool true if valid
     */
    public static function validatePackageType($ptype)
    {
        foreach(Config::$PACKAGE_TYPES as $pt)
        {
            if($pt == $ptype)
                return true;
        }
        return false;
    }

    /**
     * @param $pdate package date
     * @return bool true if valid
     */
    public static function validatePackageDate($pdate)
    {
        if(strlen($pdate) != 8 || intval($pdate) < 20160401)
            return false;
        return true;
    }

    /**
     * @param $pversion package version
     * @return bool true if valid
     */
    public static function validatePackageVersion($pversion)
    {
        if(strlen($pversion) != 2 || ctype_digit($pversion) == false)
            return false;
        return true;
    }


    /**
     * @param string $packageName name of the package
     * @return array splitted $packageName: 'ptype', 'pdate', 'pversion'
     * @throws Exception in case of error / invalid packageName
     */
    public static function splitPackageName($packageName)
    {
        $packageName = str_replace('.', '_', $packageName);
        $p = explode('_', $packageName);

        if(count($p) <= 2)
            return false;

        return array('ptype' => $p[0], 'pdate' => $p[1], 'pversion' => $p[2]);
    }
}