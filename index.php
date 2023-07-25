<?php

spl_autoload_register(function($class_name) {
    $path_to_class = str_replace('\\', '/', $class_name);
    include $path_to_class . ".php";
});


$path = isset($_SERVER['REDIRECT_URL'])?$_SERVER['REDIRECT_URL']:'/';

new App\Models\DB(require 'config_db.php');
$roles = [1 => 'admin', 2 => 'user'];
switch ($path) {
	case '/':
		$users = App\Controllers\UsersController::getUsers();
		
		require_once('view.php');
		break;

	case '/addnewuser':
		if(isset($_POST['user']) && empty($_POST['user'])) {
			$response = App\Controllers\UsersController::createUser($_POST);
		} else {
			$response = App\Controllers\UsersController::updateUser($_POST);
		}

		if(isset($response['id'])) {
			$response['role'] = $roles[$response['role']];
			$result = (object) [
				'status' => true,
				'error' => null,
				'user' => $response,
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) $response,
			];
		}
		echo json_encode($result);
		break;

	case '/deleteuser':
		$response = App\Controllers\UsersController::deleteUser($_POST);
		if($response >= 0) {
			$result = (object) [
				'status' => true,
				'error' => $response?null:['code' => 105, 'message' => 'no user'],
				'id' => $_POST['user'],
			];
		} else {
			$result = (object) [
				'status' => false,
				'error' => (object) $response,
			];
		}
		echo json_encode($result);
		break;

	case '/finduser':
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
				'error' => (object) $response,
			];
		}
		echo json_encode($result);
		break;

	case '/updatestatususers':
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
				'error' => (object) ['code' => 104, 'message' => 'wrong data'],
			];
		}
		echo json_encode($result);
		break;
	
	default:
		die('404 - this page not found');
		break;
}



