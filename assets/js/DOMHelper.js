
import * as Namespace from "./Namespace";

export async function loadDocument(uri) {
	try {
		if (uri.startsWith("farah://")) {
			uri = "/getAsset.php/" + uri.substring("farah://".length);
		}
		const response = await fetch(uri);
		const text = await response.text();
		const document = await parse(text);
		document.fileURI = uri;
		return document;
	} catch(e) {
		console.log(`Could not load XML ressource "${uri}"`);
		console.log(e);
	}
}
export async function parse(xml) {
	const document = await parser.parseFromString(xml, "application/xml");
	if (document.documentElement.namespaceURI === Namespace.byName("MOZILLA_ERROR").uri) {
		throw new Error(""+document.documentElement.textContent);
	}
	return document;
}
export async function stringify(document) {
	return await serializer.serializeToString(document);
}
export async function transformToFragment() {
	return "transformation";
}
export async function evaluate(query, contextNode = document) {
	try {
		const ownerDocument = contextNode.nodeType === Node.DOCUMENT_NODE
			? contextNode
			: contextNode.ownerDocument;
		const result = ownerDocument.evaluate(query, contextNode, Namespace.resolve, XPathResult.ANY_TYPE, null);
		
		switch (result.resultType) {
			case XPathResult.NUMBER_TYPE:
				return result.numberValue;
			case XPathResult.STRING_TYPE:
				return result.stringValue;
			case XPathResult.BOOLEAN_TYPE:
				return result.booleanValue;
			default:
				let ret = [];
				let tmp;
				while (tmp = result.iterateNext()) {
					ret.push(tmp);
				}
				return ret;
		}
	} catch(e) {
		window.console.log("XPath error!");
		window.console.log("query:%o", query);
		window.console.log("context node:%o", contextNode);
		window.console.log("exception:%o", e);
		throw e;
	}
}
export const parser = new DOMParser();
export const serializer = new XMLSerializer();