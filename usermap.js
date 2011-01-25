var jsonFileURL = "data.json";

var userMapUsers, map;
var mapReady = false;
var mapDataReady = false;

YAHOO.util.Event.onDOMReady(function() {
	initUserMap();
	getUserMapUsers();
});

function initUserMap() { 
    var options = {
      zoom: 8,
      center: new google.maps.LatLng(47, 7),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("usermap"), options);
    google.maps.event.addListener(map, 'idle', function() {
    	if(!mapReady) {
    		mapReady = true;
    		tryUserLoading();
    	}
    });
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
		window.setTimeout("showUsers(" + x + "," + y + ")", i*50);
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
	new google.maps.Marker({
 		position: new google.maps.LatLng(user.lat, user.long), 
  		map: map, 
  		animation: google.maps.Animation.DROP,
  		title:user.name
	}); 
}