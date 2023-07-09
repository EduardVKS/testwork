<?php
namespace App\Controllers;

use App\Models\DB;

class UsersController {
	protected static $fillable = ['first_name', 'last_name', 'status', 'role'];

	public static function createUser ($request) {
		if ($request["first_name"] && $request["last_name"] && isset($request["status"]) && $request["role"]) {
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
			if($id) return static::getUser(['user' => $id]);
		}
		return false;
	}

	public static function getUser ($request) {
		if (is_numeric($request["user"])) {
			$db = new DB();
			$query = 'SELECT * FROM `users` WHERE `id` = ?';
			
			return $db->getData($query, 'i', [$request["user"]]);
		}
		return false;
	}

	public static function updateUser ($request) {
		if ($request["first_name"] && $request["last_name"] && isset($request["status"]) && $request["role"] && $request['user'] > 0) {
			$request['status'] = ($request['status'] == "true")?1:0;
			$user = $request['user'];
			$db = new DB();
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
			array_push($data, $user);
			$result = $db->updateData($query, $bind, $data);
			if($result) return static::getUser(['user' => $user]);
		}
		return false;
	}

	public static function deleteUser ($request) {
		if (is_numeric($request["user"])) {
			$db = new DB();
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

				return $db->updateData($query, 'i', $request["users"]);
			} else {
				$query = 'DELETE FROM `users` WHERE `id` = ?';
				return $db->deleteData($query, 'i', $request["users"]);
			}

		}

		return false;
	}
}