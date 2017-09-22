<?php
/**
 * Author: progmem
 * Date: 21.04.2016
 * Time: 20:10
 */

require_once "inc/config_inc.php";
require_once "inc/database_inc.php";
require_once "inc/packagehelper_inc.php";

if(isset($_POST['apikey']) && $_POST['apikey'] == Config::$API_KEY)
{
    if(isset($_POST['ptype']) && isset($_POST['pdate']) && isset($_POST['pversion']) && isset($_POST['sha1'])
        && isset($_POST['directoryListing']) && is_uploaded_file($_FILES['file']['tmp_name']))
    {
        try
        {
            if(checkPackage($_POST['ptype'], $_POST['pdate'], $_POST['pversion']))
            {
                uploadPackage($_POST['ptype'], $_POST['pdate'], $_POST['pversion'], $_POST['sha1'], $_POST['directoryListing']);
                echo "true";
            }
        }
        catch (Exception $e)
        {
            
        }
    }
}

function checkPackage($ptype, $pdate, $pversion)
{
    if(!PackageHelper::validatePackage($ptype, $pdate, $pversion))
        return false;
    
    $db = Database::connectDB();

    //check if package already exists
    $q = $db->prepare("SELECT COUNT(*) FROM gamefiles WHERE ptype = :ptype AND pdate = :pdate AND pversion = :pversion");
    $q->execute(array('ptype' => $ptype, 'pdate' => $pdate, 'pversion' => $pversion));
    $result = $q->fetch();
    if($result[0] > 0)
        return false;

    //check for newer packages
    $q = $db->prepare("SELECT * FROM gamefiles WHERE pdate = (SELECT MAX(pdate) FROM gamefiles) ORDER BY pversion DESC LIMIT 1");
    $q->execute();
    $result = $q->fetch(PDO::FETCH_ASSOC);
    $resdateInt = intval($result['pdate']);
    $pdateInt = intval($pdate);
    if($resdateInt > $pdateInt || ($resdateInt == $pdateInt && intval($result['pversion']) > intval($pversion)))
        return false;

    return true;
}

function uploadPackage($ptype, $pdate, $pversion, $sha1, $directoryListing)
{
    $uploaddir = '../dl/';
    $uploadpath = $uploaddir . PackageHelper::createPackageName($ptype, $pdate, $pversion, true);

    if (!(move_uploaded_file($_FILES['file']['tmp_name'], $uploadpath)))
        throw new Exception('could not save file');

    if($sha1 != sha1_file($uploadpath))
        throw new Exception('sha1 hashes do not match');

    $db = Database::connectDB();

    $q = $db->prepare("INSERT INTO gamefiles (ptype, pdate, pversion, sha1, download_requests, directory_listing) VALUES (:ptype, :pdate, :pversion, :sha1, 0, :directoryListing)");
    $q->execute(array('ptype' => $ptype, 'pdate' => $pdate, 'pversion' => $pversion, 'sha1' => $sha1, 'directoryListing' => $directoryListing));
}
