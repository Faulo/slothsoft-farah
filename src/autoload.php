<?php
declare(strict_types = 1);
use Slothsoft\Core\StreamWrapper\StreamWrapperRegistrar;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory;


StreamWrapperRegistrar::registerStreamWrapper('farah', new FarahStreamWrapperFactory());

Kernel::getInstance()->registerModule(new Module(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'core'), dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets-core'));