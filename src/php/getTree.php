<?php
header("Content-Type:text/html; charset=utf-8");
require_once './common/common.php';
require_once './common/mysql.php';
require_once './common/tree.php';

$rootId = $_POST["rootId"];

$mysql = new MySql();
$result = new Result();

$result->data=getTree($mysql, $rootId);
echo json_encode($result);

function getTree($mysql, $rootId)
{
    setRootSelect($mysql, $rootId);
    $sql = sprintf("select * from blog.tree where blog.tree.id = %d", $rootId);
    $res = $mysql->conn->query($sql);
    if($obj = $res->fetch_object()){
        $rootNode = new stdClass();
        dbObj2Note($obj, $rootNode);
        getTreeCore($mysql, $rootNode);
        
        return decorateTree($rootNode);
    } 
}

function getTreeCore($mysql, $node)
{
    $sql = sprintf("SELECT * FROM blog.tree WHERE path like'%s/%%' AND level=%d+1 ORDER BY type ASC, CONVERT(blog.tree.title using gbk) ASC", $node->basicData->path, $node->level);
    $res = $mysql->conn->query($sql);
    while($obj = $res->fetch_object())
    {
        $childNode = new stdClass();
        dbObj2Note($obj, $childNode);
        array_push($node->children, getTreeCore($mysql, $childNode));
    }
    return $node;
}

function dbObj2Note($obj, $note)
{
    $note->id = $obj->id;
    $note->title = $obj->title;
    $note->isLast = $obj->type=='file'?true:false;
    $note->level = $obj->level;
    $note->parentId = $obj->parentId;
    $note->spread = $obj->spread?true:false;
    $note->basicData = new stdClass();
    $note->basicData->path = $obj->path;
    $note->children = array();
    $note->basicData = new stdClass();
    $note->basicData->path = $obj->path;
}

function decorateTree($rootNode)
{
    foreach($rootNode->children as $node){ 
        $node->parentId = 0;
    }
    return  $rootNode->children;
}

function setRootSelect($mysql, $rootId)
{
    //获取最底层节点
    $res = getNode($mysql, $rootId);
    $parentId = $res->parentId;

    //把所有树的根节点的spread置为0
    $sql = sprintf("UPDATE blog.tree SET blog.tree.spread = 0 WHERE blog.tree.parentId = %d", $parentId);
    $mysql->query($sql);

    //把选中树的根节点的spread置为1
    $sql = sprintf("UPDATE blog.tree SET blog.tree.spread = 1 WHERE blog.tree.id = %d", $rootId);
    $mysql->query($sql);
}

// function getNoteList($mysql)
// {
//     $sql = sprintf("SELECT * FROM blog.tree");
//     $mysql->user_func = function ($obj)
//     {
//         $obj_res = new stdClass();
//         $obj_res->id = $obj->id;
//         $obj_res->parentId = $obj->parentId;
//         $obj_res->title = $obj->title;
//         if($obj->type == 'file'){
//             $obj_res->isLast = true;
//             $obj_res->iconClass = "icon-normal-file";
//         }else{
//             $obj_res->isLast = false;
//         }
//         $obj_res->basicData = new stdClass();
//         $obj_res->basicData->path = $obj->path;
//         $obj_res->spread =  true;
        
//         return $obj_res;
//     };
//     return $mysql->query($sql);
// }

?>