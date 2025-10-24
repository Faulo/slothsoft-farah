const list = [];

export const NS = {};

export function define(name, prefix, uri) {
    list.push({ name, prefix, uri });
    NS[name] = uri;
}
export function byName(name) {
    for (let i = 0; i < list.length; i++) {
        if (list[i].name == name) {
            return list[i];
        }
    }
    throw new Error(`Namespace name "${name}" is not known to this implementation.`);
}
export function byPrefix(prefix) {
    for (let i = 0; i < list.length; i++) {
        if (list[i].prefix == prefix) {
            return list[i];
        }
    }
    throw new Error(`Namespace prefix "${prefix}" is not known to this implementation.`);
}
export function byUri(uri) {
    for (let i = 0; i < list.length; i++) {
        if (list[i].uri == uri) {
            return list[i];
        }
    }
    throw new Error(`Namespace URI "${uri}" is not known to this implementation.`);
}
export function resolve(prefix) {
    return byPrefix(prefix).uri;
}
export function prefix(uri) {
    return byUri(uri).prefix;
}

//W3C
define("XML", "xml", "http://www.w3.org/XML/1998/namespace");
define("XMLNS", "xmlns", "http://www.w3.org/2000/xmlns/");
define("HTML", "html", "http://www.w3.org/1999/xhtml");
define("XSLT", "xsl", "http://www.w3.org/1999/XSL/Transform");
define("XSD", "xsd", "http://www.w3.org/2001/XMLSchema");
define("SVG", "svg", "http://www.w3.org/2000/svg");
define("XLINK", "xlink", "http://www.w3.org/1999/xlink");
define("ATOM", "atom", "http://www.w3.org/2005/Atom");

//Slothsoft
define("AMBER_AMBERDATA", "saa", "http://schema.slothsoft.net/amber/amberdata");
define("CRON_INSTRUCTIONS", "sci", "http://schema.slothsoft.net/cron/instructions");
define("FARAH_MODULE", "sfm", "http://schema.slothsoft.net/farah/module");
define("FARAH_DICTIONARY", "sfd", "http://schema.slothsoft.net/farah/dictionary");
define("FARAH_SITEMAP", "sfs", "http://schema.slothsoft.net/farah/sitemap");
define("SAVEGAME_EDITOR", "sse", "http://schema.slothsoft.net/savegame/editor");
define("SCHEMA_VERSIONING", "ssv", "http://schema.slothsoft.net/schema/versioning");

//Misc
define("PHP", "php", "http://php.net/xpath");
define("SITEMAP", "sitemap", "http://www.sitemaps.org/schemas/sitemap/0.9");
define("MOZILLA_ERROR", "me", "http://www.mozilla.org/newlayout/xml/parsererror.xml");
define("MOZILLA_XUL", "mx", "http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul");