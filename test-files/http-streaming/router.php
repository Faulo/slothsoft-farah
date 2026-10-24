<?php
declare(strict_types = 1);

use Slothsoft\Farah\Http\MessageFactory;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\RequestStrategy\LookupAssetStrategy;
use Slothsoft\Farah\ResponseStrategy\SendHeaderAndBodyStrategy;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

// Configure buffers owned by this fixture; Farah must leave their stack intact.
if (isset($_GET['nonremovable']) || isset($_GET['custom']) || isset($_GET['blocked']) || isset($_GET['unbuffered'])) {
    ob_end_flush();
    if (isset($_GET['custom'])) {
        ob_start(static fn(string $output): string => strtoupper($output), 4096);
    } elseif (isset($_GET['blocked'])) {
        ob_start(static fn(string $output): string => strtoupper($output), 4096, 0);
    } elseif (isset($_GET['nonremovable'])) {
        ob_start(null, 4096, PHP_OUTPUT_HANDLER_FLUSHABLE);
    }
}
if (isset($_GET['buffered']) || isset($_GET['nested'])) {
    ob_start(static fn(string $output): string => strtoupper($output), 4096);
}
if (! isset($_GET['unbuffered'])) {
    echo 'prefix:';
}

Module::registerWithXmlManifestAndDefaultAssets('test@streaming', __DIR__);
$response = (new LookupAssetStrategy())->process(MessageFactory::createServerRequest());
$bufferLevel = ob_get_level();
(new SendHeaderAndBodyStrategy())->process($response);
file_put_contents(getenv('FARAH_STREAM_GATE') . '/buffer-levels', $bufferLevel . ':' . ob_get_level());
