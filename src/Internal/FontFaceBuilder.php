<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Internal;

use Slothsoft\Core\IO\Writable\Decorators\ChunkWriterFileCache;
use Slothsoft\Core\IO\Writable\Delegates\ChunkWriterFromChunksDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FileWriterResultBuilder;
use Generator;

final class FontFaceBuilder implements ExecutableBuilderStrategyInterface {
    
    /**
     *
     * @link https://www.w3.org/TR/css-fonts-4/?utm_source=chatgpt.com#font-format-definitions
     */
    private const FORMAT_MAPPING = [
        'font/woff2' => 'woff2',
        'font/woff' => 'woff',
        'font/ttf' => 'truetype',
        'font/otf' => 'opentype',
        'image/svg+xml' => 'svg',
        'application/vnd.ms-fontobject' => 'embedded-opentype'
    ];
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $assetsRef = $args->get(FontFaceParameterFilter::PARAM_ASSETS);
        
        $assetsUrl = FarahUrl::createFromReference($assetsRef, $context->createUrl());
        $assetsAsset = Module::resolveToAsset($assetsUrl);
        $assetUrls = [];
        /** @var AssetInterface $asset */
        foreach ($assetsAsset->getAssetChildren() as $asset) {
            $assetUrls[] = $asset->createRealUrl();
        }
        
        $writer = new ChunkWriterFromChunksDelegate(function () use ($assetUrls): Generator {
            $fonts = [];
            /** @var FarahUrl $assetUrl */
            foreach ($assetUrls as $assetUrl) {
                $result = Module::resolveToResult($assetUrl);
                $mimeType = $result->lookupMimeType();
                if (isset(self::FORMAT_MAPPING[$mimeType])) {
                    $format = self::FORMAT_MAPPING[$mimeType];
                    $fileName = $result->lookupFileName();
                    $fontName = pathinfo($fileName, PATHINFO_FILENAME);
                    $fonts[$fontName] ??= [];
                    
                    $href = (string) $assetUrl;
                    $href = substr($href, strlen('farah:/'));
                    $fonts[$fontName][$format] = $href;
                }
            }
            foreach ($fonts as $name => $formats) {
                yield '@font-face {' . PHP_EOL;
                yield sprintf('  font-family: "%s";%s', $name, PHP_EOL);
                yield sprintf('  src: local("%s")', $name);
                foreach (self::FORMAT_MAPPING as $format) {
                    if (isset($formats[$format])) {
                        $href = $formats[$format];
                        yield sprintf(',%s       url: local("%s") format("%s")', PHP_EOL, $href, $format);
                    }
                }
                yield ';' . PHP_EOL;
                yield '}' . PHP_EOL . PHP_EOL;
            }
        });
        
        $writer = new ChunkWriterFileCache($writer, $context->createCacheFile(md5(implode(PHP_EOL, $assetUrls)) . ".css", $args));
        $resultBuilder = new FileWriterResultBuilder($writer, "font-face.css");
        return new ExecutableStrategies($resultBuilder);
    }
}

