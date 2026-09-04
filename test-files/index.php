<?php
declare(strict_types = 1);

use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\ResponseStrategy\SendHeaderAndBodyStrategy;

require dirname(__DIR__) . '/vendor/autoload.php';

$requestStrategy = new LookupAssetStrategy();
$request = MessageFactory::createServerRequest();
$request = $request->withUri($requestStrategy->createUrl($request));
$responseStrategy = new SendHeaderAndBodyStrategy();

$kernel = Kernel::getInstance();
$kernel->handle($requestStrategy, $responseStrategy, $request);
