<?php

class UserMap {
	const jsonPath = "data.json";
	
	
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
			$this->users[] = $user;
		} else {
			trigger_error("Wrong format (expected UserMapUser or array)");
		}
	}
	
	public function addAll($users) {
		if(is_array($users)) {
			foreach($users as $user) {
				$this->add($user);
			}
		} else {
			trigger_error("Wrong format (expected array)");
		}
	}
	
	public function save() {
		if(!$json = fopen(self::jsonPath, "w+"))
			return false;
		fwrite($json, json_encode($this->users));
		fclose($json);
		return true;
	}
	
	public function json() {
		echo json_encode($this->users);
	}

}

class UserMapUser {
	public $name;
	public $city;
	public $lat;
	public $long;
	public $url;
	public $img;
	public $info;

	public function __construct($data=Null) {
		if(is_array($data)) {
			$this->set($data);
		} else if (is_string($data)) {
			$this->name = $data;
		} else if($data != Null) {
			trigger_error("Wrong format (expected string or array)");
		}
	}
	
	/** 
	 * Set all properties with an array.
	 *
	 * The array must be formatted like this: name, city, latitude, longitude, url, [image], [additional info]
	 * name: string
	 * city: string
	 * latitude: float (degrees)
	 * longitude: float (degrees)
	 * url: string (absolute)
	 * image: string (url, optional)
	 * info: assoc array (optional)
	 */
	public function set($data) {
		if(!is_array($data)) {
			trigger_error("Wrong format (expected array)");
		} else if(count($data) < 5) {
			trigger_error("Argument number missmatch (expected 5 or more arguments)");
		} else {
			$this->name = $data[0];
			$this->city = $data[1];
			$this->lat = $data[2];
			$this->long = $data[3];
			$this->url = $data[4];
			if(conut($data > 6)) {
				$this->img = $data[5];
				$this->info = $data[6];
			} else if(count($data) == 6) {
				if(is_array($data[5])) {
					$this->info = $data[5];	
				} else {
					$this->img = $data[5];
				}
			}
		}
	}
}

?>