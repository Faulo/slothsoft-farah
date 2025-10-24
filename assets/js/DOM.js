/*******************************************************************************
 *  DOM © Daniel Schulz
 * 
 * 	Changelog:
 *      v1.1.0 23.10.2025
 *          loadDocumentAsync
 *		v1.0.1 08.04.2014
 *			console.log
 * 		v1.0.0 12.09.2012
 * 			initial release
 ******************************************************************************/

import { NS } from "./XMLNamespaces";

function parseUrl(uri) {
    return uri.startsWith("farah://")
        ? uri.substring("farah:/".length)
        : uri;
}

function parseContentType(contentType) {
    const mimeType = String(contentType)
        .split(';', 1)[0]
        .trim()
        .toLowerCase();

    switch (mimeType) {
        case "application/xslt+xml": return "application/xml";
        default: return mimeType;
    }
}

export default {
    loadDocument: function(uri) {
        const request = new XMLHttpRequest();
        request.open("GET", parseUrl(uri), false);
        request.send();

        if (request.status >= 400) {
            throw new Error(`Failed to load: ${uri}\n  ${request.status} ${request.statusText}`);
        }

        if (request.responseXML) {
            return request.responseXML;
        }

        const content = request.responseText;
        const mimeType = parseContentType(request.getResponseHeader("content-type"));

        return mimeType
            ? this.loadXML(content, mimeType)
            : this.loadXML(content);
    },
    loadDocumentAsync: async function(uri) {
        const response = await fetch(parseUrl(uri));

        if (!response.ok) {
            throw new Error(`Failed to load: ${uri}\n  ${response.status} ${response.statusText}`);
        }

        const content = await response.text();
        const mimeType = parseContentType(response.headers.get("content-type"));

        return mimeType
            ? this.loadXML(content, mimeType)
            : this.loadXML(content);
    },
    saveDocument: function(uri, doc) {
        const request = new XMLHttpRequest();
        request.open("POST", parseUrl(uri), false);
        request.send(doc);

        if (request.status >= 400) {
            throw new Error(`Failed to save: ${uri}\n  ${request.status} ${request.statusText}`);
        }

        return request.responseXML;
    },
    loadXML: function(xml, mimeType = "application/xml") {
        const parser = new DOMParser();
        const doc = parser.parseFromString(xml, mimeType);

        if (doc.documentElement.namespaceURI === NS.MOZ_ERR_PARSE) {
            throw new Error(doc.documentElement.textContent);
        }

        return doc;
    },
    saveXML: function(doc) {
        const serializer = new XMLSerializer();
        const xml = serializer.serializeToString(doc);
        return xml;
    }
};