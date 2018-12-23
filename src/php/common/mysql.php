<?php
class Result{
    public $code = 0;
    public $msg = "";
    public $count = 0;
    public $data = array();
}

class MySql{
    public $conn = null;
    public $user_func = "";

    function __construct($servername = "localhost", $username = "root", $password = "123456", $dbname = "blog", $charset="utf8"){
        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("class MySql:数据库连接失败！".$this->$conn->connect_error."! $servername(".$servername."), $username(".$username."), $password(".$password."), $dbname(".$dbname.")");
        }
        $this->conn->set_charset($charset);
    }

    function __destruct(){
        $this->conn->close();
        $this->conn = null;
    }

    function query($sql)
    {
        if($this->conn == null) die("class MySql:$conn == null");

        $result = $this->conn->query($sql);
        if($result === false) die("class MySql: failed , and sql is : ".$sql);
        if($result === true) return $result;

        $array_result = array();
        while($obj = $result->fetch_object())
        {
            if($this->user_func == "")
            {
                array_push($array_result,$obj);
            }else{
                array_push($array_result,call_user_func($this->user_func,$obj));
            }
        }
        $this->user_func = "";
        return $array_result;
    }
}

?>