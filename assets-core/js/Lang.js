// © 2013 Daniel Schulz

var Lang = {
	registryURI : "/getResource.php/core/language-registry",
	registryDoc : undefined,
	init : function() {
		this.registryDoc = DOM.loadDocument(this.registryURI);
		alert(this.lookup("de-de"));
	},
	lookup : function(lang) {
		var ret, data;
		data = this.parse(lang);
		ret = data.source;
		if (data.language) {
			ret = XPath.evaluate("string(/registry/language[subtag = '" + data.language + "']/description)", this.registryDoc);
			if (data.region) {
				ret += XPath.evaluate("concat(' (', /registry/region[subtag = '" + data.region + "']/description, ')')", this.registryDoc);;
			}
		}
		return ret;
	},
	parse : function(lang) {
		var ret = {};
		ret.source = lang;
		lang = lang.split("-");
		if (lang[0]) {
			ret.language = lang[0].toLowerCase();
		}
		if (lang[1]) {
			ret.region = lang[1].toUpperCase();
		}
		return ret;
	},
};
addEventListener(
	"load",
	function(eve) {
		Lang.init();
	},
	false
);