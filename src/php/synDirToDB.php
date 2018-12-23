<?php
header("Content-Type:text/html; charset=utf-8");
require_once './common/common.php';
require_once './common/mysql.php';
require_once './common/tree.php';
require_once './common/note.php';

$dirPath = "/data";

$mysql = new MySql();

InitTree($mysql, $dirPath);

SetTree($mysql, $dirPath, 0);

checkTree($mysql, 0);

function InitTree($mysql, $dirPath)
{
    $sql = sprintf("SELECT * FROM blog.tree WHERE type='tree'");
    $result = $mysql->query($sql);
    if (count($result)>0)
    {
        return;
    }

    $sql = sprintf("INSERT INTO blog.tree(parentId, title, type, path) VALUES (0, 'data', 'tree', '%s')", $dirPath);
    $result = $mysql->query($sql);
    return;
}

function SetTree($mysql, $dirPath, $level)
{
    if(!is_dir($GLOBALS['rootPath'].$dirPath))
    {
        return;
    }
    if ($fileList = opendir($GLOBALS['rootPath'].$dirPath)){
        while ($file = readdir($fileList)){
            if($file == "." || $file == ".." || $file == "picture"){
                continue;
            }

            if(getExt($file)!='.html' && getExt($file)!=''){
                continue;
            }

            setTreeCore($mysql, $dirPath."/".$file);
            
            if(is_dir($GLOBALS['rootPath'].$dirPath."/".$file)){
                SetTree($mysql, $dirPath."/".$file, $level+1);
            }
        }
    }
    closedir($fileList);
}

function setTreeCore($mysql, $path)
{
    $result = getNodeByPath($mysql, $path);
    if($result === false)
    {
        echo "insert node $path<br/>";
        addNodeByPath($mysql, $path);
        $result = getNodeByPath($mysql, $path);
        addNote($mysql, $result->id, $result->path);
    }else{
        $time = date("Y-m-d H:i:s",filemtime($GLOBALS['rootPath'].$path));
        if($result->time!='' && strnatcmp($time, $result->time)!=0){
            echo "update node $path<br/>";
            modiNodeByPath($mysql, $path);
            modiNote($mysql, $result->id, $result->path);
        }
    }
}

function checkTree($mysql, $id)
{
    $sql = sprintf("select * from blog.tree where blog.tree.id = (select min(blog.tree.id) from blog.tree where blog.tree.id > %d)", $id);
    $result1 = $mysql->query($sql);
    if ( count($result1)>0)
    {
        $id = $result1[0]->id;
        $path = $result1[0]->path;
        if(!file_exists($GLOBALS['rootPath'].$path)){
            echo "delete node $path<br/>";
            deleNode($mysql, $id);
            deleNote($mysql, $id);
        }
        return checkTree($mysql, $id);
    }
    return;
}

function getExt($filename)
{
   $pos = strrpos($filename, '.');
   if($pos===false) return '';
   $ext = substr($filename, $pos);
   return $ext;
}

// delete from tree;delete from note;

// select * from tree;select id, length(text) from note;

?>