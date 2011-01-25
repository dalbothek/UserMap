<?php
	require_once('usermap.php');
	
	// Random data generator
	function randomUser() {
		$user = new UserMapUser();
		$user->name = randomString(mt_rand(5,10));
		$user->city = randomString(mt_rand(5,10));
		$user->lat = mt_rand(460, 480)/10;
		$user->long = mt_rand(60, 80)/10;
		$user->url = "http://www." . randomString(mt_rand(3,15)) . ".com";
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