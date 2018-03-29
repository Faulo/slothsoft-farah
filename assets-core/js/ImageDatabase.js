function ImageDatabase(name, cacheDuration) {
	this.info = {
		imageLookups : 0,
		cachedImages : 0,
		unchangedImages : 0,
		newImages : 0,
		deletedImages : 0,
		totalRequests : 0,
		finishedRequests : 0,
		errors : 0,
	};
	
	this.lookupImageQueue = [];
	
	this.lookupImageTime = new Date().getTime();
	this.lookupImageCacheDuration = cacheDuration * 24 * 60 * 60 * 1000;
	this.lookupImageCacheTime = this.lookupImageTime - this.lookupImageCacheDuration;
	
	try {
		IndexedDatabase.call(
			this,
			name,
			19,
			function() {
				let imageStore = this.db.createObjectStore(
					this.imageStoreName,
					{ keyPath: "url" }
				);
				imageStore.createIndex(
					this.imageStoreIndexURL,
					"url",
					{ unique: true }
				);
				imageStore.createIndex(
					this.imageStoreIndexTime,
					"lookupTime",
					{ unique: false }
				);
			}
		);
	} catch(e) {
		this.logError("ImageDatabase.construct error:\n"+e);
	}
}
ImageDatabase.prototype = Object.create(
	IndexedDatabase.prototype, {
		imageStoreName : { value : "images" },
		imageStoreIndexURL : { value : "urlIndex" },
		imageStoreIndexTime : { value : "timeIndex" },
		lookupImageQueue : { writable : true },
		lookupImageTime : { writable : true },
		lookupImageCacheDuration : { writable : true },
		lookupImageCacheTime : { writable : true },
		info : { writable : true },
		dbInit : {
			value : function() {
				IndexedDatabase.prototype.dbInit.call(this);
				
				while (this.lookupImageQueue.length) {
					let stack = this.lookupImageQueue.shift();
					this.lookupImage(stack[0], stack[1]);
				}
			},
		},
		lookupImage : {
			value : function(url, callback) {
				if (!this.dbInitialized) {
					this.lookupImageQueue.push([url, callback]);
					return;
				}
				this.info.imageLookups++;
				if (this.db) {
					try {
						let index = this.getObjectReaderIndex(this.imageStoreName, this.imageStoreIndexURL);
						//let req = index.openCursor(IDBKeyRange.only(url));
						let req = index.get(url);
						req.addEventListener(
							"success",
							(eve) => {
								/*
								let cursor = eve.target.result;
								if (cursor) {
									callback(cursor.value);
									this.verifyImage(cursor.value, callback);
								} else {
									this.downloadImage(url, callback);
								}
								//*/
								let res = eve.target.result;
								if (res) {
									callback(res);
									this.verifyImage(res, callback);
								} else {
									this.downloadImage(url, callback);
								}
							},
							false
						);
					} catch(e) {
						this.logError("ImageDatabase.lookupImage\n" + e);
					}
				} else {
					callback({url : url, blob : null});
				}
			},
		},
		downloadImage : {
			value : function(url, callback, oldRes) {
				this.info.totalRequests++;
				let req = new XMLHttpRequest();
				req.open("GET", url, true);
				if (oldRes) {
					if (oldRes.etag) {
						req.setRequestHeader("if-none-match", oldRes.etag);
					}
					if (oldRes.lastModified) {
						req.setRequestHeader("if-modified-since", oldRes.lastModified);
					}
				}
				req.addEventListener(
					"loadend",
					(eve) => {
						this.info.finishedRequests++;
						let req = eve.target;
						let blob = req.response;
						if (blob) {
							switch (req.status) {
								case 200: //OK
									let res = {
										url : url,
										blob : blob,
										etag : req.getResponseHeader("etag"),
										lastModified : req.getResponseHeader("last-modified"),
										lookupTime : this.lookupImageTime,
									};
									
									callback(res);
									
									this.insertImage(
										res,
										(eve) => {
											this.info.newImages++;
										}
									);
									break;
								case 304: //Not Modified
									oldRes.lookupTime = this.lookupImageTime;
									
									callback(oldRes);
									
									this.insertImage(
										oldRes,
										(eve) => {
											this.info.unchangedImages++;
										}
									);
									break;
								default:
									this.logError("ImageDatabase.downloadImage " + req.status + " " + req.statusText + ":\n" + url);
									break;
							}
						} else {
							this.logError("ImageDatabase.downloadImage response:\n" + req.responseText);
						}
					},
					false
				);
				req.responseType = "blob";
				req.send();
			}
		},
		deleteOldestImage : {
			value : function(callback) {
				if (!this.dbInitialized) {
					return;
				}
				if (this.db) {
					try {
						let index = this.getObjectReaderIndex(this.imageStoreName, this.imageStoreIndexTime);
						let req = index.openCursor();
						req.addEventListener(
							"success",
							(eve) => {
								let cursor = eve.target.result;
								if (cursor) {
									try {
										//this.logError("deleting " + cursor.value.url);
										let req = this.getObjectWriter(this.imageStoreName).delete(cursor.primaryKey);
										//let req = cursor.delete();
										req.addEventListener(
											"success",
											(eve) => {
												this.info.deletedImages++;
												if (callback) {
													callback(eve);
												}
											},
											false
										);
									} catch(e) {
										this.logError("ImageDatabase.deleteOldestImage\n" + e);
									}
								}
							},
							false
						);
					} catch(e) {
						this.logError("ImageDatabase.deleteOldestImage\n" + e);
					}
				}
			}
		},
		insertImage : {
			value : function(res, callback) {
				try {
					let req = this.getObjectWriter(this.imageStoreName).put(res);
					req.addEventListener(
						"success",
						(eve) => {
							if (callback) {
								callback(eve);
							}
						},
						false
					);
					req.addEventListener(
						"error",
						(eve) => {
							let error = eve.target.error;
							switch (error.name) {
								case "QuotaExceededError":
									this.deleteOldestImage(
										(eve) => {
											//this.insertImage(res, callback);
											/*
											setTimeout(
												this.insertImage.bind(this),
												0,
												res,
												callback
											);
											//*/
										}
									);
									eve.stopPropagation();
									eve.preventDefault();
									break;
								default:
									this.logError("ImageDatabase.insertImage error:\n" + error.name + ":\n" + error.message + "\nhttps://www.w3.org/TR/WebIDL-1/#h-idl-domexception-error-names");
									break;
							}
						},
						false
					);
				} catch(e) {
					this.logError("ImageDatabase.insertImage error:\n"+e);
				}
			}
		},
		verifyImage : {
			value : function(res, callback) {
				if (!res.lookupTime || res.lookupTime < this.lookupImageCacheTime) {
					return this.downloadImage(res.url, callback, res);
				} else {
					this.info.cachedImages++;
				}
			}
		},
		logError : {
			value : function(error) {
				this.info.errors++;
				if (self && self.logError) {
					self.logError(error);
				} else {
					console.log(error);
				}
			}
		},
		dbErrorCallback : {
			value : function(eve) {
				let error = eve.target.error;
				
				switch (error.name) {
					/*
					case "QuotaExceededError":
						break;
					case "InvalidStateError":
						break;
					//*/
					default:
						this.logError(error.name + ":\n" + error.message + "\nhttps://www.w3.org/TR/WebIDL-1/#h-idl-domexception-error-names");
						break;
				}
			}
		},
		getInfo : {
			value : function() {
				return this.info;
			}			
		},
	}
);