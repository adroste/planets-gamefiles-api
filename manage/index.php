<?php
/**
 * Author: progmem
 * Date: 13.04.2016
 * Time: 01:10
 */

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <script src="../inc/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="manage.js" type="text/javascript"></script>

    <title>Manage Game Files</title>
</head>
<body>
<div id="div-content">
    <div id="div-uploadGameFile">
        <?php include 'form_uploadPackage.html'; ?>
    </div>
    <br>
    <div id="div-uploadChangelog">
        <?php include 'form_uploadChangelog.html'; ?>
    </div>
    <br>
    <br>
    <div id="div-outer-latestPackages">
        <h3>Latest packages:</h3>
        <div id="div-inner-latestPackages"></div>
    </div>
</div>
</body>
</html>