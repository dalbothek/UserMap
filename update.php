<?php
	require_once('usermap.php');
	
	// Random data generator
	function randomUser() {
		$user = new UserMapUser();
		$user->name = randomString(mt_rand(5,10));
		$user->city = randomString(mt_rand(5,10));
		$user->lat = mt_rand(-90, 90);
		$user->long = mt_rand(-180, 180);
		$user->url = "http://www." . randomString(mt_rand(3,15)) . ".com";
		return $user;
	}
	
	function randomString($l) {
		return ($l==0) ? "" : chr(mt_rand(ord("a"), ord("z"))) . randomString($l-1);
	}
			
	$map = new UserMap();
	
	for($i=0; $i<20; $i++) {
		$map->add(randomUser());
	}
	
	if($map->save()) {
		echo "Success";
	} else {
		echo "Fail";
	}
?>

