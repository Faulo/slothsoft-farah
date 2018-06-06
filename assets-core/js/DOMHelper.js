/*******************************************************************************
 * DOMHelper v1.02 02.05.2018 © Daniel Schulz
 * 
 * 	Changelog:
 * 		v1.02 02.05.2018
 * 			class syntax
 * 			fetch API
 * 			Promises
 *		v1.01 08.04.2014
 *			console.log
 * 		v1.00 12.09.2012
 * 			initial release
 ******************************************************************************/

class DOMHelper {
	static loadDocument(uri) {
		return fetch(uri)
			.then((response) => response.text())
			.then(DOMHelper.parse)
			.then((document) => {
				document.fileURI = uri;
				return document;
			})
			.catch((e) => {
				console.log(`Could not load XML ressource "${uri}"`);
				console.log(e);
			});
	}
	static saveDocument(uri, doc) {
		return Promise.resolve()
			.then(() => doc)
			.then(DOMHelper.stringify)
			.then((xml) => new Request(uri, {method: 'POST', body: xml}))
			.then(fetch)
			.catch((e) => {
				console.log(`Could not save XML ressource "${uri}"`);
				console.log(e);
			});
	}
	static parse(xml) {
		return Promise.resolve()
			.then(() => new DOMParser().parseFromString(xml, "application/xml"))
			.then(document => {
				if (document.documentElement.namespaceURI === NS.MOZ_ERR_PARSE) {
					throw new Error(""+document.documentElement.textContent);
				}
				return document;
			});
	}
	static stringify(document) {
		return Promise.resolve()
			.then(() => new XMLSerializer().serializeToString(document));
	}
}