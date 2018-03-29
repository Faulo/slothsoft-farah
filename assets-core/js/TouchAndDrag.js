/*******************************************************************************
 * TouchAndDrag v1.00 24.09.2014 © Daniel Schulz
 *
 *	Uses touchstart, touchend and touchmove to simulate dragstart, dragend and dragmove
 *	Requires XPath
 * 
 * 	Changelog:
 * 		v1.00 24.09.2014
 * 			initial release
 ******************************************************************************/

addEventListener(
	"load",
	function(eve) {
		TouchAndDrag.init();
	},
	false
);

var TouchAndDrag = {
	init : function() {
		if (window.MutationObserver) {
			try {
				var observer;
				observer = new MutationObserver(
					function(mutationList) {
						for (var i = 0; i < mutationList.length; i++) {
							this._tad.install(mutationList[i].target);
						}
					}
				);
				observer._tad = this;
				observer.observe(
					document.documentElement, {
						childList : true,
						subtree : true,
					}
				);
			} catch(e) {
				this._log(e);
			}
		} else {
			try {
				document.documentElement._tad = this;
				document.documentElement.addEventListener(
					"DOMSubtreeModified",
					function(eve) {
						if (eve.target && eve.target.nodeType === 1) {
							this._tad.install(eve.target);
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
			nodeList = XPath.evaluate(".//html:*[@draggable]", rootNode);
			for (i = 0; i < nodeList.length; i++) {
				node = nodeList[i];
				if (!node._tad) {
					node._tad = this;
					
					node.addEventListener(
						"touchstart",
						this.events.draggable.touchStart,
						false
					);
					node.addEventListener(
						"touchend",
						this.events.draggable.touchEnd,
						false
					);
					node.addEventListener(
						"touchmove",
						this.events.draggable.touchMove,
						false
					);
				}
			}
		} catch(e) {
			this._log(e);
		}
		
		/*
		nodeList = XPath.evaluate("//html:*[@dropzone]", document);
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node._tad = this;
			
			node.addEventListener(
				"touch",
				function(eve) {
					try {
						eve.preventDefault();
					} catch(e) {
						alert(e);
					}
				},
				false
			);
		}
		//*/
	},
	events : {
		draggable : {
			touchStart : function(eve) {
				this._tad.startEvent(this, eve);
			},
			touchEnd : function(eve) {
				this._tad.endEvent(this, eve);
			},
			touchMove : function(eve) {
				this._tad.moveEvent(this, eve);
			},
		},
		dropzone : {
		},
	},
	startEvent : function(refNode, eve) {
		try {
			var touchList, touch, i, id;
			touchList = eve.changedTouches;
			for (i = 0; i < touchList.length; i++) {
				touch = touchList[i];
				id = touch.identifier;
				
				eve.preventDefault();
				
				this.dispatchDragEvent(refNode, "dragstart", eve, this.getDataTransfer(id));
			}
		} catch(e) {
			alert(e);
		}
	},
	endEvent : function(refNode, eve) {
		try {
			var touchList, touch, i, id;
			touchList = eve.changedTouches;
			for (i = 0; i < touchList.length; i++) {
				touch = touchList[i];
				id = touch.identifier;
				
				eve.preventDefault();
				
				this.dispatchDragEvent(document.elementFromPoint(touch.clientX, touch.clientY), "drop", eve, this.getDataTransfer(id));
				this.dispatchDragEvent(refNode, "dragend", eve, this.getDataTransfer(id));
				this.clearDataTransfer(id);
			}
		} catch(e) {
			alert(e);
		}
	},
	moveEvent : function(refNode, eve) {
		try {
			var touchList, touch, i, id;
			touchList = eve.changedTouches;
			for (i = 0; i < touchList.length; i++) {
				touch = touchList[i];
				id = touch.identifier;
				if (this.hasDataTransfer(id)) {
					eve.preventDefault();
					
					this.dispatchDragEvent(document.elementFromPoint(touch.clientX, touch.clientY), "dragover", eve, this.getDataTransfer(id));
				}
			}
		} catch(e) {
			alert(e);
		}
	},
	dispatchDragEvent : function(targetNode, eventType, refEvent, dataTransfer) {
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
			
			if (!newEvent.dataTransfer && dataTransfer) {
				newEvent.dataTransfer = dataTransfer;
			}
			
			if (targetNode) {
				targetNode.dispatchEvent(newEvent);
				//targetNode.focus();
			}
		} catch(e) {
			alert(e);
		}
	},
	dataList : {},
	getDataTransfer : function(id) {
		id += "";
		if (!this.dataList[id]) {
			this.dataList[id] = new CustomDataTransfer();
		}
		return this.dataList[id];
	},
	hasDataTransfer : function(id) {
		id += "";
		return !!this.dataList[id];
	},
	clearDataTransfer : function(id) {
		id += "";
		delete this.dataList[id];
	},
	_log : function(message) {
		//alert(message);
		window.console.log(message);
	},
};


function CustomDataTransfer() {
	this.data = null;
}
CustomDataTransfer.prototype.setData = function(type, data) {
	this.data = data;
};
CustomDataTransfer.prototype.getData = function(type) {
	return this.data;
};