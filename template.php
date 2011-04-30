{vb:stylevar htmldoctype}
<html xmlns="http://www.w3.org/1999/xhtml" dir="{vb:stylevar textdirection}" lang="{vb:stylevar languagecode}">
<head>
	{vb:raw headinclude}
	<script type="text/javascript" src="{vb:stylevar yuipath}/animation/animation-min.js?v={vb:raw vboptions.simpleversion}"></script>

<script src="http://yui.yahooapis.com/2.7.0r1/build/yahoo/yahoo-min.js" ></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/event/event-min.js" ></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/connection/connection-min.js"></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/json/json-min.js"></script>
		<script src="http://yui.yahooapis.com/2.7.0r1/build/element/element-min.js"></script>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

        <script type="text/javascript">
var jsonFileURL = "usermap.php?do=get";
var saveURL = function(lat,lng,adr) { return "usermap.php?do=post&lat=" + lat + "&lng=" + lng + "&city=" + escape(adr); };

var userMapUsers, map, mapOverlay, mapOverlayDiv, mapOverlayMulti, selectedMarker;
var mapReady = false;
var mapDataReady = false;
var markers = [];
var geocoder = new google.maps.Geocoder();

var red = new google.maps.MarkerImage("http://www.google.com/intl/en_us/mapfiles/ms/micons/red-dot.png",
	      new google.maps.Size(32, 32),
	      new google.maps.Point(0,0),
	      new google.maps.Point(16, 32));
	       
var blue = new google.maps.MarkerImage("http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png",
	       new google.maps.Size(32, 32),
	       new google.maps.Point(0,0),
	       new google.maps.Point(16, 32));

var shadow = new google.maps.MarkerImage('http://maps.google.com/mapfiles/ms/micons/msmarker.shadow.png',
      		 new google.maps.Size(59, 32),
      		 new google.maps.Point(0,0),
      		 new google.maps.Point(16, 32));

YAHOO.util.Event.onDOMReady(function() {
	initUserMap();
	getUserMapUsers(jsonFileURL);
});

function initUserMap() {
    map = new google.maps.Map(document.getElementById("usermap_map"), {
      zoom: 6,
      center: new google.maps.LatLng(50.764192, 10.239258),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });
    google.maps.event.addListener(map, 'idle', function() {
    	if(!mapReady) {
    		mapReady = true;
    		tryUserLoading();
    	}
    });
    mapOverlayDiv = document.getElementById('usermap_overlay_single');
    mapOverlayDiv.parentNode.removeChild(mapOverlayDiv);
    mapOverlayDiv.style.display = "block";
    mapOverlayMulti = document.getElementById('usermap_overlay_multi');
    mapOverlayMulti.parentNode.removeChild(mapOverlayMulti);
    mapOverlayMulti.style.display = "block";
}

function getUserMapUsers(url) {
	var request = YAHOO.util.Connect.asyncRequest('GET', url, {success: function(o) {
		var users = YAHOO.lang.JSON.parse(o.responseText);
		userMapUsers = users;
		mapDataReady = true;
		tryUserLoading();
	}, failure: function(o) {
		alert("Error");
	}}); 
}

function tryUserLoading() {
	if(mapReady && mapDataReady) {
		window.setTimeout(startUserLoading, 500);
	}
}

function startUserLoading() {
	var n = userMapUsers.length/20;
	var x = 0;
	for(var i=1; i<=20; i++) {
		var y = Math.round(i*n);
		window.setTimeout("showUsers(" + x + "," + y + ")", i*30);
		x = y;
	}
}

function showUsers(x, y) {
	for(var i=x; i<y; i++) {
		showUser(i);
	}
}

