<?php
declare(strict_types = 1);
use Slothsoft\Core\StreamFilter\ChunkEncode;
use Slothsoft\Core\StreamFilter\ZlibEncodeDeflate;
use Slothsoft\Core\StreamFilter\ZlibEncodeGzip;
use Slothsoft\Core\StreamWrapper\StreamWrapperRegistrar;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\ModuleRepository;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory;


stream_filter_register('http.content-encoding.gzip', ZlibEncodeGzip::class);
stream_filter_register('http.content-encoding.deflate', ZlibEncodeDeflate::class);

stream_filter_register('http.transfer-encoding.chunked', ChunkEncode::class);


StreamWrapperRegistrar::registerStreamWrapper('farah', new FarahStreamWrapperFactory());

ModuleRepository::getInstance()->registerModule(
    new Module(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'core'),
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets-core'));