<?php

class Login {

    public function __construct(){

    }

    public function LoginForms(){
        $user = new DBase();
        $user = $user->checkUser();
        setcookie("user",$user[0],time() + 3600);
        setcookie("token",$user[7],time() + 3600);
        header('Location: /');
        return $user;
    }

    public function LogOut(){
        $user = new DBase();
        $user->clearToken($_COOKIE['user']);

        setcookie ("user","",time() - 3600);
        setcookie ("token","",time() - 3600);

        header('Location: /');
    }

    function LoginToken($post){
        $db = new DBase();
        $q = "SELECT * FROM `users` WHERE `email` = '".$post['email']."' AND `pass` = '".md5($post['pass'])."' AND `token` <> ''";
        $result = $db->queryCount($q);

        if($result!=0){
            return 1;
        } else {
            return 0;
        }
    }

    public function Validate(){
        $answer = true;

        $user = new DBase();
        $user = $user->checkUser();

        if(!$this->validate_req($_POST['password'])){
            $answer = false;
        }

        if(!$this->validate_email($_POST['email'])){
            $answer = false;
        }

        if(!isset($user)){
            $answer = false;
        }

        return $answer;
    }

    function validate_req($value)
    {
        $answer = true;
        if(!isset($value) ||
            strlen($value) <=0)
        {
            $answer=false;
        }
        return $answer;
    }

    function validate_email($email)
    {
        return preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email);
    }

}

Class DBase {

    private $conn;

    private $defaults = array(
        'host'      => 'localhost',
        'user'      => 'root',
        'pass'      => '',
        'db'        => 'usersn',
        'charset'   => 'utf8'
    );

    function __construct(){
        $this->conn = mysqli_connect($this->defaults['host'], $this->defaults['user'], $this->defaults['pass'],$this->defaults['db']);
        mysqli_set_charset($this->conn, $this->defaults['charset']);
}

    function checkUser(){
        $q = "SELECT * FROM `users` WHERE `email` = '".$_POST['email']."' AND `pass` = '".md5($_POST['password'])."'";
        $result = $this->query($q);

        if(isset($result)){
            $token = md5(time());
            $q1 = "UPDATE `users` SET `token`= '".$token."'  WHERE `email` = '".$_POST['email']."' AND `pass` = '".md5($_POST['password'])."'";
            $this->update($q1);
        }

        return $result;
    }

    function infoUser($id){
        $q = "SELECT * FROM `users` WHERE `id` = '".$id."'";
        $result = $this->query($q);
        return $result;
    }

    function clearToken($id){
        $q = "UPDATE `users` SET `token`= ''  WHERE `id` = '".$id."'";
        $this->update($q);
    }

    function query($query){
        $q = mysqli_query($this->conn,$query);
        $result = mysqli_fetch_row($q);
        return $result;
    }

    function queryCount($query){
        $q = mysqli_query($this->conn,$query);
        return mysqli_num_rows($q);
    }

    function update($query){
        $q = mysqli_query($this->conn,$query);
        return $q;
    }
}