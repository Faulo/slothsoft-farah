/*
var _html5 = function() {
	try {
		//http://caniuse.com/#feat=details
		if (!('open' in document.createElement('details'))) {
			var nodeList, node, i;
			
			
			//wait until document is actually ready...
			node = document.createTextNode("");
			document.body.appendChild(node);
			document.body.removeChild(node);
			
			nodeList = document.getElementsByTagName("summary");
			if (nodeList.length) {
				console.log("installing HTML5 <details> functionality on " + nodeList.length + " elements...");
			}
			for (i = 0; i < nodeList.length; i++) {
				node = nodeList[i];
				if (!node._init) {
					node._init = true;
					node.addEventListener(
						"click",
						function(eve) {
							if (this.parentNode.hasAttribute("open")) {
								this.parentNode.removeAttribute("open");
							} else {
								this.parentNode.setAttribute("open", "open");
							}
							
							if (this.parentNode.hasAttribute("open")) {
								//this.nextSibling.style.removeProperty("display");
							} else {
								//this.nextSibling.style.setProperty("display", "none");
							}
						},
						false
					);
					if (!node.parentNode.hasAttribute("open")) {
						//node.nextSibling.style.setProperty("display", "none");
					}
				}
			}
		}
	} catch(e) {
	}
	try {
		if (window.location.hash) {
			var node, parentNode, id;
			id = window.location.hash.substr(1);
			if (node = document.getElementById(id)) {
				do {
					if (node.tagName === "details" && !node.hasAttribute("open")) {
						node.setAttribute("open", "open");
					}
				} while (node = node.parentNode);
			}
		}
	} catch(e) {
	}
};

_html5.call();

window.addEventListener(
	"DOMContentLoaded",
	_html5,
	false
);
window.addEventListener(
	"load",
	_html5,
	false
);
//*/