// © 2013 Daniel Schulz

var NS = {
	XML				: "http://www.w3.org/XML/1998/namespace",
	HTML 			: "http://www.w3.org/1999/xhtml",
	SVG 			: "http://www.w3.org/2000/svg",
	XLINK 			: "http://www.w3.org/1999/xlink",
	XSL 			: "http://www.w3.org/1999/XSL/Transform",
	MOZ_XUL			: "http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul",
	MOZ_ERR_PARSE 	: "http://www.mozilla.org/newlayout/xml/parsererror.xml",
	IBP				: "http://www.ibp-dresden.de",
	LLO				: "http://slothsoft.net",
	resolver : function(prefix) {
		return NS[prefix.toUpperCase()];
	},
	prefixer : function(uri) {
		var prefix, ret = "";
		for (prefix in NS) {
			if (NS[prefix] === uri) {
				ret = prefix;
				break;
			}
		}
		return ret.toLowerCase();
	},
};