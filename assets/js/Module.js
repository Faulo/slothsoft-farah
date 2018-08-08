import { loadDocument } from "./DOMHelper";

export async function resolveToDocument(farahUrl) {
	return loadDocument(""+farahUrl);
};