/*******************************************************************************
 * XSLT v1.01 08.04.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 08.04.2014
 *			console.log
 * 		v1.00 12.09.2012
 * 			initial release
 ******************************************************************************/

var XSLT = {
	transformToFragment : function(dataNode, templateDoc, ownerDoc) {
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
			} catch(e) {
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
		} catch(e) {
			console.log("An error occured while attempting to XSL transform. :|");
			console.log("Data node:%o", dataNode);
			console.log("Template document:%o", templateDoc);
			console.log("Owner document:%o", ownerDoc);
			console.log("Exception:%o", e);
			retFragment = ownerDoc.createDocumentFragment();
		}
		return retFragment;
	},
	transformToNode : function(dataNode, templateDoc, ownerDoc) {
		return this.transformToFragment(dataNode, templateDoc, ownerDoc).firstChild;
	},
};