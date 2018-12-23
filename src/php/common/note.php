<?php
header("Content-Type:text/html; charset=utf-8");
require_once './common/common.php';
require_once './common/mysql.php';

function addNote($mysql, $id, $htmlPath)
{
    $mdPath = pathHtml2Md($htmlPath);
    $content = readText($mdPath);
    $sql = sprintf("INSERT INTO blog.note (id, text) VALUES (%d, '%s')", $id, addslashes($content));
    $mysql->query($sql);
}

function modiNote($mysql, $id, $htmlPath)
{
    $result = getNote($mysql, $id);
    if($result === false){
        return addNote($mysql, $id, $htmlPath);
    }

    $mdPath = pathHtml2Md($htmlPath);
    $content = readText($mdPath);
    $sql = sprintf("UPDATE blog.note SET blog.note.text = '%s' WHERE blog.note.id = %d", addslashes($content), $id);
    $mysql->query($sql);
}

function deleNote($mysql, $id)
{
    $sql = sprintf("DELETE FROM blog.note WHERE id = %d", $id);
    $mysql->query($sql);
}


function getNote($mysql, $id)
{
    $sql = sprintf("SELECT * FROM blog.note WHERE id=%d", $id);
    $result = $mysql->query($sql);
    if(count($result) > 0)
    {
        return $result[0];
    }
    return false;
}

function pathHtml2Md($htmlPath)
{
    $MdPath = substr($htmlPath,0,strpos($htmlPath, '.')).".md";
    return $MdPath;
}
?>