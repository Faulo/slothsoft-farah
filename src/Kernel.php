<?php
declare(strict_types = 1);
namespace Slothsoft\Farah;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\RequestStrategy\RequestStrategyInterface;
use Slothsoft\Farah\ResponseStrategy\ResponseStrategyInterface;
use Slothsoft\Farah\Tracking\Manager;

class Kernel {

    public static function getInstance(): self {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private static function currentSitemap(): ConfigurationField {
        static $field;
        if ($field === null) {
            $field = new AssetConfigurationField();
        }
        return $field;
    }

    public static function setCurrentSitemap($asset): void {
        self::currentSitemap()->setValue($asset);
    }

    public static function getCurrentSitemap(): AssetInterface {
        return self::currentSitemap()->getValue();
    }

    public static function hasCurrentSitemap() : bool {
        return self::currentSitemap()->hasValue();
    }

    public static function clearCurrentSitemap() : void {
        self::currentSitemap()->setValue(null);
    }

    private static function currentRequest(): ConfigurationField {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField();
        }
        return $field;
    }

    public static function setCurrentRequest(ServerRequestInterface $request) {
        self::currentRequest()->setValue($request);
    }

    public static function getCurrentRequest(): ServerRequestInterface {
        return self::currentRequest()->getValue();
    }

    private static function trackingEnabled(): ConfigurationField {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField(false);
        }
        return $field;
    }

    public static function setTrackingEnabled(bool $value) {
        self::trackingEnabled()->setValue($value);
    }

    public static function getTrackingEnabled(): bool {
        return self::trackingEnabled()->getValue();
    }

    private static function trackingExceptionUris() {
        static $field;
        if ($field === null) {
            $field = new ConfigurationField([]);
        }
        return $field;
    }

    public static function setTrackingExceptionUris(string ...$uriList) {
        self::trackingExceptionUris()->setValue($uriList);
    }

    public static function getTrackingExceptionUris(): array {
        return self::trackingExceptionUris()->getValue();
    }

    public function handle(RequestStrategyInterface $requestStrategy, ResponseStrategyInterface $responseStrategy, ServerRequestInterface $request): ResponseInterface {
        self::setCurrentRequest($request);

        $response = $requestStrategy->process($request);
        if (self::getTrackingEnabled()) {
            $this->track((new \ReflectionClass($requestStrategy))->getShortName(), $request, $response);
        }
        $responseStrategy->process($response);
        return $response;
    }

    private function track(string $strategy, ServerRequestInterface $request, ResponseInterface $response) {
        $env = $request->getServerParams();

        // request parameters
        $env['RESPONSE_STRATEGY'] = $strategy;

        // response parameters
        $env['RESPONSE_STATUS'] = $response->getStatusCode();
        $env['RESPONSE_TYPE'] = $response->getHeaderLine('content-type');
        $env['RESPONSE_ENCODING'] = $response->getHeaderLine('content-encoding');
        $env['RESPONSE_LANGUAGE'] = $response->getHeaderLine('content-language');

        // environment parameters
        $env['RESPONSE_TIME'] = get_execution_time();
        $env['RESPONSE_MEMORY'] = sprintf('%.2f', memory_get_peak_usage() / 1048576);

        Manager::track($env);
    }
}


