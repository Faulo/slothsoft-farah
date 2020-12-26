<?php
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\ResponseStrategy\SendBodyStrategy;

array_shift($_SERVER['argv']);

foreach ([
    'vendor/autoload.php',
    '../vendor/autoload.php'
] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

if (count($_SERVER['argv']) !== 1) {
    echo <<<'EOT'
Retrieve a farah asset via its URL.

Usage:
composer farah-asset "farah://vendor@module/path/to/asset?arguments#stream-type"

EOT;
    return 1;
}

$request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
$requestStrategy = new LookupAssetStrategy();
$responseStrategy = new SendBodyStrategy();

$kernel = Kernel::getInstance();
$response = $kernel->handle($requestStrategy, $responseStrategy, $request);
$code = $response->getStatusCode();
if ($code !== HTTPResponse::STATUS_OK) {
    printf('HTTP/%s %s%s', $response->getProtocolVersion(), StatusCode::getMessage($code), PHP_EOL);
    foreach ($response->getHeaders() as $key => $headers) {
        foreach ($headers as $header) {
            printf('%s: %s%s', $key, $header, PHP_EOL);
        }
    }
    return $code;
}
return 0;