function showUser(i) {
	var user = userMapUsers[i];
	var marker = new google.maps.Marker({
 		position: new google.maps.LatLng(user.lat, user.long), 
  		map: map, 
  		//icon: 'http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png',
  		animation: google.maps.Animation.DROP,
  		title:user.name
	}); 
	google.maps.event.addListener(marker, "mouseover", function() {
		showUserInfo(i);
	});
	google.maps.event.addListener(marker, "mouseout", function() {
		closeUserInfo();
	});
	google.maps.event.addListener(marker, "click", function() {
		mapOverlay.handleClick();
	});
	markers.push(marker);
}

function showUserInfo(i) {
	mapOverlay = new UserInfo(i);
}

function near(p1, p2) {
	return (p1.x-p2.x)*(p1.x-p2.x) + (p1.y-p2.y)*(p1.y-p2.y) < 100;
}

function closeUserInfo() {
	if(mapOverlay) {
		mapOverlay.remove();
		mapOverlay = null;
	}
}

// Location Finder

function keyDownHandler(e) {
	if (window.event && window.event.keyCode == 13) {
		findLocation();
	} else if(e && e.which == 13) {
		findLocation();
	}
}

function showSelectWindow(id) {
	document.getElementById("usermap_findinfo").style.display = "block";
	document.getElementById("usermap_findsave").style.display = "none";
	document.getElementById("usermap_findselect").style.display = "none";
	document.getElementById("usermap_findfail").style.display = "none";

	document.getElementById("usermap_find" + id).style.display = "block";
}

function cleanMarkers() {
	closeUserInfo();
	for(var i in markers) {
		markers[i].setVisible(false);
	}
	markers = [];
	selectedMarker = null;
}


function markHere() {
	cleanMarkers();
	
	updateAddress(showResultMarker(map.getCenter(), true));
	
	return false;
}


function findLocation(wherever) {
	cleanMarkers();
	
	var search = document.getElementById("locationfinder_box").value;
	
	if(wherever) {
		geocoder.geocode({address: search, language: "de"}, foundLocation);
	} else {
		geocoder.geocode({address: search, bounds: map.getBounds(), language: "de"}, mayHaveFoundLocation);
	}
	
	return false;
}

function mayHaveFoundLocation(result, status) {
	if(status == "OK") {
		foundLocation(result, status);
	} else {
		findLocation(true);
	}
}

function foundLocation(result, status) {
	if(status == "OK") {
		var bounds = new google.maps.LatLngBounds();
		for(var i in result) {
			showResultMarker(result[i], result.length==1);
			bounds.extend(result[i].geometry.location);
		}
		
		if(result.length!=1) {
			showSelectWindow("select");
		}
		
		var border = 1;
		
		var sw = bounds.getSouthWest();
		sw = new google.maps.LatLng(sw.lat() - border, sw.lng() - border);
		
		var no = bounds.getNorthEast();
		no = new google.maps.LatLng(no.lat() + border, no.lng() + 2*border);
		
		bounds = new google.maps.LatLngBounds(sw, no);
		
		if(map.getZoom() >= 10) {
			map.setZoom(6);
		}
		
		if(result.length!=1) {
			map.panToBounds(bounds);
		} else {
			map.panTo(result[0].geometry.location)
		}
	} else {
		showSelectWindow("fail");
	}
}

function showResultMarker(pos, select) {
	if(pos instanceof google.maps.LatLng) {
		var title = "Laden...";
	} else {
		var title = pos.formatted_address;
		pos = pos.geometry.location;
	}
	var marker = new google.maps.Marker({
 		position: pos, 
  		map: map, 
  		icon: red,
  		shadow: shadow,
  		animation: google.maps.Animation.DROP,
  		title: title,
  		draggable: true
	}); 
	
	google.maps.event.addListener(marker, "click", function() {
		selectResult(marker);
	});
	
	google.maps.event.addListener(marker, "dragstart", function() {
		selectResult(marker);
	});
	
	google.maps.event.addListener(marker, "dragend", function() {
		updateAddress(marker);
	});
	
	google.maps.event.addListener(marker, "dblclick", function() {
		map.setZoom(11);
		map.panTo(marker.getPosition());
	});
	
	markers.push(marker);
	
	if(select) {
		selectResult(marker);
	}
	
	return marker;
}

