<?php

use Slothsoft\Farah\Stream\FarahWrapper;

stream_wrapper_register('farah', FarahWrapper::class);

if (!defined('FARAH_TRACKING_ENABLED')) {
    define('FARAH_TRACKING_ENABLED', false);
}
if (!defined('FARAH_TRACKING_DNT_URI')) {
    define('FARAH_TRACKING_DNT_URI', []);
}


if (!defined('FARAH_RESPONSE_MERGE_STYLEFILES')) {
    define('FARAH_RESPONSE_MERGE_STYLEFILES', false);
}
if (!defined('FARAH_RESPONSE_MINIFY_STYLEFILES')) {
    define('FARAH_RESPONSE_MINIFY_STYLEFILES', false);
}

if (!defined('FARAH_RESPONSE_MERGE_SCRIPTFILES')) {
    define('FARAH_RESPONSE_MERGE_SCRIPTFILES', false);
}
if (!defined('FARAH_RESPONSE_MINIFY_SCRIPTFILES')) {
    define('FARAH_RESPONSE_MINIFY_SCRIPTFILES', false);
}


