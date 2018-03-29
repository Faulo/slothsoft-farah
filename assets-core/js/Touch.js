
var Touch = {
	eventList : [
		"touchstart",
		"touchend",
		"touchmove"
	],
	currentTargetList : {},
	init : function() {
		var i, node, nodeList, tmpList;
		
		nodeList = [];
		tmpList = document.getElementsByTagName("button");
		for (i = 0; i < tmpList.length; i++) {
			nodeList.push(tmpList[i]);
		}
		tmpList = document.getElementsByTagName("input");
		for (i = 0; i < tmpList.length; i++) {
			nodeList.push(tmpList[i]);
		}
		tmpList = document.getElementsByTagName("label");
		for (i = 0; i < tmpList.length; i++) {
			nodeList.push(tmpList[i]);
		}
		for (i = 0; i < nodeList.length; i++) {
			node = nodeList[i];
			node._touch = this;
			node.addEventListener(
				"touchstart",
				this.events.start,
				false
			);
			node.addEventListener(
				"touchend",
				this.events.end,
				false
			);
			node.addEventListener(
				"touchmove",
				this.events.move,
				false
			);
		}
	},
	dispatchMouseEvent : function(targetNode, eventType, refEvent) {
		try {
			var newEvent = document.createEvent("MouseEvents");
			newEvent.initMouseEvent(
				eventType,
				refEvent.bubbles, refEvent.cancelable, refEvent.view,
				refEvent.detail, refEvent.screenX, refEvent.screenY, refEvent.clientX, refEvent.clientY,
				refEvent.ctrlKey, refEvent.altKey, refEvent.shiftKey, refEvent.metaKey, 
				refEvent.button, refEvent.relatedTarget
			);
			targetNode.dispatchEvent(newEvent);
			targetNode.focus();
		} catch(e) {
			alert(e);
		}
	},
	setCurrenTargetList : function(touchList) {
		var touch, node;
		for (var i = 0; i < touchList.length; i++) {
			touch = touchList[i];
			node = document.elementFromPoint(touch.clientX, touch.clientY);
			if (node) {
				this.currentTargetList[touch.identifier] = node;
			}
		}
	},
	getCurrenTargetList : function(touchList) {
		var touch, node, ret;
		ret = [];
		for (var i = 0; i < touchList.length; i++) {
			touch = touchList[i];
			node = document.elementFromPoint(touch.clientX, touch.clientY);
			if (this.currentTargetList[touch.identifier] === node) {
				ret.push(node);
			}
		}
		return ret;
	},
	clearCurrentTargetList : function(touchList) {
		var touch;
		for (var i = 0; i < touchList.length; i++) {
			touch = touchList[i];
			if (this.currentTargetList[touch.identifier]) {
				delete this.currentTargetList[touch.identifier];
			}
		}
	},
	events : {
		start : function(eve) {
			eve.preventDefault();
			
			this._touch.dispatchMouseEvent(this, "mousedown", eve);
			
			this._touch.setCurrenTargetList(eve.changedTouches);
		},
		end : function(eve) {
			var nodeList;
			eve.preventDefault();
			
			this._touch.dispatchMouseEvent(this, "mouseup", eve);
			
			nodeList = this._touch.getCurrenTargetList(eve.changedTouches);
			
			for (var i = 0; i < nodeList.length; i++) {
				this._touch.dispatchMouseEvent(nodeList[i], "click", eve);
			}
			
			this._touch.clearCurrenTargetList(eve.changedTouches);
		},
		move : function(eve) {
			eve.preventDefault();
		},
	},
};

addEventListener(
	"load",
	function(eve) {
		Touch.init();
	},
	false
);