function selectResult(marker) {
	for(var i in markers) {
		markers[i].setIcon(red);
	}
	
	marker.setIcon(blue);
	
	selectedMarker = marker;
	
	showSelectWindow("save");
	document.getElementById("usermap_findaddress").innerHTML = marker.getTitle();
}

function roundDigits(n, d) {
	var str = "" + n;
	return str.substr(0, d);
}

function savePosition() {
	var lat = selectedMarker.getPosition().lat();
	var lng = selectedMarker.getPosition().lng();
	var address = selectedMarker.getTitle();
	cleanMarkers();
	getUserMapUsers(saveURL(lat,lng,address));
	return false;
}

function updateAddress(marker) {
	geocoder.geocode({location: marker.getPosition(), language: "de"}, foundAddress);
}

function foundAddress(result, status) {
	var targets = ["locality", "postal_code", "postal_code_prefix", "sublocality", "street_address"];
	if(status == "OK") {
		for(var j in targets) {
			var target = targets[j];
			for(var i in result) {
				if(result[i].types[0] == target) {
					var address = result[i];
					for(var j in address.address_components) {
						switch(address.address_components[j].types[0]) {
							case "locality":
								var city = address.address_components[j].long_name;
								break;
							case "country":
								var country = address.address_components[j].long_name;
						}		
					}
					if(country && city) {
						address = city + ", " + country;
					} else {
						address = address.formated_address;
					}
					break;
				}
			}
			if(address) {
				break;
			}	
		}
	}
	if(!address) {
		var address = "Ende der Welt";
	}
	selectedMarker.title = address;
	document.getElementById("usermap_findaddress").innerHTML = address;	
}

// UserInfo Layer Object

function UserInfo(i) {
	this.div_ = mapOverlayDiv;
	this.multi_ = mapOverlayMulti;
	this.id_ = i;
	this.user_ = userMapUsers[i];
	this.setMap(map);
}

UserInfo.prototype = new google.maps.OverlayView();

UserInfo.prototype.onAdd = function() {
	var div = new YAHOO.util.Element(this.div_);
	div.getElementsByTagName('span')[0].innerHTML = this.user_.name;
	div.getElementsByTagName('span')[1].innerHTML = this.user_.city;
	var img = div.getElementsByTagName('img')[0]
	if(this.user_.img) {
		img.src = this.user_.img;
		img.alt = this.user_.name;
		img.style.display = "block";
	} else {
		img.style.display = "none";
	}
	
	var multi = new YAHOO.util.Element(this.multi_);
	multi.getElementsByTagName('span')[0].innerHTML = this.user_.city;
	multi.getElementsByTagName('span')[1].innerHTML = this.user_.name;
}

UserInfo.prototype.draw = function() {
  var overlayProjection = this.getProjection();
  var pos = overlayProjection.fromLatLngToDivPixel(new google.maps.LatLng(this.user_.lat, this.user_.long));
  var multi = false;
  
  for(var j=0; j<userMapUsers.length; j++) {
		if(this.id_==j) continue;
		user = userMapUsers[j];
		var reference = overlayProjection.fromLatLngToDivPixel(new google.maps.LatLng(user.lat, user.long));
		if(near(pos, reference)) {
			multi = true;
			this.multi_.getElementsByTagName('span')[1].innerHTML += ", " + user.name;
		}
	}

  this.div_ = multi ? this.multi_ : this.div_;

  this.div_.style.left = (pos.x - 100) + 'px';
  this.div_.style.top = (pos.y - 100) + 'px';
  
  this.getPanes().floatPane.appendChild(this.div_);
 }
	

UserInfo.prototype.onRemove = function() {
  this.div_.parentNode.removeChild(this.div_);
  this.div_ = null;
  this.multi_ = null;
}

UserInfo.prototype.remove = function() {
	this.setMap(null);
}

