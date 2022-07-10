<?php
declare(strict_types = 1);
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\IO\Psr7\ChunkedFilteredStreamFactory;
use Slothsoft\Core\IO\Psr7\ZlibFilteredStreamFactory;
use Slothsoft\Core\StreamWrapper\StreamWrapperRegistrar;
use Slothsoft\Farah\HTTPResponse;
use Slothsoft\Farah\Http\ContentCoding;
use Slothsoft\Farah\Http\TransferCoding;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory;

StreamWrapperRegistrar::registerStreamWrapper('farah', new FarahStreamWrapperFactory());

Module::registerWithXmlManifestAndDefaultAssets('slothsoft@core', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets-core');

ContentCoding::registerEncodingFilter('gzip', new ZlibFilteredStreamFactory(ZLIB_ENCODING_GZIP));
ContentCoding::registerEncodingFilter('deflate', new ZlibFilteredStreamFactory(ZLIB_ENCODING_DEFLATE));

TransferCoding::registerEncodingFilter('chunked', new ChunkedFilteredStreamFactory());

Module::registerWithXmlManifestAndDefaultAssets('slothsoft@farah', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets');

HTTPResponse::cacheDurations()['image'] = Seconds::MINUTE;
HTTPResponse::cacheDurations()['application/font'] = Seconds::MINUTE;
HTTPResponse::cacheDurations()['text/css'] = Seconds::MINUTE;
HTTPResponse::cacheDurations()['application/javascript'] = Seconds::MINUTE;