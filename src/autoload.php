<?php
declare(strict_types = 1);
use Slothsoft\Core\StreamWrapper\StreamWrapperRegistrar;
use Slothsoft\Farah\Http\ContentCoding;
use Slothsoft\Farah\Http\TransferCoding;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory;
use Slothsoft\Farah\Streams\Filters\ChunkedFilteredStreamFactory;
use Slothsoft\Farah\Streams\Filters\ZlibFilteredStreamFactory;

StreamWrapperRegistrar::registerStreamWrapper('farah', new FarahStreamWrapperFactory());

Module::registerWithXmlManifestAndDefaultAssets('slothsoft@core', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets-core');

ContentCoding::registerEncodingFilter('gzip', new ZlibFilteredStreamFactory(ZLIB_ENCODING_GZIP));
ContentCoding::registerEncodingFilter('deflate', new ZlibFilteredStreamFactory(ZLIB_ENCODING_DEFLATE));

TransferCoding::registerEncodingFilter('chunked', new ChunkedFilteredStreamFactory());