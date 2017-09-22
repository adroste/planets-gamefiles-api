<?php
/**
 * Author: progmem
 * Date: 21.04.2016
 * Time: 23:59
 */

require_once "inc/database_inc.php";
require_once "inc/config_inc.php";
require_once "inc/packagehelper_inc.php";


if(isset($_GET['validateFile']) && isset($_POST['sha1']))
{
    echo validateSha1($_POST['sha1']);
}
else if(isset($_GET['getLatestVersion']))
{
    echo getLatestVersion();
}
else if(isset($_GET['getLatestCumulativeVersion']))
{
    echo getLatestVersion('cumulative');
}
else if(isset($_GET['getLatestFullVersion']))
{
    echo getLatestVersion('full');
}
else if(isset($_GET['listAllVersions']))
{
    echo listAllVersions();
}
else if(isset($_GET['listAllPackages']))
{
    echo listAllPackages();
}
else if(isset($_GET['downloadPackage']) && isset($_POST['ptype']) && isset($_POST['pdate']) && isset($_POST['pversion']))
{
    if(PackageHelper::validatePackage($_POST['ptype'], $_POST['pdate'], $_POST['pversion']))
        echo getDownload($_POST['ptype'], $_POST['pdate'], $_POST['pversion']);
}
else if(isset($_GET['getUpdateOrder']) && isset($_POST['pdate']) && isset($_POST['pversion']))
{
    echo getUpdateOrder($_POST['pdate'], $_POST['pversion']);
}
else if(isset($_GET['getDirectoryListing']) && isset($_POST['pdate']) && isset($_POST['pversion']))
{
    echo getDirectoryListing($_POST['pdate'], $_POST['pversion']);
}



function getDirectoryListing($pdate, $pversion)
{
    $db = Database::connectDB();
    $q = $db->prepare("SELECT directory_listing FROM gamefiles WHERE pdate = :pdate AND pversion = :pversion LIMIT 1");
    $q->execute(array('pdate' => $pdate, 'pversion' => $pversion));
    $result = $q->fetch(PDO::FETCH_ASSOC);
    return $result['directory_listing'];
}


function listAllPackages()
{
    $db = Database::connectDB();
    $q = $db->prepare("SELECT ptype, pdate, pversion FROM gamefiles ORDER BY pdate DESC, pversion DESC");
    $q->execute();
    $result = $q->fetchAll(PDO::FETCH_ASSOC);

    $list = array();

    foreach($result as $row)
    {
        $list[] = PackageHelper::createPackageName($row['ptype'], $row['pdate'], $row['pversion']);
    }

    return PackageHelper::convertArrayToString($list);
}


function listAllVersions()
{
    $db = Database::connectDB();
    $q = $db->prepare("SELECT DISTINCT pdate, pversion FROM gamefiles ORDER BY pdate DESC, pversion DESC");
    $q->execute();
    $result = $q->fetchAll(PDO::FETCH_ASSOC);

    $list = array();

    foreach($result as $row)
    {
        $list[] = PackageHelper::createPackageVersion($row['pdate'], $row['pversion']);
    }

    return PackageHelper::convertArrayToString($list);
}


function getLatestVersion($type = '')
{
    $db = Database::connectDB();

    if($type == 'full')
    {
        $q = $db->prepare("SELECT pdate, pversion FROM gamefiles WHERE ptype = 'full' ORDER BY pdate DESC, pversion DESC LIMIT 1");
    }
    else if($type == 'cumulative')
    {
        $q = $db->prepare("SELECT pdate, pversion FROM gamefiles WHERE ptype = 'cumulative' ORDER BY pdate DESC, pversion DESC LIMIT 1");
    }
    else
    {
        $q = $db->prepare("SELECT pdate, pversion FROM gamefiles WHERE pdate = (SELECT MAX(pdate) FROM gamefiles) ORDER BY pversion DESC LIMIT 1");
    }
    $q->execute();
    $result = $q->fetch(PDO::FETCH_ASSOC);
    return PackageHelper::createPackageVersion($result['pdate'], $result['pversion']);
}


function getUpdateOrder($pdate, $pversion)
{
    $db = Database::connectDB();

    $q = $db->prepare("SELECT pdate, pversion FROM gamefiles WHERE ptype = 'full' AND (pdate > :pdate OR (pdate = :pdate AND pversion > :pversion)) ORDER BY pdate ASC, pversion ASC");
    $q->execute(array('pdate' => $pdate, 'pversion' => $pversion));

    $list = array();

    while($row = $q->fetch(PDO::FETCH_ASSOC))
    {
        $list[] = PackageHelper::createPackageName('cumulative', $row['pdate'], $row['pversion']);
    }

    $q->closeCursor();
    $q = $db->prepare("SELECT ptype, pdate, pversion FROM gamefiles WHERE (ptype = 'single' OR ptype = 'cumulative') AND (pdate > :pdate OR (pdate = :pdate AND pversion > :pversion)) ORDER BY pdate DESC, pversion DESC");
    $q->execute(array('pdate' => $pdate, 'pversion' => $pversion));

    $list2 = array();

    while($row = $q->fetch(PDO::FETCH_ASSOC))
    {
        $list2[] = PackageHelper::createPackageName($row['ptype'], $row['pdate'], $row['pversion']);
        if($row['ptype'] == 'cumulative')
            break;
    }

    $list2 = array_reverse($list2);

    $list = array_unique(array_merge($list, $list2));

    return PackageHelper::convertArrayToString($list);
}


function getDownload($ptype, $pdate, $pversion)
{
    $db = Database::connectDB();

    //check if requested package exists
    $q = $db->prepare("SELECT COUNT(*) FROM gamefiles WHERE ptype = :ptype AND pdate = :pdate AND pversion = :pversion");
    $q->execute(array('ptype' => $ptype, 'pdate' => $pdate, 'pversion' => $pversion));
    $result = $q->fetch();
    if($result[0] == 0)
        return false;

    //increment download_requests counter for requested package
    $q = $db->prepare("UPDATE gamefiles SET download_requests = download_requests + 1 WHERE ptype = :ptype AND pdate = :pdate AND pversion = :pversion");
    $q->execute(array('ptype' => $ptype, 'pdate' => $pdate, 'pversion' => $pversion));

    //creates download link for the file
    return Config::$URL_DL . PackageHelper::createPackageName($ptype, $pdate, $pversion, true);
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
    if($result[0] == 0)
        return "false";
    else
        return "true";
}