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
		if (uri.startsWith("farah://")) {
			uri = "/getAsset.php/" + uri.substring("farah://".length);
		}
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
	static transformToFragment(dataNode, templateDocument, ownerDocument) {
		let promise;
		let importNode = templateDocument.getElementsByTagNameNS(NS.XSL, "import")[0];
		if (importNode) {
			promise = DOMHelper.loadDocument(importNode.getAttribute("href"))
				.then((importDocument) => {
					[...importDocument.documentElement.children].forEach(
						(childNode) => {
							importNode.parentNode.appendChild(templateDocument.adoptNode(childNode));
						}
					);
					importNode.parentNode.removeChild(importNode);
					return DOMHelper.transformToFragment(dataNode, templateDocument, ownerDocument);
				});
		} else {
			promise = Promise.resolve()
				.then(() => {
					let xslt = new XSLTProcessor();
					xslt.importStylesheet(templateDocument);
					return xslt.transformToFragment(dataNode, ownerDocument);
				});
		}
		return promise.catch((e) => {
			console.log(`Could not transform XML ressource "${dataNode}".`);
			console.log(e);
		});
	}
}