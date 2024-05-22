// © 2012 Daniel Schulz

var CMS = {
	requestDocument : function(url) {
		var req = new XMLHttpRequest();
		req.open("GET", url, false);
		req.send();
		return req.responseXML;
	},
};