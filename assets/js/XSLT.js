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

function coerceToDocument(data) {
    if (data instanceof Document) {
        return data;
    }

    return DOM.loadDocument(data);
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
            console.log("XSLT Error: could not process xsl:import elements");
            throw e;
        }

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
            console.warn("An error occured while attempting to XSL transform. :|");
            console.log("Data node:%o", dataNode);
            console.log("Template document:%o", templateDoc);
            console.log("Owner document:%o", ownerDoc);
            throw e;
        }
        /*
        
        
        
        
        var xslt,
            uri, tmpDoc, nodeList, node, i,
            retFragment = false;
        try {
            //xsl:import parsen, für Chrome+Safari
            try {
                while (node = XPath.evaluate("//xsl:import", templateDoc)[0]) {
                    uri = node.getAttribute("href");
                    if (uri.startsWith("farah://")) {
                        uri = "/" + uri.substring("farah://".length);
                    }
                    if (tmpDoc = DOM.loadDocument(uri)) {
                        nodeList = XPath.evaluate("/xsl:stylesheet/*", tmpDoc);
                        for (i = 0; i < nodeList.length; i++) {
                            node.parentNode.appendChild(templateDoc.importNode(nodeList[i], true));
                        }
                    } else {
                        console.log("XSLT Error: could not load xsl:import " + uri);
                    }
                    node.parentNode.removeChild(node);
                }
            } catch (e) {
                console.log("XSLT Error: could not process xsl:import elements");
                console.log(e);
            }
            if (window.XSLTProcessor) {
                xslt = new XSLTProcessor();
                xslt.importStylesheet(templateDoc);
                retFragment = xslt.transformToFragment(dataNode, ownerDoc);
                if (!retFragment) {
                    throw "XSLTProcessor.transformToFragment returned null!";
                }
            } else if (window.ActiveXObject) {
                xslt = new ActiveXObject("Msxml2.XSLTemplate.6.0");
                //var xmlDoc = new ActiveXObject("Msxml2.DOMDocument");
                //var xslDoc = new ActiveXObject("Msxml2.FreeThreadedDOMDocument");
                xslt.stylesheet = templateDoc;
                xslt = xslt.createProcessor();
                xslt.input = dataNode;
                xslt.transform();
                retFragment = ownerDoc.importNode(xslProc.output, true);
                if (!retFragment) {
                    throw "Msxml2.XSLTemplate.transform returned null!";
                }
            }
        } catch (e) {
            console.log("An error occured while attempting to XSL transform. :|");
            console.log("Data node:%o", dataNode);
            console.log("Template document:%o", templateDoc);
            console.log("Owner document:%o", ownerDoc);
            console.log("Exception:%o", e);
            retFragment = ownerDoc.createDocumentFragment();
        }
        return retFragment;
        //*/
    },
    transformToNode: function(dataNode, templateDoc, ownerDoc) {
        return this.transformToFragment(dataNode, templateDoc, ownerDoc).firstChild;
    },
};