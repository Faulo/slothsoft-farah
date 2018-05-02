/*******************************************************************************
 * DOMHelper v1.01 08.04.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 08.04.2014
 *			console.log
 * 		v1.00 12.09.2012
 * 			initial release
 ******************************************************************************/

class DOMHelper {
	static loadDocument(uri, callback) {
		fetch(uri)
			.then((response) => response.text())
			.then((text) => new DOMParser().parseFromString(text, "application/xml"))
			.then((document) => {
				document.fileURI = uri;
				if (callback) {
					callback(document);
				}
			})
			.catch((e) => {
				console.log(`Could not load XML ressource "${uri}"`);
				console.log(e);
			});
	}
	/*
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
	},
	//*/
}