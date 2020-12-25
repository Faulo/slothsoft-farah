<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\RequestStrategy;

use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Farah\Kernel;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;
use Slothsoft\Farah\Sites\Domain;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;

class LookupRouteStrategy extends RequestStrategyBase {

    private $domain;

    public function __construct() {
        $this->domain = new Domain(Kernel::getCurrentSitemap());
    }

    protected function createUrl(ServerRequestInterface $serverRequest): FarahUrl {
        $uri = $serverRequest->getUri();
        $body = $serverRequest->getParsedBody();
        $params = $serverRequest->getQueryParams();

        $stack = new TreeRouteStack();
        $stack->addRoutes($this->domain->toRoutes());
        $zendRequest = new Request();
        $zendRequest->setUri((string) $uri);
        $match = $stack->match($zendRequest);
        // print_r($this->domain->toRoutes());
        var_dump([
            (string) $uri => $match
        ]);
        die();

        if (is_array($body)) {
            $args = $body + $params;
        } else {
            $args = $params;
        }

        if ($uri instanceof FarahUrl) {
            $url = $uri;
        } else {
            $urlAuthority = FarahUrlAuthority::createFromVendorAndModule(self::DEFAULT_VENDOR, self::DEFAULT_MODULE);
            $urlPath = null;
            $urlArgs = FarahUrlArguments::createFromValueList($args);

            $url = FarahUrl::createFromReference($this->extractFarahUrl($uri->getPath()), FarahUrl::createFromComponents($urlAuthority, $urlPath, $urlArgs));
        }

        return $url;
    }
}

