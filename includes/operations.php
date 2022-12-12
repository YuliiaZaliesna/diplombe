<?php
function getUserData(User $user){
    $data = $user->getSaveData();
    return json_encode($data);
}
function registerUser(Database $database, array $data){
    
    if(empty($data['login']) || empty($data['password']) || empty($data['email'])){
        return array("type"=>"error", "message"=>"Some data are missing!");
    }

    $registration = User::register($database, $data);
    if((int)$registration > 0){
        $user = new User($database, $_SESSION['id']);
        $data = $user->getSaveData();
        return $data;
    }else {
        return array("type"=>"error", "message"=>$registration);
    }
}
function getUserHabitsList(Habit $habit){
    $habits = $habit->getAllUserHabits();
    return json_encode($habits);
}

function getHabitsList(Habit $habit){
    $habits = $habit->getAllHabits();
    return json_encode($habits);
}

function insertProgress(Habit $habit, array $data){
    $habits = $habit->addProgress($data);
    return json_encode($habits);
}

function newHabit(Habit $habit, array $data){
    $habits = $habit->addHabit($data);
    return json_encode($habits);
}
