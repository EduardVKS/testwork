<?php

//$dbinfo = require 'config_db.php';
//print_r($dbinfo);
//exit;

session_start();
setcookie('PHPSESSID', session_id());

spl_autoload_register(function($class_name) {
    $path_to_class = str_replace('\\', '/', $class_name);
    include $path_to_class . ".php";
});

function check_token () {
	if(isset($_SERVER['HTTP_TOKEN'])) {
		if($_SERVER['HTTP_TOKEN'] != $_SESSION['token']) exit;
	} else {
		exit;
	}
}

$path = isset($_GET['path'])?$_GET['path']:'';
new App\Models\DB(require 'config_db.php');
switch ($path) {
	case '':
		if(!isset($_SESSION['token'])) $_SESSION['token'] = md5(microtime() . 'turbo' . time());
		$DB = new App\Models\DB();
		$users = $DB->getDataAll('Select * FROM `users`');
		foreach ($users as $key => $user) {
			$users[$key]['status'] = $user['status']?'status-green':'status-grey';
		}
		require_once('view.php');
	break;

	case 'addnewuser':
		check_token();
		if(isset($_POST['user']) && empty($_POST['user'])) {
			$response = App\Controllers\UsersController::createUser($_POST);
		} else {
			$response = App\Controllers\UsersController::updateUser($_POST);
		}
		if(isset($response['id'])) {
			$result = (object) [
				'status' => true,
				'error' => null,
				'user' => $response,
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) ['code'=>100, 'message'=>'wrong data'],
			];
		}
		echo json_encode($result);
	break;

	case 'deleteuser':
		check_token();
		$response = App\Controllers\UsersController::deleteUser($_POST);
		if($response) {
			$result = (object) [
				'status' => true,
				'error' => null,
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) ['code'=>100, 'message'=>'not found user'],
			];
		}
		echo json_encode($result);
	break;

	case 'finduser':
		check_token();
		$response = App\Controllers\UsersController::getUser($_POST);
		if(isset($response['id'])) {
			$result = (object) [
				'status' => true,
				'error' => null,
				'user' => $response,
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) ['code'=>100, 'message'=>'not found user'],
			];
		}
		echo json_encode($result);
	break;

	case 'updatestatususers':
		check_token();
		$response = App\Controllers\UsersController::updateStatusUsers($_POST);
		if($response) {
			$result = (object) [
				'status' => true,
				'error' => null,
				'action' => $_POST['status'], 
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) ['code'=>100, 'message'=>'not found user'],
			];
		}
		echo json_encode($result);
	break;

	case 'value':
	// code...
	break;
	
	default:
		die('404 - this page not found');
		break;
}

