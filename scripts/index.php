<?php
declare(strict_types = 1);

use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\RequestStrategy\LookupPageStrategy;
use Slothsoft\Farah\ResponseStrategy\SendHeaderAndBodyStrategy;

foreach ([
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php'
] as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}

$request = MessageFactory::createServerRequest($_SERVER, $_REQUEST, $_FILES);
if (preg_match('~^/[^/]+@[^/]+~', $request->getUri()->getPath())) {
    $requestStrategy = new LookupAssetStrategy();
} else {
    $requestStrategy = new LookupPageStrategy();
}
$responseStrategy = new SendHeaderAndBodyStrategy();

$kernel = Kernel::getInstance();
$kernel->handle($requestStrategy, $responseStrategy, $request);