/*******************************************************************************
 * TouchAndClick v1.00 06.10.2014 © Daniel Schulz
 *
 *	Uses touchstart and touchend to simulate click
 * 
 * 	Changelog:
 * 		06.10.2014
 * 			initial release
 ******************************************************************************/

addEventListener(
	"load",
	function(eve) {
		//TouchAndClick.init();
	},
	false
);

var TouchAndClick = {
	init : function() {
		if (window.MutationObserver) {
			try {
				var observer;
				observer = new MutationObserver(
					function(mutationList) {
						for (var i = 0; i < mutationList.length; i++) {
							this._tac.install(mutationList[i].target);
						}
					}
				);
				observer._tac = this;
				observer.observe(
					document.body, {
						childList : true,
						subtree : true,
					}
				);
			} catch(e) {
				this._log(e);
			}
		} else {
			try {
				document.body._tac = this;
				document.body.addEventListener(
					"DOMSubtreeModified",
					function(eve) {
						if (eve.target && eve.target.nodeType === 1) {
							this._tac.install(eve.target);
						}
					},
					false
				);
			} catch(e) {
				this._log(e);
			}
		}
		
		this.install(document);
	},
	install : function(rootNode) {
		var nodeList, node, i, eveName;
		
		try {
			nodeList = rootNode.querySelectorAll("label, button");
			for (i = 0; i < nodeList.length; i++) {
				node = nodeList[i];
				if (!node._tac) {
					node._tac = this;
					
					node.addEventListener(
						"touchstart",
						this.events.touchStart,
						false
					);
					node.addEventListener(
						"touchend",
						this.events.touchEnd,
						false
					);
					node.addEventListener(
						"touchstart",
						function(eve) {
							this.style.setProperty("background-color", "white");
						},
						false
					);
					node.addEventListener(
						"touchend",
						function(eve) {
							this.style.setProperty("background-color", "silver");
						},
						false
					);
					node.addEventListener(
						"click",
						function(eve) {
							this.style.setProperty("background-color", "black");
						},
						false
					);
					/*
					node.addEventListener(
						"mousedown",
						this.events.touchStart,
						false
					);
					node.addEventListener(
						"mouseup",
						this.events.touchEnd,
						false
					);
					//*/
				}
			}
		} catch(e) {
			this._log(e);
		}
	},
	events : {
		touchStart : function(eve) {
			this._tac.startEvent(this, eve);
		},
		touchEnd : function(eve) {
			this._tac.endEvent(this, eve);
		},
	},
	startEvent : function(refNode, eve) {
		try {
			this.setMousePosition(eve.changedTouches[0].clientX, eve.changedTouches[0].clientY);
		} catch(e) {
			alert(e);
		}
	},
	endEvent : function(refNode, eve) {
		try {
			//alert([eve.changedTouches[0].clientX, eve.changedTouches[0].clientY]);
			//alert(this.isMousePosition(eve.changedTouches[0].clientX, eve.changedTouches[0].clientY));
			if (this.isMousePosition(eve.changedTouches[0].clientX, eve.changedTouches[0].clientY)) {
				eve.preventDefault();
				//alert("clicking " + refNode + "...");
				this.dispatchEvent(refNode, "click", eve);
			}
		} catch(e) {
			alert(e);
		}
	},
	mouseX : 0,
	mouseY : 0,
	maxDiff : 2,
	setMousePosition : function(x, y) {
		this.mouseX = x;
		this.mouseY = y;
	},
	isMousePosition : function(x, y) {
		var ret, retX, retY;
		if (x > this.mouseX) {
			retX = x - this.mouseX;
		} else {
			retX = this.mouseX - x;
		}
		if (y > this.mouseY) {
			retY = y - this.mouseY;
		} else {
			retY = this.mouseY - y;
		}
		ret = (retX < this.maxDiff && retY < this.maxDiff);
		//alert([this.mouseX, x, this.mouseY, y, ret]);
		return ret;
	},
	cleraMousePosition : function() {
		this.mouseX = 0;
		this.mouseY = 0;
	},
	dispatchEvent : function(targetNode, eventType, refEvent) {
		try {
			var newEvent, details, key;
			details = {};
			for (key in refEvent) {
				switch (typeof refEvent[key]) {
					case "string":
					case "boolean":
					case "number":
						details[key] = refEvent[key];
						break;
					case "function":
					case "object":
					case "undefined":
						break;
					default:
						alert(typeof refEvent[key]);
						break;
				}
			}
			
			newEvent = new CustomEvent(eventType, details);
			
			if (targetNode) {
				targetNode.dispatchEvent(newEvent);
				//targetNode.focus();
			}
		} catch(e) {
			alert(e);
		}
	},
	_log : function(message) {
		//alert(message);
		window.console.log(message);
	},
};