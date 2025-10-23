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

var DOM = {
    loadDocument: function(uri) {
        const request = new XMLHttpRequest();
        request.open("GET", uri, false);
        request.send();

        if (request.responseXML) {
            return request.responseXML;
        }

        if (request.status >= 400) {
            throw new Error(`Failed to query: ${uri}\n  ${request.status} ${request.statusText}`);
        }

        const content = request.responseText;
        const mimeType = this.parseContentType(request.getResponseHeader("content-type"));

        return mimeType
            ? this.loadXML(content, mimeType)
            : this.loadXML(content);
    },
    loadDocumentAsync: async function(uri) {
        const response = await fetch(uri);

        if (!response.ok) {
            throw new Error(`Failed to query: ${uri}\n  ${response.status} ${response.statusText}`);
        }

        const content = await response.text();
        const mimeType = this.parseContentType(response.headers.get("content-type"));

        return mimeType
            ? this.loadXML(content, mimeType)
            : this.loadXML(content);
    },
    parseContentType: function(contentType) {
        return String(contentType)
            .split(';', 1)[0]
            .trim()
            .toLowerCase();
    },
    saveDocument: function(uri, doc) {
        const request = new XMLHttpRequest();
        request.open("POST", uri, false);
        request.send(doc);
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