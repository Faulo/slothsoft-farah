export default class IndexedDatabase {
    db;
    dbInitialized;
    dbName;
    dbVersion;
    dbCreation;
    dbRequest;

    constructor(name, version = 1, creation = undefined) {
        if (!indexedDB) {
            throw new Error("no support for indexedDB");
        }

        this.dbInitialized = false;
        this.dbName = name;
        this.dbVersion = version;
        this.dbCreation = creation ? creation : (_) => { };
    }

    async initializeAsync() {
        try {
            this.dbRequest = indexedDB.open(
                this.dbName,
                /* //not yet implemented https://developer.mozilla.org/en-US/docs/Web/API/IDBFactory/open
                {
                    version : version,
                    storage : "persistent",
                }
                //*/
                this.dbVersion
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

            return new Promise((resolve, reject) => {
                this.dbRequest.addEventListener(
                    "success",
                    resolve,
                    false
                );
                this.dbRequest.addEventListener(
                    "error",
                    reject,
                    false
                );
            });
        } catch (e) {
            this.dbErrorCallback(
                {
                    target: {
                        error: e
                    }
                }
            );

            return Promise.reject(e);
        }
    }

    deleteAllStores() {
        while (this.db.objectStoreNames.length) {
            this.db.deleteObjectStore(this.db.objectStoreNames[0]);
        }
    }

    createAllStores() {
        this.dbCreation(this);
    }

    requestUpgradeCallback(_) {
        this.db = this.dbRequest.result;
        try {
            this.deleteAllStores();
            this.createAllStores();
        } catch (e) {
            this.dbErrorCallback(
                {
                    target: {
                        error: e
                    }
                }
            );
        }
    }

    requestSuccessCallback(_) {
        this.db = this.dbRequest.result;
        this.db.addEventListener(
            "error",
            this.dbErrorCallback.bind(this),
            false
        );

        this.dbInitialized = true;
    }

    dbErrorCallback(eve) {
        let error = eve.target.error;
        console.error(error.name + ":\n" + error.message);
    }

    getObjectReaderIndex(storeName, storeIndex) {
        return this.getObjectReader(storeName).index(storeIndex);
    }

    getObjectReader(storeName) {
        return this.db.transaction(storeName, "readonly").objectStore(storeName);
    }

    getObjectWriterIndex(storeName, storeIndex) {
        return this.getObjectWriter(storeName).index(storeIndex);
    }

    getObjectWriter(storeName) {
        return this.db.transaction(storeName, "readwrite").objectStore(storeName);
    }
}