<?php
/**
 * Author: progmem
 * Date: 21.04.2016
 * Time: 20:10
 */

require_once "inc/config_inc.php";

if(isset($_POST['apikey']) && $_POST['apikey'] == Config::$API_KEY)
{
    if(is_uploaded_file($_FILES['file']['tmp_name']))
    {
        try
        {
            if(isset($_GET['changelog']))
                uploadChangelog();
            elseif(isset($_GET['linuxgame']))
                uploadFile("PlanetsGameLinux.zip");
            elseif(isset($_GET['linuxserver']))
                uploadFile("PlanetsServerLinux.zip");
            elseif(isset($_GET['windowsserver']))
                uploadFile("PlanetsServerWindows.zip");

            echo "true";
        }
        catch (Exception $e)
        {
            die;
        }
    }
}


function uploadChangelog()
{
    $size = $_FILES['file']['size'];
    //max filesize: 256000 bytes = 256kb
    if($size > 256000)
        throw new Exception('filesize to big');

    $uploaddir = '../';
    $uploadpath = $uploaddir . 'changelog.txt';

    if(!move_uploaded_file($_FILES['file']['tmp_name'], $uploadpath))
        throw new Exception('could not save file');
}


function uploadFile($fileName)
{
    $uploaddir = '../';
    $uploadpath = $uploaddir . $fileName;

    if(!move_uploaded_file($_FILES['file']['tmp_name'], $uploadpath))
        throw new Exception('could not save file');
}