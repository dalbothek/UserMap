<?php
	require_once('usermap.php');
			
	$map = new UserMap();
	
	$users = $vbulletin->db->query_read_slave("SELECT  
		u.userid
		FROM " . TABLE_PREFIX . "user as u");
	
	$host = $_SERVER['REMOTE_HOST'];
	
	while ($user = $vbulletin->db->fetch_array($users))
	{
		$info = fetch_userinfo($user['userid']);
		if($info['field7'] == 1) {
			$user = new UserMapUser();
			$user->name = $info['username'];
			$user->city = utf8_encode($info['field2']);
			$user->lat = $info['field5'];
			$user->long = $info['field6'];
			$user->url ="$host/members/" . $info['userid'] . "-" . $info['username'];
			$user->img = "$host/image.php?u=" . $info['userid'] . "&type=thumb";
			$map->add($user);
		}
	}
	

	$map->json();
?>