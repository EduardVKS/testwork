<?php
namespace App\Controllers;

use App\Models\DB;

class UsersController {
	protected static $fillable = ['first_name', 'last_name', 'status', 'role'];

	private static $role = [1 => 'admin', 2 => 'user'];

	public static function createUser ($request) {
		if (trim($request["first_name"]) && trim($request["last_name"]) && isset($request["status"]) && (isset($request["role"]) && isset(self::$role[$request["role"]])) ) {
			$request['status'] = ($request['status'] == "true")?1:0;
			$db = new DB();
			$query = 'INSERT INTO `users` SET';
			$bind = '';
			foreach ($request as $field => $value) {
				if(!in_array($field, static::$fillable)) {
					unset($request[$field]);
					continue;
				}
				$query .= ' `'.$field.'` = ?,';
				$bind .= ($field != 'status')?'s':'i';

			}
			$id = $db->setData(rtrim($query, ','), $bind, array_values($request));
			if($id) {
				$user = static::getUser(['user' => $id]);
				$user['role'] = self::$role[$user['role']];
				return $user;
			}
		}
		return "Wrong data";
	}

	public static function getUser ($request) {
		if (is_numeric($request["user"])) {
			$db = new DB();
			$query = 'SELECT * FROM `users` WHERE `id` = ?';
			
			return $db->getData($query, 'i', [$request["user"]]);
		}
		return false;
	}

	public static function getUsers () {
		$db = new DB();
		$users = $db->getDataAll('Select * FROM `users`');
		foreach ($users as $key => $user) {
			$users[$key]['role'] = self::$role[$user['role']];
			$users[$key]['status'] = $user['status']?'status-green':'status-grey';
		}
		return $users;
	}

	public static function updateUser ($request) {
		if ($request["first_name"] && $request["last_name"] && isset($request["status"]) && isset(self::$role[$request["role"]]) && $request['user'] > 0) {

			$db = new DB();
			$user = static::getUser(['user' => $request['user']]);
			if(!$user) return 'Not found user';

			$request['status'] = ($request['status'] == "true")?1:0;
			$query = 'UPDATE `users` SET';
			$bind = '';
			foreach ($request as $field => $value) {
				if(!in_array($field, static::$fillable)) {
					unset($request[$field]);
					continue;
				}
				$query .= ' `'.$field.'` = ?,';
				$bind .= ($field != 'status')?'s':'i';
			}
			$bind .= 'i';
			$query =  rtrim($query, ',').' WHERE `id` = ?';
			$data = array_values($request);
			array_push($data, $user['id']);
			if($db->updateData($query, $bind, $data)) {
				$user = static::getUser(['user' => $user['id']]);
				$user['role'] = self::$role[$user['role']];
				return $user;
			}
		}
		return 'Wrong data';
	}

	public static function deleteUser ($request) {
		if (is_numeric($request["user"])) {
			$db = new DB();

			if(!static::getUser(['user' => $request["user"]])) return false;

			$query = 'DELETE FROM `users` WHERE `id` = ?';
			
			return $db->deleteData($query, 'i', [$request["user"]]);
		}
		return false;
	}

	public static function updateStatusUsers ($request) {
		if (in_array($request['status'], ['active', 'notactive', 'delete']) && count($request['users'])) 
		{
			$db = new DB();
			$bind = 'i';
			if($request['status'] != 'delete') {
				$status = ($request['status'] == 'active')?1:0;
				$query = "UPDATE `users` SET `status` = $status WHERE `id` = ?";
				$result = $db->updateData($query, 'i', $request["users"]);
			} else {
				$query = 'DELETE FROM `users` WHERE `id` = ?';
				$result = $db->deleteData($query, 'i', $request["users"]);
			}

			if (!$result) {
				foreach ($request['users'] as $user) {
					if(static::getUser(['user' => $user[0]])) return $result;
				}
				return 'No found users';
			} else {
				return $result;
			}

		}
		if (!in_array($request['status'], ['active', 'notactive', 'delete'])) return "no actions";
		return "no users";
	}
}