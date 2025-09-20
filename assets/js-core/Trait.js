// © 2012 Daniel Schulz

var Trait = {
	use : function(targetObject, traitName) {
		var i,
			traitObject = this[traitName];
		if (traitObject.uses) {
			for (i = 0; i < traitObject.uses.length; i++) {
				this.use(targetObject, traitObject.uses[i]);
			}
		}
		targetObject.prototype[traitName] = traitObject.construct
			? traitObject.construct
			: function() {};
		if (traitObject.vars) {
			for (i in traitObject.vars) {
				targetObject.prototype[i] = traitObject.vars[i];
			}
		}
		if (traitObject.methods) {
			for (i in traitObject.methods) {
				targetObject.prototype[i] = traitObject.methods[i];
				targetObject.prototype[traitName + i[0].toUpperCase() + i.substr(1)] = traitObject.methods[i];
			}
		}
	},
	build : function(traitObject) {
		var i, targetObject;
		targetObject = traitObject.construct
			? traitObject.construct
			: function() {};
		if (traitObject.uses) {
			for (i = 0; i < traitObject.uses.length; i++) {
				this.use(targetObject, traitObject.uses[i]);
			}
		}
		if (traitObject.vars) {
			for (i in traitObject.vars) {
				targetObject.prototype[i] = traitObject.vars[i];
			}
		}
		if (traitObject.methods) {
			for (i in traitObject.methods) {
				targetObject.prototype[i] = traitObject.methods[i];
			}
		}
		return targetObject;
	}
};

/*
Trait.Example = {
	uses : [
	],
	construct : function() {
		this.data += " welt";
	},
	vars : {
		data : "hallo",
	},
	methods : {
		init : function() {
		}
	}
};

var ProjectClass = Trait.build({
	uses : [
	],
	construct : function() {
	},
	vars : {
	},
	methods : {
	}
});
//*/