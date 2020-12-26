<?php
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Http\StatusCode;
use Slothsoft\Farah\RequestStrategy\LookupPageStrategy;
use Slothsoft\Farah\ResponseStrategy\SendBodyStrategy;
use Slothsoft\Farah\HTTPResponse;

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
Retrieve a farah page via its URL.
        
Usage:
composer farah-page "/path/to/page?arguments#stream-type"

EOT;
    return 1;
}

$request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
$requestStrategy = new LookupPageStrategy();
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