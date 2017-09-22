/**
 * Author: progmem
 * Date: 13.04.2016
 * Time: 15:50
 */
function uploadPackage()
{
    var filePath = document.getElementById('file-package').value;
    if(filePath != '')
    {
        var filePathSplit = filePath.split('\\');
        var fileName = filePathSplit[filePathSplit.length - 1];

        $.post("uploadfile.php?checkFilename",
            {
                filename: fileName
            },
        function (data)
        {
            var result = data.split(':');

            if(result[0] == 'false')
                alert('file: ' + result[1] + '\n' + result[2]);
            else
                $('form#form-uploadPackage').submit();
        });
    }
    else
    {
        alert('No file specified!');
    }
}

function uploadChangelog()
{
    var filePath = document.getElementById('file-changelog').value;
    if(filePath != '')
    {
        var filePathSplit = filePath.split('\\');
        var fileName = filePathSplit[filePathSplit.length - 1];
        var ext = fileName.substr(-3);

        if(ext != 'txt')
            alert('file: ' + fileName + '\ninvalid file type');
        else
            $('form#form-uploadChangelog').submit();
    }
    else
    {
        alert('No file specified!');
    }
}

function showLatestPackages()
{
    $.get("../api.php?latestPackages", function (data)
    {
        var result = $.parseJSON(data);

        var packageTypes = ['bin', 'maps', 'msc', 'pic', 'sfx', 'sdllib', 'shader'];
        var div = '';

        for (i = 0; i < packageTypes.length; i++)
        {
            div += packageTypes[i] + ': ' + result[packageTypes[i]];
            div += '<br>';
        }
        $('#div-inner-latestPackages').html(div);
    });
}

$(document).ready(function ()
{
    showLatestPackages();

    $('#b-uploadPackage').click(function ()
    {
       uploadPackage();
    });

    $('#b-uploadChangelog').click(function ()
    {
        uploadChangelog();
    });
});