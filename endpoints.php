<?php

error_reporting(E_ALL & ~E_NOTICE);
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Special-Request-Header');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

session_start();
require_once('includes/Database.php');
require_once('includes/User.php');
require_once('includes/operations.php');
require_once('includes/Habit.php');
$database = new Database();
$errorMessage = array("type"=>"error", "message"=>"An error happened during processing");
$wrongMessage = array("type"=>"error", "message"=>"Operation Not found");
$notLoggedInMessage = array("type"=>"error", "message"=>"You are not logged in");

$data = json_decode(file_get_contents('php://input'), true);

if(isset($_SESSION['id'])){
    try {
        $user = new User($database, $_SESSION['id']);
    } catch (Exception $e){
        $errorData = array("type"=>"error", "message"=>$e->getMessage());
        echo json_encode($errorData);
        return false;
    }
} else {
    if(isset($data['registrationData'])){
        echo json_encode(registerUser($database, $data['registrationData']));
        return;
    }
    if(isset($data['login']) && isset($data['password'])){
        $user = new User($database);
        if($user->logIn($data['login'], $data['password'])){
            echo getUserData($user);
            return;
        }
    }
    echo json_encode($notLoggedInMessage);
    return false;
}

if (isset($data['action'])){
    $action = $data['action'];
    if(empty($action['type'])){
        echo json_encode($errorMessage);
        return false;
    }
    switch ($action['type']){
        case 'userInfo':
            echo getUserData($user);
            break;
        case 'logOut':
            echo $user->logOut();
            break;
        case 'userHabits':
            $habit = new Habit($database);
            echo getUserHabitsList($habit);
            break;
        case 'listOfHabits':
            $habit = new Habit($database);
            echo getHabitsList($habit);
            break;
        case 'insertProgress':
            $habit = new Habit($database);
            echo insertProgress($habit, $action['data']);
            break;
        case 'addHabit':
            $habit = new Habit($database);
            echo newHabit($habit, $action['data']);
            break;
        default:
            echo json_encode($wrongMessage);
            break;
    }
}else{
    echo json_encode($wrongMessage);
}
return true;
