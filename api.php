<?php
/**
 * Author: progmem
 * Date: 14.04.2016
 * Time: 14:16
 */

require_once "inc/database_inc.php";
require_once "inc/packagehelper_inc.php";
require_once "inc/config_inc.php";


if(isset($_GET['validateFile']) && isset($_POST['sha1']))
{
    $sha1 = $_POST['sha1'];
    $result = validateSha1($sha1);
    if($result != false)
        echo $result;
}
else if(isset($_GET['latestPackages']))
{
    $result = getLatestPackages();
    if($result != false)
        echo json_encode($result);

}
else if(isset($_GET['downloadPackage']) && isset($_POST['package']))
{
    $requestedPackage = $_POST['package'];
    $result = getDownload($requestedPackage);
    if($result != false)
        echo $result;
}
else if(isset($_GET['listPackages']) && isset($_POST['packageType']))
{
    $ptype = $_POST['packageType'];
    if(PackageHelper::checkPackageType($ptype) == true)
    {
        $result = listPackages($ptype);
        if($result != false)
            echo json_encode($result);
    }
}

function listPackages($ptype)
{
    $db = Database::connectDB();
    //if db connect was not successful return
    if($db == false)
        return false;

    //get packages (only columns pdate & pversion -> performance) sorted by pdate and pversion descending
    $q = $db->prepare("SELECT pdate, pversion FROM gamefiles WHERE ptype = :ptype ORDER BY pdate DESC, pversion DESC");
    $q->execute(array('ptype' => $ptype));

    $list = array();
    
    while($row = $q->fetch(PDO::FETCH_ASSOC))
    {
        $list[] = PackageHelper::createPackageName($ptype, $row['pdate'], $row['pversion']);
    }

    return $list;
}

function getDownload($requestedPackage)
{
    //correctly split $requestedPackage
    $p = PackageHelper::splitAndCheckPackageName($requestedPackage);
    //$p[0] boolean if success
    if($p[0] == false)
        return false;
    //$p[1] array with package descriptors: ptype, pdate, pversion; set to $p for easier access
    $p = $p[1];

    $db = Database::connectDB();
    //if db connect was not successful return
    if($db == false)
        return false;

    //increment download_requests counter for requested package
    $q = $db->prepare("UPDATE gamefiles SET download_requests = download_requests + 1 WHERE ptype = :ptype AND pdate = :pdate AND pversion = :pversion");
    $q->execute(array('ptype' => $p['ptype'], 'pdate' => $p['pdate'], 'pversion' => $p['pversion']));

    //creates download link for the file
    return Config::$URL_DL . PackageHelper::createPackageName($p['ptype'], $p['pdate'], $p['pversion'], true);
}

function getLatestPackages()
{
    $db = Database::connectDB();
    if($db == false)
        return false;

    //get newest package for package x
    $q = $db->prepare("SELECT ptype, pdate, pversion FROM gamefiles WHERE ptype = :ptype AND pdate = (SELECT MAX(pdate) FROM gamefiles WHERE ptype = :ptype) ORDER BY pversion DESC LIMIT 1");

    $latestPackages = array();

    foreach(Config::$PACKAGE_TYPES as $ptype)
    {
        $q->execute(array('ptype' => $ptype));
        $result = $q->fetch(PDO::FETCH_ASSOC);
        //closeCursor to make new execution possible
        $q->closeCursor();
        //create valid package name and add to array
        $latestPackages[$ptype] = PackageHelper::createPackageName($result['ptype'], $result['pdate'], $result['pversion']);
    }

    return $latestPackages;
}

function validateSha1($sha1)
{
    $db = Database::connectDB();
    if($db == false)
        return false;

    //check if a package with the entered sha1 exists
    $q = $db->prepare("SELECT COUNT(*) FROM gamefiles WHERE sha1 = :sha1");
    $q->execute(array('sha1' => $sha1));
    //query returns 0 if sha1 doesnt match and 1 if sha1 was found inside db
    $result = $q->fetch();

    //1 if found, 0 if not
    if($result[0] == 1)
        return "true";
    else
        return "false";
}