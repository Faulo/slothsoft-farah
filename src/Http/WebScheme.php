<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Http;

use InvalidArgumentException;

/**
 * Normalizes the URL schemes supported by Farah's web request handling.
 *
 * @author Daniel Schulz
 * @since 2026-09-04
 */
final class WebScheme {
    
    public const HTTP = 'http';
    
    public const HTTPS = 'https';
    
    private const SUPPORTED_SCHEMES = [
        self::HTTP,
        self::HTTPS,
    ];
    
    public static function isSupported(string $scheme): bool {
        return in_array(strtolower($scheme), self::SUPPORTED_SCHEMES, true);
    }
    
    public static function normalize(string $scheme): string {
        $scheme = strtolower($scheme);
        if (! self::isSupported($scheme)) {
            throw new InvalidArgumentException("Web URL scheme '$scheme' is not supported.");
        }
        return $scheme;
    }
}
