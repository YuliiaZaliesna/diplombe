<?php
class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'bazadanych';

    private $database;
    private $error;
    private $request;

    public function __construct() {
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8';
        $options = array (
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Create a new PDO instanace
        try {
            $this->database = new PDO ($dsn, $this->user, $this->pass, $options);
        }		// Catch any errors
        catch ( PDOException $e ) {
            $this->error = $e->getMessage();
        }
    }

    // Prepare statement with query
    public function query($query) {
        $this->request = $this->database->prepare($query);
    }

    // Bind values
    public function bind($param, $value, $type = null) {
        if (is_null ($type)) {
            switch (true) {
                case is_int ($value) :
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool ($value) :
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null ($value) :
                    $type = PDO::PARAM_NULL;
                    break;
                default :
                    $type = PDO::PARAM_STR;
            }
        }
        $this->request->bindValue($param, $value, $type);
    }


    // Get result set as array of objects
    public function resultSet(string $query, array $data = []){
        $this->request = $this->database->prepare($query);
        foreach ($data as $key => $value){
            $this->bind($key,$value);
        }
        $this->request->execute();
        return $this->request->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single record as object
    public function single(string $query, array $data = []){
        $this->request = $this->database->prepare($query);
        foreach ($data as $key => $value){
            $this->bind($key,$value);
        }
        $this->request->execute();
        return $this->request->fetch(PDO::FETCH_ASSOC);
    }

    public function update(string $query, array $data = []){
        $this->request = $this->database->prepare($query);
        foreach ($data as $key => $value){
            $this->bind($key,$value);
        }
        return $this->request->execute();
    }

    public function insert(string $query, array $data = []){
        $this->request = $this->database->prepare($query);
        foreach ($data as $key => $value){
            $this->bind($key,$value);
        }
        $this->request->execute();
        return $this->database->lastInsertId();
    }

    // Get record row count
    public function rowCount(){
        return $this->request->rowCount();
    }
}
