const callbacks = [];
let domReady = document.readyState !== "loading";

function flushCallbacks() {
    domReady = true;
    document.removeEventListener("DOMContentLoaded", flushCallbacks);

    while (callbacks.length > 0) {
        const callback = callbacks.shift();
        try {
            callback();
        } catch (err) {
            console.error(err);
        }
    }
}

if (!domReady) {
    document.addEventListener("DOMContentLoaded", flushCallbacks);
}

export default {
    run(callback) {
        if (domReady) {
            try {
                callback();
            } catch (err) {
                console.error(err);
            }
        } else {
            callbacks.push(callback);
        }
    }
};
