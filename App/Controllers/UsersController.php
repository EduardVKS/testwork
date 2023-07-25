<?php
namespace App\Controllers;

use App\Models\DB;

class UsersController {
	protected static $fillable = ['first_name', 'last_name', 'status', 'role'];

	public static function createUser ($request) {
		if (trim($request["first_name"]) && trim($request["last_name"]) && isset($request["status"]) && $request["role"] >= 1) {
			$request['status'] = ($request['status'] == "true")?1:0;
			$db = new DB();
			$query = 'INSERT INTO `users` SET';
			$bind = '';
			foreach ($request as $field => $value) {
				if(!in_array($field, static::$fillable)) {
					unset($request[$field]);
					continue;
				}
				$query .= ' `'.htmlspecialchars($field).'` = ?,';
				$bind .= ($field != 'status')?'s':'i';

			}
			$id = $db->setData(rtrim($query, ','), $bind, array_values($request));
			if($id) {
				return static::getUser(['user' => $id]);
			}
		}
		return ['code' => 104, 'message' => 'wrong data'];
	}

	public static function getUser ($request) {
		if (is_numeric($request["user"])) {
			$db = new DB();
			$query = 'SELECT * FROM `users` WHERE `id` = ?';
			
			if($user = $db->getData($query, 'i', [$request["user"]])) {
				return $user;
			} else {
				return ['code' => 105, 'message' => 'no user'];
			}
		}
		return ['code' => 104, 'message' => 'wrong data'];
	}

	public static function getUsers () {
		$db = new DB();
		return $db->getDataAll('Select * FROM `users`');
	}

	public static function updateUser ($request) {
		if ($request["first_name"] && $request["last_name"] && isset($request["status"]) && $request["role"] > 0 && $request['user'] > 0) {

			$db = new DB();
			$user = $request['user'];

			$request['status'] = ($request['status'] == "true")?1:0;
			$query = 'UPDATE `users` SET';
			$bind = '';
			foreach ($request as $field => $value) {
				if(!in_array($field, static::$fillable)) {
					unset($request[$field]);
					continue;
				}
				$query .= ' `'.htmlspecialchars($field).'` = ?,';
				$bind .= ($field != 'status')?'s':'i';
			}
			$bind .= 'i';
			$query =  rtrim($query, ',').' WHERE `id` = ?';
			$data = array_values($request);
			array_push($data, $user);
			if($db->updateData($query, $bind, $data)) {
				return static::getUser(['user' => $user]);
			} else {
				return ['code' => 105, 'message' => 'no user'];
			}
		}
		return ['code' => 104, 'message' => 'wrong data'];
	}

	public static function deleteUser ($request) {
		if (is_numeric($request["user"])) {
			$db = new DB();

			$query = 'DELETE FROM `users` WHERE `id` = ?';
			
			return $db->deleteData($query, 'i', [$request["user"]]);
		}
		return ['code' => 104, 'message' => 'wrong data'];
	}

	public static function updateStatusUsers ($request) {
		if (in_array($request['status'], ['active', 'notactive', 'delete']) && (isset($request['users']) && count($request['users'])))
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
			foreach ($request['users'] as $key => $user) {
				$validate[$key]['id'] = $user[0];
				$validate[$key]['isset'] = (in_array($user[0], $result))?true:false;
			}
			return $validate;
		}
		return false;
	}

}