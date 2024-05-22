/*******************************************************************************
 * XPath v1.01 08.04.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 08.04.2014
 *			window.wgxpath
 * 		v1.00 02.08.2013
 * 			initial release
 ******************************************************************************/

var XPath = {
	evaluate : function(query, contextNode) {
		var ownerDoc, res, ret, tmp, isTextNode;
		if (contextNode instanceof Array) {
			ret = [];
			for (var i = 0; i < contextNode.length; i++) {
				res = this.evaluate(query, contextNode[i]);
				if (res instanceof Array) {
					ret = ret.concat(res);
				} else {
					ret.push(res);
				}
			}
			return ret;
		}
		try {
			ownerDoc = contextNode.nodeType === Node.DOCUMENT_NODE
				? contextNode
				: contextNode.ownerDocument;
			//use wgxpath
			if (window.wgxpath) {
				if (!window.document.evaluate) {
					window.wgxpath.install(window);
				}
				if (!ownerDoc.evaluate) {
					window.wgxpath.install( { document : ownerDoc } );
				}
			}
			//alert(ownerDoc + "\n" + ownerDoc.evaluate + "\n" + ownerDoc.selectNodes);
			isTextNode = false;
			if (contextNode.nodeType === Node.TEXT_NODE) {
				if (contextNode.nodeValue === "") {
					contextNode.nodeValue = " ";
					isTextNode = true;
				}
			}
			res = ownerDoc.evaluate(query, contextNode, NS.resolver, XPathResult.ANY_TYPE, null);
			switch (res.resultType) {
				case XPathResult.NUMBER_TYPE:
					ret = res.numberValue;
					break;
				case XPathResult.STRING_TYPE:
					ret = res.stringValue;
					break;
				case XPathResult.BOOLEAN_TYPE:
					ret = res.booleanValue;
					break;
				default:
					ret = [];
					while (tmp = res.iterateNext()) {
						ret.push(tmp);
					}
					break;
			}
			//window.console.log("XPath result type: %o (%o)", res.resultType, typeof ret);
			if (isTextNode) {
				contextNode.nodeValue = "";
			}
			return ret;
		} catch(e) {
			window.console.log("XPath error!");
			window.console.log("Query:%o", query);
			window.console.log("Context node:%o", contextNode);
			window.console.log("Exception:%o", e);
			throw e;
		}
	},
	createPath : function(contextNode) {
		var node, nodeList, i, ret = [""], tag, precedingList, followingList;
		nodeList = this.evaluate("ancestor-or-self::node()", contextNode);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			switch (node.nodeType) {
				case node.TEXT_NODE:
					tag = "text()";
					break;
				case node.ELEMENT_NODE:
					tag = NS.prefixer(node.namespaceURI) + ":" + node.localName;
					break;
				default:
					tag = false;
					break;
			}
			if (tag) {
				precedingList = XPath.evaluate("preceding-sibling::" + tag, node);
				followingList = XPath.evaluate("following-sibling::" + tag, node);
				if (precedingList.length + followingList.length) {
					tag += "[" + (precedingList.length + 1) + "]";
				}
				ret.push(tag);
			}
		}
		return ret.join("/");
	},
};