UserInfo.prototype.handleClick = function() {
	if(this.div_ == this.multi_) {
		map.setZoom(15);
		map.panTo(new google.maps.LatLng(this.user_.lat, this.user_.long));
	} else {
		window.open(this.user_.url, '_blank');
	}
}
        </script>

<style>
#usermap {
	/*height: 600px;*/
	position: relative;
}

#locationfinder, #usermap {
	width: 950px;
	margin: 0 auto;
}

#locationfinder {
 margin-top: 10px;
}

#locationfinder .blockbody {
	padding: 5px 0;
}

	/*#locationfinder a {
		border: 1px solid black;
		background: #eee;
		padding: 0 3px;
		text-decoration: none;
		color: black;
	}*/

	#locationfinder input {
		width: 400px;
                margin-left: 5px;
	}

#usermap_map {
	width: 100%;
	height: 100%;
}

#usermap_findinfo {
	top: 37px;
	left: 100px;
	width: 300px;
	height: 50px;
	padding: 5px 5px 5px 60px;
	background: url(http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png) no-repeat 15px center;
	position: relative;
}

#usermap_overlay_single, #usermap_overlay_multi, #usermap_findinfo {
	display: none;
	position: absolute;
	
	overflow: hidden;
	
	background-color: #fff;
	border: #666 solid 1px;
	
	box-shadow: 10px 10px 5px #888;
	-webkit-box-shadow: 3px 3px 5px #000;
	-moz-box-shadow: 10px 10px 5px #888;
}

#usermap_overlay_single, #usermap_overlay_multi {
	width: 200px;
	min-height: 50px;
}

#usermap_overlay_multi {
	padding: 5px;
}

#usermap_findinfo div {
	display: none;
}

#usermap_findinfo a {
	position: absolute;
	bottom: 5px;
	right: 5px;
}

#usermap_findtip {
	color: #aaa;
	font-size: 9px;
	display: block;
}

#usermap_coordinates {
	display: block;
	font-size: 14px;
}

#usermap_avatar {
	float: left;
	background: #fff;
	width: 60px;
	border: none;
}

#usermap_info {
	width: 130px;
	padding: 5px;
	margin-left: 60px;
}

#usermap_info span {
	display: block;
}

#usermap_username, #usermap_city_multi {
	font-weight: bold;
	color: #5aaaff;
}

#usermap_city_multi {
	display: block;
}

#usermap_city {
	font-size: 0.7em;
	color: #222;
}

.blockbody {
 border: 1px solid #5a7f97;
 border-top: none;
}

#usermap .blockbody {
height: 600px;
}
</style>
	<title>{vb:raw vboptions.bbtitle} {vb:rawphrase usermap}</title>
        {vb:raw headinclude_bottom}
</head>
<body>
	{vb:raw header}
	{vb:raw navbar}

<div id="usermap" class="block">
<h2 class="blockhead">{vb:rawphrase usermap}</h2>
<div class="blockbody">
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
			<div id="usermap_overlay_single">
				<img id="usermap_avatar" src="" alt="sadimusi" />
				<div id="usermap_info">
					<span id="usermap_username">sadimusi</span>
					<span id="usermap_city">Biel</span>
				</div>
			</div>
			<div id="usermap_overlay_multi">
				<span id="usermap_city_multi">Biel</span>
				<span id="usermap_usernames">sadimusi</span>
			</div>
		</div>
</div>
		<div id="locationfinder" class="block">
			<h2 class="blockhead">Eigenen Wohnort suchen</h2>
                        <div class="blockbody">
			<input type="text" id="locationfinder_box" class="textbox" onkeypress="keyDownHandler(event)" />
			<a href="#" onclick="return findLocation()" class="textcontrol">Suchen</a>
			<a href="#" onclick="return markHere()" class="textcontrol">Manuell</a>
			</div>
		</div>
 
{vb:raw footer}

</body>
</html>