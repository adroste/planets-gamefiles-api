<?php
/**
 * Author: progmem
 * Date: 13.04.2016
 * Time: 16:37
 */

require_once "../inc/database_inc.php";
require_once "../inc/packagehelper_inc.php";


if(isset($_GET['checkFilename']) && isset($_POST['filename']))
{
    $filename = $_POST['filename'];
    $checkName = checkFilename($filename);
    if($checkName[0] == false)
        echo 'false' . ':' . $checkName[1] . ':' . $checkName[2];
    else
        echo 'true';
}
else if(is_uploaded_file($_FILES['file-package']['tmp_name']))
{
    $filename = basename($_FILES['file-package']['name']);
    $checkName = checkFilename($filename);
    if($checkName[0] == false)
        die('file: ' . $checkName[1] . '<br>' . $checkName[2]);

    $result = uploadPackage($checkName);
    if($result[0] == false)
        die('Upload failed!<br>' . $result[1]);
    else
        echo 'Upload successful<br>' . $result[1];
}
else if(is_uploaded_file($_FILES['file-changelog']['tmp_name']))
{
    $result = uploadChangelog();
    if($result == false)
        die('Upload failed!');
    else
        echo 'Upload successful';
}

function checkFilename($filename)
{
    $p = PackageHelper::splitAndCheckPackageName($filename, true);

    if($p[0] == false)
        return array(false, $filename, $p[1]);
    else
        $p = $p[1];

    $db = Database::connectDB();
    if($db == false)
        return array(false, $filename, 'DB Error');

    $q = $db->prepare("SELECT COUNT(*) FROM gamefiles WHERE ptype = :ptype AND pdate = :pdate AND pversion = :pversion");
    $q->execute(array('ptype' => $p['ptype'], 'pdate' => $p['pdate'], 'pversion' => $p['pversion']));
    $result = $q->fetch();
    if($result[0] > 0)
        return array(false, $filename, 'package already exists in database');

    $q = $db->prepare("SELECT * FROM gamefiles WHERE pdate = (SELECT MAX(pdate) FROM gamefiles) ORDER BY pversion DESC LIMIT 1");
    $q->execute();
    $result = $q->fetch(PDO::FETCH_ASSOC);
    $packageName = PackageHelper::createPackageName($result['ptype'], $result['pdate'], $result['pversion']);
    $pdateInt = intval($result['pdate']);
    $dateInt = intval($p['pdate']);
    if($pdateInt > $dateInt || ($pdateInt == $dateInt && intval($result['pversion']) > intval($p['pversion'])))
        return array(false, $filename, "There are already newer components inside the database\nlatest package = " . $packageName);

    return array(true, $filename, $p);
}

function uploadPackage($result_checkFilename)
{
    $uploaddir = '../dl/';
    $uploadpath = $uploaddir . $result_checkFilename[1];

    if (!(move_uploaded_file($_FILES['file-package']['tmp_name'], $uploadpath)))
        return array(false, 'invalid local file');

    $sha1 = sha1_file($uploadpath);

    $db = Database::connectDB();
    if($db == false)
        return array(false, 'database returned false');

    $p = $result_checkFilename[2];

    $q = $db->prepare("INSERT INTO gamefiles (ptype, pdate, pversion, sha1, download_requests) VALUES (:ptype, :pdate, :pversion, :sha1, 0)");
    $q->execute(array('ptype' => $p['ptype'], 'pdate' => $p['pdate'], 'pversion' => $p['pversion'], 'sha1' => $sha1));

    return array(true, 'Sha1: ' . $sha1);
}

function uploadChangelog()
{
    $filename = basename($_FILES['file-changelog']['name']);
    if (substr($filename, -3) != 'txt')
        return false;

    $size = $_FILES['file-changelog']['size'];
    //max filesize: 1000 bytes = 1kb
    if($size > 1000)
        return false;

    $uploaddir = '../';
    $uploadpath = $uploaddir . 'changelog.txt';

    if(move_uploaded_file($_FILES['file-changelog']['tmp_name'], $uploadpath))
        return true;
    else
        return false;
}