<?php

/**
 * Author: progmem
 * Date: 14.04.2016
 * Time: 16:25
 */
require_once 'config_inc.php';

class PackageHelper
{

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
     * @param $ptype package type
     * @return bool true if valid
     */
    public static function checkPackageType($ptype)
    {
        foreach(Config::$PACKAGE_TYPES as $pt)
        {
            if($pt == $ptype)
                return true;
        }
        return false;
    }

    /**
     * @param string $packageName name of the package
     * @param boolean $hasExtension if packagename contains extension
     * @return array [0] = true|false, [1] = string(error message)|array containing parts of splitted $packageName: 'ptype', 'pdate', 'pversion'
     */
    public static function splitAndCheckPackageName($packageName, $hasExtension = false)
    {
        $packageName = str_replace('.', '_', $packageName);
        $p = explode('_', $packageName);

        if($hasExtension)
        {
            if(count($p) != 4)
                return array(false, 'invalid file name');

            if(strlen($p[3]) != 3 || $p[3] != 'zip')
                return array(false, 'invalid file extension/type');
        }
        else
        {
            if(count($p) != 3)
                return array(false, 'invalid file name');
        }
        
        //check if $p[0] (package type) is a valid package type
        if(self::checkPackageType($p[0]) == false)
            return array(false, 'invalid package name');

        $dateInt = intval($p[1]);
        if(strlen($p[1]) != 8 || $dateInt < 20160401 || checkdate(substr($p[1], 4, 2), substr($p[1], 6, 2), substr($p[1], 0, 4)) == false)
            return array(false, 'invalid date');

        if(intval(date('Ymd')) < $dateInt)
            return array(false, 'date lies in future');

        if(strlen($p[2]) != 2 || ctype_digit($p[2]) == false)
            return array(false, 'invalid subversion');

        return array(true, array('ptype' => $p[0], 'pdate' => $p[1], 'pversion' => $p[2]));
    }
}