import IndexedDatabase from "/slothsoft@farah/js/IndexedDatabase";

export default class BlobDatabase {
    info = {
        blobLookups: 0,
        cachedBlobs: 0,
        unchangedBlobs: 0,
        newBlobs: 0,
        deletedBlobs: 0,
        totalRequests: 0,
        finishedRequests: 0,
        errors: 0,
    };

    lookupQueue = [];

    storeName = "blobs";
    storeIndexURL = "urlIndex";
    storeIndexTime = "timeIndex";

    lookupTime;
    lookupCacheDuration;
    lookupCacheTime;

    indexed;

    constructor(name, cacheDuration = 7) {
        this.lookupTime = new Date().getTime();
        this.lookupCacheDuration = cacheDuration * 24 * 60 * 60 * 1000;
        this.lookupCacheTime = this.lookupTime - this.lookupCacheDuration;

        this.indexed = new IndexedDatabase(name, 20, indexed => {
            const blobStore = indexed.db.createObjectStore(
                this.storeName,
                { keyPath: "url" }
            );
            blobStore.createIndex(
                this.storeIndexURL,
                "url",
                { unique: true }
            );
            blobStore.createIndex(
                this.storeIndexTime,
                "lookupTime",
                { unique: false }
            );
        });
    }

    async initializeAsync() {
        await this.indexed.initializeAsync();

        while (this.lookupQueue.length > 0) {
            const stack = this.lookupQueue.shift();
            this.lookupBlob(stack[0], stack[1]);
        }
    }

    async lookupBlobAsync(url) {
        return new Promise(resolve => this.lookupBlob(url, resolve));
    }

    lookupBlob(url, callback) {
        if (!this.indexed.dbInitialized) {
            this.lookupQueue.push([url, callback]);
            return;
        }

        this.info.blobLookups++;

        this.indexed
            .getObjectByIdAsync(this.storeName, this.storeIndexURL, url)
            .then(result => {
                if (result == undefined) {
                    this.downloadBlob(url, callback);
                } else {
                    callback(result);

                    if (!result.lookupTime || result.lookupTime < result.lookupBlobCacheTime) {
                        this.downloadBlob(result.url, callback, result);
                    } else {
                        this.info.cachedBlobs++;
                    }
                }
            });
    }

    downloadBlob(url, callback, previousResult = undefined) {
        this.info.totalRequests++;

        const request = new XMLHttpRequest();
        request.open("GET", url, true);

        if (previousResult) {
            if (previousResult.etag) {
                request.setRequestHeader("if-none-match", previousResult.etag);
            }
            if (previousResult.lastModified) {
                request.setRequestHeader("if-modified-since", previousResult.lastModified);
            }
        }

        request.addEventListener(
            "loadend",
            (_) => {
                this.info.finishedRequests++;

                if (request.response) {
                    switch (request.status) {
                        case 200: // OK
                            const result = {
                                url: url,
                                blob: request.response,
                                etag: request.getResponseHeader("etag"),
                                lastModified: request.getResponseHeader("last-modified"),
                                lookupTime: this.lookupTime,
                            };

                            callback(result);

                            this.insertBlob(
                                result,
                                (success) => {
                                    if (success) {
                                        this.info.newBlobs++;
                                    }
                                }
                            );
                            break;
                        case 304: // Not Modified
                            previousResult.lookupTime = this.lookupTime;

                            callback(previousResult);

                            this.insertBlob(
                                previousResult,
                                (success) => {
                                    if (success) {
                                        this.info.unchangedBlobs++;
                                    }
                                }
                            );
                            break;
                        default:
                            this.logError("BlobDatabase.downloadBlob " + request.status + " " + request.statusText + ":\n" + url);
                            break;
                    }
                } else {
                    this.logError("BlobDatabase.downloadBlob response:\n" + request.responseText);
                }
            },
            false
        );
        request.responseType = "blob";
        request.send();
    }
    async insertBlobAsync(result) {
        try {
            await this.indexed.putObjectAsync(this.storeName, result);
            return true;
        } catch (e) {
            return false;
        }
    }
    insertBlob(result, callback) {
        this.indexed
            .putObjectAsync(this.storeName, result)
            .catch(eve => {
                const error = eve.target.error;
                switch (error.name) {
                    case "QuotaExceededError":
                        eve.stopPropagation();
                        eve.preventDefault();

                        this.deleteOldestBlob(_ => this.insertBlob(result, callback));
                        break;
                    default:
                        this.logError("BlobDatabase.insertBlob error:\n" + error.name + ":\n" + error.message + "\nhttps://www.w3.org/TR/WebIDL-1/#h-idl-domexception-error-names");
                        callback(false);
                        break;
                }
            })
            .catch(_ => callback(false))
            .then(_ => callback(true));
    }
    deleteOldestBlob(callback) {
        this.indexed
            .getObjectCursorAsync(this.storeName, this.storeIndexTime)
            .then(cursor => this.indexed.deleteObjectAsync(this.storeName, cursor.primaryKey))
            .then(result => {
                this.info.deletedBlobs++;
                return result;
            })
            .then(callback)
            .catch(this.logError);
    }
    logError(error) {
        this.info.errors++;

        console.error(error);
    }
}