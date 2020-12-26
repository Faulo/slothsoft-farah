<?php
foreach ([
    'vendor/autoload.php',
    '../vendor/autoload.php'
] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

if (! isset($_SERVER['argv'][1])) {
    echo <<<'EOT'
Retrieve a farah asset via its URL.

Usage:
composer farah-asset "farah://vendor@module/path/to/asset?arguments#stream-type"

EOT;
    return 1;
}

readfile($_SERVER['argv'][1]);

return 0;