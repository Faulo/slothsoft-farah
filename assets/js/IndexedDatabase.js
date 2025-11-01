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

    async getObjectByIdAsync(storeName, storeIndex, id) {
        const index = this.getObjectReaderIndex(storeName, storeIndex);
        const request = index.get(id);

        return new Promise((resolve, reject) => {
            request.addEventListener(
                "success",
                eve => resolve(eve.target.result),
                false
            );
            request.addEventListener(
                "error",
                reject,
                false
            );
        });
    }

    async getObjectCursorAsync(storeName) {
        const store = this.getObjectReader(storeName);
        const request = store.openCursor();

        return new Promise((resolve, reject) => {
            request.addEventListener(
                "success",
                eve => resolve(eve.target.result),
                false
            );
            request.addEventListener(
                "error",
                reject,
                false
            );
        });
    }

    getObjectReaderIndex(storeName, storeIndex) {
        return this.getObjectReader(storeName).index(storeIndex);
    }

    getObjectReader(storeName) {
        return this.db.transaction(storeName, "readonly").objectStore(storeName);
    }

    async putObjectAsync(storeName, obj) {
        const index = this.getObjectWriter(storeName);
        const request = index.put(obj);

        return new Promise((resolve, reject) => {
            request.addEventListener(
                "success",
                resolve,
                false
            );
            request.addEventListener(
                "error",
                reject,
                false
            );
        });
    }

    getObjectWriter(storeName) {
        return this.db.transaction(storeName, "readwrite").objectStore(storeName);
    }
}