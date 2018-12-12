<?php
header("Content-Type:text/html; charset=utf-8");
require_once './common/common.php';
require_once './common/mysql.php';
require_once './common/tree.php';

$mysql = new MySql();
$result = new Result();

//获取根节点
$sql = sprintf("select * from blog.tree where blog.tree.id = (select min(blog.tree.id) from blog.tree where blog.tree.id > 0)");
$res = $mysql->query($sql);

//获取一级枝干
$sql = sprintf("select * from blog.tree where blog.tree.parentId = %d", $res[0]->id);
$res = $mysql->query($sql);

$result->data = $res;
die(json_encode($result));
?>