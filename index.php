<!DOCTYPE html>
<?php require_once('usermap.php'); ?>
<html>
	<head>
		<title>UserMap</title>
		<meta charset="utf-8" />
		<script src="http://yui.yahooapis.com/2.7.0r1/build/yahoo/yahoo-min.js" ></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/event/event-min.js" ></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/connection/connection-min.js"></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/json/json-min.js"></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/element/element-min.js"></script>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<script type="text/javascript" src="usermap.js"></script>
		<link rel="stylesheet" href="usermap.css" />
		<link rel="stylesheet" href="example.css" />
	</head>
	<body>
		<div id="usermap">
			<div id="usermap_map"></div>
			<div id="usermap_loader"></div>
			<div id="usermap_overlay">
				<img id="usermap_avatar" src="avatar.png" alt="sadimusi" />
				<div id="usermap_info">
					<span id="usermap_username">sadimusi</span>
					<span id="usermap_city">Biel</span>
				</div>
			</div>
		</div>
	</body>
</html>
