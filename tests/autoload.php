<?php
declare(strict_types = 1);

use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Farah\Kernel;
use GuzzleHttp\Psr7\ServerRequest;

require_once __DIR__ . '/../vendor/autoload.php';

ServerEnvironment::setRootDirectory(dirname(__DIR__));
ServerEnvironment::setCacheDirectory(sys_get_temp_dir());
ServerEnvironment::setLogDirectory(sys_get_temp_dir());

Kernel::setCurrentRequest(ServerRequest::fromGlobals());