<?php

class User
{
    private Database $database;
    public int $userId;
    private array $userData;
    private array $saveKeys = array("uzytkownik_id","loginn", "points", "poziom");
    public function __construct($database, $userId = 0)
    {
        $this->database = $database;
        if(empty($userId)){
        return false;
            //throw new Exception("User id is wrong");
        }
        $this->userId = $userId;
        $queryParameters = array(':id'=>$this->userId, ':login'=>$_SESSION['login'], ':password'=>$_SESSION['password']);
        try {
            $this->userData = $this->database->single('SELECT * FROM uzytkownik WHERE uzytkownik_id=:id AND loginn=:login AND pass=:password', $queryParameters);
        }catch (Exception $e) {
            unset($_SESSION['id']);
            unset($_SESSION['login']);
            unset($_SESSION['password']);
            throw new Exception("User not found!");
        }
    }

    public function logIn(string $login, string $password)
    {
        //TODO MD5 PASSWORD
        $queryParameters = array(':loginn'=>$login, ':pass'=>$password);
        try {
            $data = $this->database->single('SELECT * FROM uzytkownik WHERE loginn=:loginn AND pass=:pass', $queryParameters);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        if(empty($data)){
            return false;
        }
        $this->userId = $data['uzytkownik_id'];
        $this->userData = $data;
        $_SESSION['id'] = $this->userId;
        $_SESSION['login'] = $login;
        $_SESSION['password'] = $password;
        return true;
    }

    public function logOut(){
        if(isset($_SESSION['id'])){
            unset($_SESSION['id']);
            unset($_SESSION['login']);
            unset($_SESSION['password']);
            $returnMessage = array("type"=>"success", "message"=>"User logged out");
        } else {
            $returnMessage = array("type"=>"error", "message"=>"You are not logged in");
        }
        return json_encode($returnMessage);
    }

    public static function register(Database $database, array $data)
    {
        //TODO MD5 PASSWORD
        $queryParameters = array(':loginn'=>$data['login'], ':email'=>$data['email']);
        $userEntry = $database->single('SELECT * FROM uzytkownik WHERE loginn=:loginn OR email=:email', $queryParameters) || [];
        if(gettype($userEntry) == 'array'){
            if($userEntry['loginn']==$data['login']){
                return "This login already registered";
            }else if($userEntry['email']==$data['email']){
                return "This email already registered";
            }
        }

        try {
            $registerParameters = array(':loginn'=>$data['login'], ':pass'=>$data['password'], ':email'=>$data['email']);
            $insertedID = $database->insert('INSERT INTO uzytkownik (loginn, pass, email) VALUES (:loginn, :pass, :email)', $registerParameters);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if(empty($insertedID)){
            return false;
        }

        $_SESSION['id'] = $insertedID;
        $_SESSION['login'] = $data['login'];
        $_SESSION['password'] = $data['password'];
        return $insertedID;
    }

    public function getSaveData(){
        return array_intersect_key($this->userData, array_flip($this->saveKeys));
    }
}
