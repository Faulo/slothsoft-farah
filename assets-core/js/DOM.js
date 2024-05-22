/*******************************************************************************
 * DOM v1.01 08.04.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 08.04.2014
 *			console.log
 * 		v1.00 12.09.2012
 * 			initial release
 ******************************************************************************/

var DOM = {
	loadDocument : function(uri) {
		var retDoc, req;
		try {
			req = new XMLHttpRequest();
			req.open("GET", uri, false);
			if (req.overrideMimeType) {
				//req.overrideMimeType("application/xml; charset=UTF-8");
			}
			req.send();
			if (req.responseXML) {
				retDoc = req.responseXML;
			} else {
				//retDoc = this.loadXML(req.responseText);
				throw req.responseText;
			}
			retDoc.fileURI = uri;
		} catch (e) {
			console.log("Could not load XML ressource: %o", uri);
			console.log("Exception:%o", e);
			retDoc = false;
		}
		return retDoc;
	},
	saveDocument : function(uri, doc) {
		var ret, req;
		try {
			req = new XMLHttpRequest();
			req.open("POST", uri, false);
			//req.setRequestHeader("Content-Type", "application/xml");
			req.send(doc);
			ret = req.responseXML;
		} catch (e) {
			console.log("Could not save XML ressource: %o", uri);
			console.log("Exception:%o", e);
			ret = false;
		}
		return ret;
	},
	loadXML : function(xml) {
		var doc, parser;
		parser = new DOMParser();
		doc = parser.parseFromString(xml, "application/xml");
		if (doc.documentElement.namespaceURI === NS.MOZ_ERR_PARSE) {
			throw ""+doc.documentElement.textContent;
		}
		return doc;
	},
	saveXML : function(doc) {
		var xml, serializer;
		serializer = new XMLSerializer();
		xml = serializer.serializeToString(doc);
		return xml;
	}
};