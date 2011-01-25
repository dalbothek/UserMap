<?php
	require_once('usermap.php');
	
	// Random data generator
	function randomUser() {
		$user = new UserMapUser();
		$user->name = randomString(mt_rand(5,10));
		$user->city = randomString(mt_rand(5,10));
		$user->lat = mt_rand(460, 550)/10;
		$user->long = mt_rand(60, 170)/10;
		$user->url = "http://www." . randomString(mt_rand(3,15)) . ".com";
		if(mt_rand(0,1) == 1)
			$user->img = 'avatar.png';
		if(mt_rand(0,1) == 1)
			$user->info = array("gender"=>"male", "age"=>13);
		return $user;
	}
	
	function randomString($l) {
		return ($l==0) ? "" : chr(mt_rand(ord("a"), ord("z"))) . randomString($l-1);
	}
			
	$map = new UserMap();
	
	for($i=0; $i<200; $i++) {
		$map->add(randomUser());
	}
	
	$map->save();
?>