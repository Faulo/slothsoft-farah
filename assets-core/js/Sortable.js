
function Sortable(rootNode) {
	var nodeList;
	
	this.rootNode = rootNode;
	
	this.headCellList = [];
	nodeList = this.rootNode.querySelectorAll("thead > tr > *");
	for (var i = 0; i < nodeList.length; i++) {
		this.headCellList.push(nodeList[i]);
	}
	
	this.bodyRowList = [];
	nodeList = this.rootNode.querySelectorAll("tbody > tr");
	for (var i = 0; i < nodeList.length; i++) {
		this.bodyRowList.push(nodeList[i]);
	}
	
	for (var i = 0; i < this.headCellList.length; i++) {
		var node = this.headCellList[i];
		node.addEventListener(
			"click",
			this.events.clickHead.bind(this),
			false
		);
	}
}
Sortable.prototype.sort = function(index, order) {
	this.currentIndex = index;
	this.currentOrder = order;
	this.bodyRowList.sort(this.sortFunction.bind(this));
	
	for (var i = 0; i < this.bodyRowList.length; i++) {
		this.bodyRowList[i].parentNode.appendChild(this.bodyRowList[i]);
	}
};
Sortable.prototype.sortFunction = function(a, b) {
	a = a.cells[this.currentIndex]
		? a.cells[this.currentIndex].textContent
		: null;
	b = b.cells[this.currentIndex]
		? b.cells[this.currentIndex].textContent
		: null;
	return this.currentOrder
		? a > b
		: a < b;
};
Sortable.prototype.events = {
	clickHead : function(eve) {
		var index = eve.target.hasAttribute("data-sortable-index")
			? eve.target.getAttribute("data-sortable-index")
			: eve.target.cellIndex;
		var order = eve.target.hasAttribute("data-sortable-order")
			? parseInt(eve.target.getAttribute("data-sortable-order"))
			: 0;
			
		order = order
			? 0
			: 1;
			
		eve.target.setAttribute("data-sortable-order", order);
		this.sort(index, order);
	},
};


addEventListener(
	"load",
	function(eve) {
		var nodeList = document.querySelectorAll("*[data-sortable]");
		for (var i = 0; i < nodeList.length; i++) {
			new Sortable(nodeList[i]);
		}
	},
	false
);