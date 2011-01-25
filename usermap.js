var jsonFileURL = "data.json";

var userMapUsers, map, mapOverlay, mapOverlayDiv;
var mapReady = false;
var mapDataReady = false;

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
		var y = i*n;
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