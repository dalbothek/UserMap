<?php

class UserMap {
	private $users;

	public function __construct($users=Null) {
		if($users) {
			$this->addAll($users);
		}
	}
	
	public function add($user) {
		if(is_array($user)) {
			$this->add(new UserMapUser($user));
		} else if(is_a($user, "UserMapUser")) {
			$this->$users[] = $user;
		} else {
			trigger_error("Wrong format (shoud be UserMapUser or array)");
		}
	}
	
	public function addAll($users) {
		if(is_array($users)) {
			foreach($users as $user) {
				$this->add($user);
			}
		} else {
			trigger_error("Wrong format (should be array)")
		}
	}

}

class UserMapUser {
	public $name;

	public function __construct($data=Null) {
		if(is_array($data)) {
			$this->set($data);
		} else if (is_string($data)) {
			$this->name = $data;
		} else if($data != Null) {
			trigger_error("Wrong format (should be string or array)");
		}
	}
}

?>