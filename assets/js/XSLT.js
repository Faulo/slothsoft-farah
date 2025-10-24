/*******************************************************************************
 * XSLT v1.01 08.04.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 08.04.2014
 *			console.log
 * 		v1.00 12.09.2012
 * 			initial release
 ******************************************************************************/

import DOM from "./DOM";
import { NS } from "./XMLNamespaces";

function coerceToNode(data) {
    if (data instanceof Node) {
        return data;
    }

    return DOM.loadDocument(data);
}

async function coerceToNodeAsync(data) {
    if (data instanceof Node) {
        return data;
    }

    return await DOM.loadDocumentAsync(data);
}

function coerceToDocument(data) {
    if (data instanceof Document) {
        return data;
    }

    return DOM.loadDocument(data);
}

async function coerceToDocumentAsync(data) {
    if (data instanceof Document) {
        return data;
    }

    return DOM.loadDocumentAsync(data);
}

function getNamespaces(element) {
    const namespaces = {};
    for (const attr of element.attributes) {
        if (attr.prefix == "xmlns") {
            namespaces[attr.name] = attr.value;
        }
    }
    return namespaces;
}

function transform(dataNode, templateDoc, ownerDoc) {
    try {
        const xslt = new XSLTProcessor();
        xslt.importStylesheet(templateDoc);
        const retFragment = xslt.transformToFragment(dataNode, ownerDoc);

        if (!retFragment) {
            throw new Error("XSLTProcessor.transformToFragment returned null!");
        }

        // Firefox adds a useless <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> (even if one is already present), remove it
        var metaElements = retFragment.querySelectorAll('html > head > meta[http-equiv="Content-Type"][content="text/html; charset=UTF-8"]');
        if (metaElements.length > 0) {
            metaElements[metaElements.length - 1].parentNode.removeChild(metaElements[metaElements.length - 1]);
        }

        return retFragment;
    } catch (e) {
        console.warn("XSLT Error: An error occured while attempting to transform.");
        console.log("Data node:%o", dataNode);
        console.log("Template document:%o", templateDoc);
        console.log("Owner document:%o", ownerDoc);
        throw e;
    }
}

export default {
    transformToFragment: function(data, template, ownerDoc) {
        const dataNode = coerceToNode(data);
        const templateDoc = coerceToDocument(template);

        try {
            const imported = new Set();
            for (let importNode; importNode = templateDoc.querySelector(":root > import[href]");) {
                const uri = importNode.getAttribute("href");
                if (!imported.has(uri)) {
                    imported.add(uri);
                    const tmpDoc = DOM.loadDocument(uri);
                    const namespaces = getNamespaces(tmpDoc.documentElement);
                    const nodeList = tmpDoc.querySelectorAll(":root > *");
                    for (let i = 0; i < nodeList.length; i++) {
                        const templateNode = templateDoc.importNode(nodeList[i], true);
                        for (const prefix in namespaces) {
                            if (!templateNode.hasAttributeNS(NS.XMLNS, prefix)) {
                                templateNode.setAttributeNS(NS.XMLNS, prefix, namespaces[prefix]);
                            }
                        }
                        importNode.parentNode.insertBefore(templateNode, importNode);
                    }
                }
                importNode.parentNode.removeChild(importNode);
            }
        } catch (e) {
            console.warn("XSLT Error: Could not process all xsl:import elements.");
            throw e;
        }

        return transform(dataNode, templateDoc, ownerDoc);
    },
    transformToFragmentAsync: async function(data, template, ownerDoc) {
        const [dataNode, templateDoc] = await Promise.all([coerceToNodeAsync(data), coerceToDocumentAsync(template)]);

        try {
            const imported = new Set();
            for (let importNode; importNode = templateDoc.querySelector(":root > import[href]");) {
                const uri = importNode.getAttribute("href");
                if (!imported.has(uri)) {
                    imported.add(uri);
                    const tmpDoc = await DOM.loadDocumentAsync(uri);
                    const namespaces = getNamespaces(tmpDoc.documentElement);
                    const nodeList = tmpDoc.querySelectorAll(":root > *");
                    for (let i = 0; i < nodeList.length; i++) {
                        const templateNode = templateDoc.importNode(nodeList[i], true);
                        for (const prefix in namespaces) {
                            if (!templateNode.hasAttributeNS(NS.XMLNS, prefix)) {
                                templateNode.setAttributeNS(NS.XMLNS, prefix, namespaces[prefix]);
                            }
                        }
                        importNode.parentNode.insertBefore(templateNode, importNode);
                    }
                }
                importNode.parentNode.removeChild(importNode);
            }
        } catch (e) {
            console.warn("XSLT Error: Could not process all xsl:import elements.");
            throw e;
        }

        return transform(dataNode, templateDoc, ownerDoc);
    },
    transformToNode: function(dataNode, templateDoc, ownerDoc) {
        return this.transformToFragment(dataNode, templateDoc, ownerDoc).firstChild;
    },
};