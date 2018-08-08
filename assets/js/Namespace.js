const list = [];

export function define(name, prefix, uri) {
	list.push({name, prefix, uri});
}
export function byName(name) {
	for (let i = 0; i < list.length; i++) {
		if (list[i].name == name) {
			return list[i];
		}
	}
}
export function byPrefix(prefix) {
	for (let i = 0; i < list.length; i++) {
		if (list[i].prefix == prefix) {
			return list[i];
		}
	}
}
export function byUri(uri) {
	for (let i = 0; i < list.length; i++) {
		if (list[i].uri == uri) {
			return list[i];
		}
	}
}
export function resolve(prefix) {
	return byPrefix(prefix).uri;
}
export function prefix(uri) {
	return byUri(uri).prefix;
}

define("XML", "xml", "http://www.w3.org/XML/1998/namespace");
define("HTML", "html", "http://www.w3.org/1999/xhtml");
define("MOZILLA_ERROR", "me", "http://www.mozilla.org/newlayout/xml/parsererror.xml");
define("MOZILLA_XUL", "mx", "http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul");

define("AMBER_AMBERDATA", "saa", "http://schema.slothsoft.net/amber/amberdata");
define("FARAH_MODULE", "sfm", "http://schema.slothsoft.net/farah/module");