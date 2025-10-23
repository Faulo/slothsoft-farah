var Test = {
    run: function(callback, arguments) {
        try {
            return callback.apply(null, arguments);
        } catch (error) {
            return {
                error: "javascript error",
                message: error.toString(),
            };
        }
    },
    runAsync: async function(callback, arguments) {
        const resolve = arguments[arguments.length - 1];
        try {
            const response = await callback.apply(null, arguments);
            resolve(response);
        } catch (error) {
            resolve({
                error: "javascript error",
                message: error.toString(),
            });
        }
    }
};