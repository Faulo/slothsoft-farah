function IndexedDatabase(name, version, creation) {
	//console.log("IndexedDatabase.construct\n" + name + "\n" + version);
	
	if (!indexedDB) {
		throw "no support for indexedDB";
	}
	
	this.dbInitialized = false;
	this.dbName = name;
	this.dbVersion = version;
	this.dbCreation = creation.bind(this);
	
	try {
		this.dbRequest = indexedDB.open(
			name, 
			/* //not yet implemented https://developer.mozilla.org/en-US/docs/Web/API/IDBFactory/open
			{
				version : version,
				storage : "persistent",
			}
			//*/
			version
		);
		
		this.dbRequest.addEventListener(
			"upgradeneeded",
			this.requestUpgradeCallback.bind(this),
			false
		);
		this.dbRequest.addEventListener(
			"success",
			this.requestSuccessCallback.bind(this),
			false
		);
		this.dbRequest.addEventListener(
			"error",
			this.dbErrorCallback.bind(this),
			false
		);
	} catch(e) {
		this.dbErrorCallback(
			{
				target : {
					error : e
				}
			}
		);
	}
}
IndexedDatabase.prototype = Object.create(
	Object.prototype, {
		db				: { writable : true},
		dbInitialized 	: { writable : true },
		dbName			: { writable : true},
		dbVersion		: { writable : true},
		dbCreation		: { writable : true},
		dbRequest		: { writable : true},
		deleteAllStores : {
			value : function() {
				while (this.db.objectStoreNames.length) {
					this.db.deleteObjectStore(this.db.objectStoreNames[0]);
				}
			}
		},
		createAllStores : {
			value : function() {
				this.dbCreation(this);
			}
		},
		requestUpgradeCallback : {
			value : function(eve) {
				this.db = this.dbRequest.result;
				try {
					this.deleteAllStores();
					this.createAllStores();
				} catch(e) {
					this.dbErrorCallback(
						{
							target : {
								error : e
							}
						}
					);
				}
			}
		},
		requestSuccessCallback : {
			value : function(eve) {
				this.db = this.dbRequest.result;
				this.db.addEventListener(
					"error",
					this.dbErrorCallback.bind(this),
					false
				);
				this.dbInit();
			}
		},
		dbInit : {
			value : function() {
				this.dbInitialized = true;
			}
		},
		dbErrorCallback : {
			value : function(eve) {
				let error = eve.target.error;
				console.log(error.name + ":\n" + error.message);
			}
		},
		getObjectReaderIndex :{
			value : function(storeName, storeIndex) {
				return this.getObjectReader(storeName).index(storeIndex);
			}
		},
		getObjectReader :{
			value : function(storeName) {
				return this.db.transaction(storeName, "readonly").objectStore(storeName);
			}
		},
		getObjectWriterIndex :{
			value : function(storeName, storeIndex) {
				return this.getObjectWriter(storeName).index(storeIndex);
			}
		},
		getObjectWriter :{
			value : function(storeName) {
				return this.db.transaction(storeName, "readwrite").objectStore(storeName);
			}
		},
	}
);