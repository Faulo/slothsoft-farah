<?php

use Slothsoft\Core\FileSystem;

$ret = [];

$moduleList = FileSystem::scanDir(SERVER_ROOT . 'vendor/slothsoft', FileSystem::SCANDIR_REALPATH);

foreach ($moduleList as $modulePath) {
    $moduleName = basename($modulePath);
    $ret["/$moduleName"] = $this->createClosure(
        [],
        function() use ($modulePath, $moduleName) {
            $ret = new DOMDocument();
            
            $ret->appendChild($ret->createElement('result'));
            $ret->documentElement->textContent = "validating module $moduleName...";
            
            return $ret;
        }
    );
}

return $ret;