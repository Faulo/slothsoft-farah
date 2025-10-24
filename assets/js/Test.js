export async function run(callback, args) {
    const resolve = args[args.length - 1];
    try {
        const response = await callback.apply(null, args);
        resolve(response);
    } catch (error) {
        resolve({
            error: "javascript error",
            message: error.toString(),
        });
    }
}
