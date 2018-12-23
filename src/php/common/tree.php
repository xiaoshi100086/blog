<?php
header("Content-Type:text/html; charset=utf-8");
require_once './common/common.php';
require_once './common/mysql.php';

function getNodeByPath($mysql, $path)
{
    $sql = sprintf("SELECT * FROM blog.tree WHERE path='%s'", $path);
    $res = $mysql->query($sql);
    if(count($res) > 0)
    {
        return $res[0];
    }
    return false;
}

function getNode($mysql, $id)
{
    $sql = sprintf("SELECT * FROM blog.tree WHERE blog.tree.id=%d", $id);
    $res = $mysql->query($sql);
    if(count($res) > 0)
    {
        return $res[0];
    }
    return false;
}

function addNodeByPath($mysql, $path)
{
    $file = substr(strrchr($path,'/'),1);
    $dirPath = substr($path, 0, strrpos($path,'/'));
    $title = strstr($file, '.', TRUE)?strstr($file, '.', TRUE):$file;
    $type = is_dir($GLOBALS['rootPath'].$dirPath."/".$file)?"folder":"file";

    $res = getNodeByPath($mysql, $dirPath);
    $parentId = $res->id;
    $level = $res->level + 1;

    if($type == "folder")
    {
        $sql = sprintf("INSERT INTO blog.tree (parentId, title, type, path, level) VALUES (%d, '%s', '%s', '%s', %d)", $parentId, $title, $type, $path, $level);
    }
    else
    {   
        $time = date("Y-m-d H:i:s.",filemtime($GLOBALS['rootPath'].$path));
        $sql = sprintf("INSERT INTO blog.tree (parentId, title, type, path, level, time) VALUES (%d, '%s', '%s', '%s', %d, '%s')", $parentId, $title, $type, $path, $level, $time);
    }
    $mysql->query($sql);
}

function modiNodeByPath($mysql, $path)
{
    $time = date("Y-m-d H:i:s.",filemtime($GLOBALS['rootPath'].$path));
    $sql = sprintf("UPDATE blog.tree SET blog.tree.time = '%s' WHERE blog.tree.path = '%s'", $time, $path);
    $mysql->query($sql);
}

function deleNode($mysql, $id)
{
    $sql = sprintf("DELETE FROM blog.tree WHERE id = %d", $id);
    $mysql->query($sql);
}