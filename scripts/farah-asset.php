<?php

foreach (['vendor/autoload.php', '../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}


if (!isset($_SERVER['argv'][1])) {
	echo <<<HELP
Retrieve farah asset
Usage: farah-asset "farah://vendor@package/asset/path?arguments#stream-type"

HELP;
    return 1;
}

readfile($_SERVER['argv'][1]);

exit(0);