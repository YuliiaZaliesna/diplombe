<?php

class Habit
{
    private Database $database;
    public int $habitId;
    private array $habitData;
    public function __construct($database, $habitId = 0)
        {
            $this->database = $database;
            if(empty($habitId)){
            return false;
                //throw new Exception("User id is wrong");
            }
            $this->habitId = $habitId;
            $queryParameters = array(':id'=>$this->habitId);
            try {
                $this->habitData = $this->database->single('SELECT * FROM nawyki WHERE nawyki_id=:id', $queryParameters);
            }catch (Exception $e) {
                unset($_SESSION['id']);
                throw new Exception("Habit not found!");
            }
        }
    public function getAllUserHabits(){
        $queryParameters = array(':id'=>$_SESSION['id']);
        $habits = $this->database->resultSet("SELECT * FROM wybrane_nawyki LEFT JOIN nawyki ON nawyki.nawyki_id = wybrane_nawyki.nawyki_id WHERE uzytkownik_id=:id", $queryParameters);
        foreach($habits as &$habit){
            //TODO function to get list of checks
            $habit['checks'] = $this->getChecksCalendar($habit['wybrane_nawyki_id']);
        }
        return $habits;
    }

    public function getAllHabits(){
        $queryParameters = array(':id'=>$_SESSION['id']);
        $habits = $this->database->resultSet("SELECT * FROM nawyki ", $queryParameters);
        return $habits;
    }

    public function getChecksCalendar(int $wybranenawykiid){
        $queryParameters = array(':wybrane_nawyki_id'=>$wybranenawykiid);
        $calendar = $this->database->resultSet("SELECT * FROM postep WHERE wybrane_nawyki_id=:wybrane_nawyki_id", $queryParameters);
        return $calendar;
    }

    public function addProgress(array $data)
    {
        //TODO MD5 PASSWORD
        $queryParameters = array(':wybrane_nawyki_id'=>$data['nawykiID'], ':dzien'=>$data['dzien']);
        $progressEntry = $this->database->single('SELECT * FROM postep WHERE wybrane_nawyki_id=:wybrane_nawyki_id OR dzien=:dzien', $queryParameters) || [];
        if(gettype($progressEntry) == 'array'){
                return "This day and habit is already checked";
        }
        $jsDate = strtotime($data['dzien']);
        if(date('Y-m-d',$jsDate) != date('Y-m-d')){
            return array("type"=>"error", "message"=>"Progress  was not added");
        }
        try {
            $insertedID = $this->database->insert('INSERT INTO postep (wybrane_nawyki_id, dzien) VALUES (:wybrane_nawyki_id, :dzien)', $queryParameters);
            $amountOfProgress = $this->database->single('SELECT COUNT(*) AS amount FROM postep WHERE wybrane_nawyki_id=:wybrane_nawyki_id', array(':wybrane_nawyki_id'=>$data['nawykiID']));
            $levelUp = 0;
            if($amountOfProgress["amount"] == 15){
                $levelUp = 1;
            }
            $this->database->update('UPDATE uzytkownik SET points = points+1, poziom = poziom+:levell WHERE uzytkownik_id=:id', array(":id" => $_SESSION['id'], ":levell"=>$levelUp));
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if(empty($insertedID)){
            return false;
        }
        $returnMessage = array("type"=>"success", "message"=>"Progress added");
        return $returnMessage;
    }

    public function addHabit(array $data)
    {
        //TODO MD5 PASSWORD
        $queryParameters = array(':id'=>$_SESSION['id'], ':nawyki_id'=>$data['nawykiID'], ':dzien'=>$data['dzien']);
        $habits = $this->database->single("SELECT * FROM wybrane_nawyki WHERE nawyki_id=:nawyki_id AND data_rozpoczecia=:dzien AND uzytkownik_id=:id", $queryParameters) || [];
        if(gettype($habits) == 'array'){
                return "This habit is already added";
        }
        try {
            $insertedID = $this->database->insert('INSERT INTO wybrane_nawyki (uzytkownik_id, nawyki_id, data_rozpoczecia) VALUES (:id, :nawyki_id, :dzien)', $queryParameters);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if(empty($insertedID)){
            return false;
        }
        $returnMessage = array("type"=>"success", "message"=>"Habit added");
        return $returnMessage;
    }
}