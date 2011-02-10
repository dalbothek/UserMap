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
			<div id="usermap_findinfo">
				<div id="usermap_findselect">
					<span>Wähle einen der markierten Positionen aus.</span>
				</div>
				<div id="usermap_findsave">
					<span id="usermap_findaddress"></span>
					<span id="usermap_findtip">Bewege die Markierung um die Position zu korrigieren.</span>
					<a href="#" onclick="return savePosition()">Speichern</a>
				</div>
				<div id="usermap_findfail">
					<span>Der angegebene Ort wurde nicht gefunden.</span>
				</div>
			</div>
			<div id="usermap_overlay">
				<img id="usermap_avatar" src="avatar.png" alt="sadimusi" />
				<div id="usermap_info">
					<span id="usermap_username">sadimusi</span>
					<span id="usermap_city">Biel</span>
				</div>
			</div>
		</div>
		<div id="locationfinder">
			<span>Eigener Wohnort suchen:</span>
			<input type="text" id="locationfinder_box" onkeypress="keyDownHandler(event)" />
			<a href="#" onclick="return findLocation()">Suchen</a>
			<a href="#" onclick="return markHere()">opa button</a>
		</div>
	</body>
</html>
