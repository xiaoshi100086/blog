<?php
header("Content-Type:text/html; charset=utf-8");
$rootPath = "/home/wwwroot/default";

function readText($path)
{
    $content = "";
    $filename = $GLOBALS['rootPath'].$path;
    if(file_exists($filename)){
        $handle = fopen($filename, "r");
        $fileSize = filesize($filename);
        if($fileSize>0){
            $content = fread($handle, $fileSize);
        }
        fclose($handle);
    }
    
    return $content;
}
?>