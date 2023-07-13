<?php

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
		$users = App\Controllers\UsersController::getUsers();
		
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
				'error' => (object) ['code'=>100, 'message'=>$response],
			];
		}
		echo json_encode($result);
	break;

	case 'deleteuser':
		check_token();
		$response = App\Controllers\UsersController::deleteUser($_POST);
		if($response !== false) {
			$result = (object) [
				'status' => true,
				'error' => ($response)?null:'no user',
				'id' => $_POST['user'],
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) ['code'=>100, 'message'=>'Not found user'],
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
				'error' => (object) ['code'=>100, 'message'=>'Not found user'],
			];
		}
		echo json_encode($result);
	break;

	case 'updatestatususers':
		check_token();
		$response = App\Controllers\UsersController::updateStatusUsers($_POST);
		if(is_array($response)) {
			$result = (object) [
				'status' => true,
				'error' => null,
				'action' => $_POST['status'],
				'users' => $response,
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) ['code'=>100, 'message'=> $response],
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

