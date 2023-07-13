<?php
namespace App\Models;

class DB {
	
	private static $db;


	public function __construct($dbinfo = null) {
		if(!$dbinfo) return;
		static::$db = mysqli_connect(
		    $dbinfo->db_host,
		    $dbinfo->db_user,
		    $dbinfo->db_password,
		    $dbinfo->db_name,
		);
	}

	public function getData ($query, $bind='', $data=[]) {
		$stmt = static::$db->prepare($query);
		$stmt->bind_param($bind, ...$data);
		$stmt->execute();
		$result = $stmt->get_result();
		return $result->fetch_assoc();
	}

	public function getDataAll ($query) {
		$result = static::$db->query($query);
		return $result->fetch_all(MYSQLI_ASSOC);
		return $result->fetch_assoc();
	}

	public function setData ($query, $bind='', $data=[]) {
		$stmt = static::$db->prepare($query);
		$stmt->bind_param($bind, ...$data);
		$stmt->execute();
		return $stmt->insert_id;
	}

	public function updateData ($query, $bind='', $data=[]) {
		$stmt = static::$db->prepare($query);
		if(!is_array($data[array_key_first($data)])) {
			$stmt->bind_param($bind, ...$data);
			return $stmt->execute();
		} else {
			$result = [];
			foreach ($data as $value) {
				$stmt->bind_param($bind, ...$value);
				$stmt->execute();
				if($stmt->affected_rows) $result[] = $value; 
			}
			return $result;
		}
	}

	public function deleteData ($query, $bind='', $data=[]) {
		$stmt = static::$db->prepare($query);
		if(!is_array($data[array_key_first($data)])) {
			$stmt->bind_param($bind, ...$data);
			$stmt->execute();
			return $stmt->affected_rows;
		} else {
			$result = [];
			foreach ($data as $value) {
				$stmt->bind_param($bind, ...$value);
				$stmt->execute();
				if($stmt->affected_rows) $result[] = $value;
			}
			return $result;
		}
	}
}
