var jsonFileURL = "data.json";

var userMapUsers, map, mapOverlay, mapOverlayDiv, selectedMarker;
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
	getUserMapUsers();
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
    mapOverlayDiv = document.getElementById('usermap_overlay');
    mapOverlayDiv.parentNode.removeChild(mapOverlayDiv);
    mapOverlayDiv.style.display = "block";
}

function getUserMapUsers() {
	var request = YAHOO.util.Connect.asyncRequest('GET', jsonFileURL, {success: function(o) {
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
		window.open(user.url, '_blank');
	});
	markers.push(marker);
}

function showUserInfo(i) {
	mapOverlay = new UserInfo(i);
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

function savePosition() {
	var lat = selectedMarker.getPosition().lat();
	var lng = selectedMarker.getPosition().lng();
	var address = selectedMarker.getTitle();
	alert(lat + "\n" + lng + "\n" + address);
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
}

UserInfo.prototype.draw = function() {
  var overlayProjection = this.getProjection();
  var pos = overlayProjection.fromLatLngToDivPixel(new google.maps.LatLng(this.user_.lat, this.user_.long));

  this.div_.style.left = (pos.x - 100) + 'px';
  this.div_.style.top = (pos.y - 100) + 'px';
  
  this.getPanes().floatPane.appendChild(this.div_);
}

UserInfo.prototype.onRemove = function() {
  this.div_.parentNode.removeChild(this.div_);
  this.div_ = null;
}

UserInfo.prototype.remove = function() {
	this.setMap(null);
}