# Slothsoft Farah

[![Packagist Version](https://img.shields.io/packagist/v/slothsoft/farah)](https://packagist.org/packages/slothsoft/farah)
[![PHP Version Support](https://img.shields.io/packagist/php-v/slothsoft/farah)](https://www.php.net/)
[![Documentation](https://img.shields.io/badge/docs-reference-blue.svg)](https://faulo.github.io/slothsoft-farah/)
[![Test Status](https://github.com/Faulo/slothsoft-farah/actions/workflows/ci-tests.yml/badge.svg)](https://github.com/Faulo/slothsoft-farah/actions/workflows/ci-tests.yml)
[![license badge](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Files and Resources and Hypertext: a manifest-driven content management and asset delivery package for slothsoft modules.

Farah resolves `farah://vendor@module/path?arguments#stream` URLs through XML manifests, turns assets into executable results, and exposes those results as PSR-7 responses, DOM writers, file writers, stream writers, or chunk writers. It also ships the XML schemas, XSLT assets, JavaScript helpers, and small command-line tools used by existing Farah modules.

This is an older slothsoft package with active infrastructure and historical compatibility code. The package is kept installable for existing consumers, but not every namespace is recommended for new code.

## Compatibility Policy

Semantic versioning is in effect. Public classes are public API, including public constructors, methods, constants, and properties. Public signatures must remain backward-compatible unless an API-breaking change is specifically requested for a major release.

All code in this package must remain syntactically valid on the PHP version declared by `PHP_VERSION` in `.env` and behaviorally compatible with every PHP version covered by CI.

Bug fixes are allowed in every area of the package, including deprecated and historical APIs. Deprecated APIs should not be used for new code, but they are still expected to keep working for existing consumers, including compatibility fixes for newer PHP versions.

Adding dependencies is acceptable when the dependency is justified by the change and remains compatible with this package's supported PHP versions.

## Current / Supported Areas

These parts are suitable for use in new or maintained Farah code:

- `Slothsoft\Farah\Kernel`
  - Request/response orchestration for Farah page and asset lookups.
  - Process-wide current request, page, sitemap, and tracking configuration.
- `Slothsoft\Farah\FarahUrl`
  - Immutable Farah URL value objects for authorities, paths, query arguments, and stream identifiers.
  - PSR-7 `UriInterface` integration for the `farah://vendor@module/path?arguments#stream` URL format.
- `Slothsoft\Farah\Module`
  - Module registration and resolution from manifest to asset, executable, result, and writer interfaces.
  - File-system cache/data path helpers scoped by Farah URL.
- `Slothsoft\Farah\Module\Manifest`
  - XML manifest loading and asset construction.
  - Strategy interfaces for tree loading and asset building.
- `Slothsoft\Farah\Module\Asset`
  - Asset lookup, path resolution, manifest instructions, parameter filtering, parameter suppliers, and executable builders.
  - Built-in instruction strategies for imports, links, templates, documents, manifests, scripts, stylesheets, and dictionaries.
- `Slothsoft\Farah\Module\Executable`
  - Executable lookup and result building.
  - Result builders for DOM writers, file writers, streams, chunk writers, transformations, maps, proxies, and CLI delegation.
- `Slothsoft\Farah\Module\Result`
  - Result lookup and conversion into DOM, file, stream, string, and chunk writer forms.
- `Slothsoft\Farah\RequestStrategy` and `Slothsoft\Farah\ResponseStrategy`
  - Page, asset, and route lookup strategies.
  - Header/body response sending strategies.
- `Slothsoft\Farah\Http`
  - Namespaced HTTP helpers for PSR-7 messages, status codes, content coding, transfer coding, and identity encoding.
- `Slothsoft\Farah\LinkDecorator`
  - HTML, SVG, and Farah link decoration for generated DOM output.
- `Slothsoft\Farah\StreamWrapper`
  - Stream wrapper factory and wrappers for Farah document and string resources.
- `Slothsoft\Farah\Schema`
  - Schema lookup for bundled Farah XML schemas.
- `Slothsoft\Farah\Exception`
    - Package-specific exception types and DOM-serializable exception context.
- `Slothsoft\Farah\Internal`
    - Built-in executable builders for Farah's bundled module assets, such as sitemap, request, phpinfo, and font-face generation.
- `Slothsoft\Farah\Dictionary`
    - Dictionary loading and transformation helpers used by older XML/XSLT workflows.
- `Slothsoft\Farah\Sites`
    - Domain and sitemap lookup model for page routing.
- `Slothsoft\Farah\Configuration`
    - Mutable configuration field wrappers for Farah URLs and assets.
- `assets/`
  - Farah's own module manifest, XML schemas, XSLT templates, sitemap generator assets, and JavaScript helper assets.
- `scripts/farah-asset` and `scripts/farah-page`
  - Composer binaries for resolving a Farah asset URL or page URL from the command line.

## Historical / Deprecated

These components are included for historical reasons only. Do not use them for new code.

- `Slothsoft\Farah\Cache`
  - Small cache helpers retained for existing module behavior.
- `Slothsoft\Farah\Container`
  - Minimal container interface used by existing code.
- `Slothsoft\Farah\Daemon`
  - Socket-based daemon server/client helpers for long-running executable builders.
- `Slothsoft\Farah\Security`
  - Banned-user/session helper retained for existing deployments.
- `Slothsoft\Farah\Session`
  - Legacy session persistence helper.
- `Slothsoft\Farah\Tracking`
  - Request tracking, log table, archive, tick, and view helpers.
- `Slothsoft\Farah\HTTPClosure`, `HTTPCommand`, `HTTPEvent`, `HTTPRequest`, and `HTTPResponse`
  - Legacy root-level HTTP classes predating the current PSR-7 request/response flow.
  - Prefer `Slothsoft\Farah\Http`, `Slothsoft\Farah\RequestStrategy`, `Slothsoft\Farah\ResponseStrategy`, and PSR-7 message interfaces for new code.
- `Slothsoft\Farah\PThreads` in `src-pthreads/`
  - Deprecated pthreads integration retained for old consumers.
  - New code should avoid `ext-pthreads` and use maintained process, queue, or async infrastructure instead.
- The `/sites` asset in `assets/manifest.xml`
  - Deprecated alias for `/current-sitemap`.

## Farah Modules

A Farah module is registered with a vendor/module authority and an asset directory:

```php
use Slothsoft\Farah\Module\Module;

Module::registerWithXmlManifestAndDefaultAssets(
    'slothsoft@farah',
    __DIR__ . '/assets'
);
```

The module manifest describes resources, directories, fragments, custom assets, imports, links, templates, parameter filters, and executable builders. Farah then resolves URLs through the pipeline:

```text
FarahUrl -> Manifest -> Asset -> Executable -> Result -> Writer/Response
```

Example URLs:

```text
farah://slothsoft@farah/schema/module/1.1#file
farah://slothsoft@farah/current-sitemap#xml
farah://slothsoft@farah/sitemap-generator#html
```

See `assets/manifest.xml`, `assets/example-domain.xml`, and `test-files/` for canonical examples of manifests, domains, dictionaries, transformations, and path resolution behavior.

## Command-Line Usage

Install dependencies first:

```bash
composer install
```

Retrieve an asset by Farah URL:

```bash
composer exec farah-asset "farah://vendor@module/path/to/asset?arguments#stream-type"
```

Retrieve a page by URL path:

```bash
composer exec farah-page "/path/to/page?arguments#stream-type"
```

## Development Notes

Some APIs use global or static process state, especially `Kernel`, `Module`, configuration fields, stream wrappers, session/tracking helpers, and request/page/sitemap state. Tests that exercise stateful behavior should isolate side effects with `@runInSeparateProcess`.

Tests may create temporary files through `temp_file`, `temp_dir`, or `Slothsoft\Core\IO\FileInfoFactory::createTempFile`; those helpers do not require manual cleanup. Files in `test-files/` are canonical fixtures.

The local development environment should provide the Composer development extensions. If an optional extension or platform feature is unavailable, affected tests may be skipped or impossible to run locally. Skipped tests should be treated as intentionally skipped unless the task is specifically about test skipping.

Use `.editorconfig` for coding style. Additional formatter or static-analysis tooling may be added later, but none is required right now.

Run tests with:

```bash
vendor/bin/phpunit
```

The full suite can take up to 300 seconds. Some API tests use browser mocking to validate the bundled JavaScript assets, including the first-run browser driver detection/download step.

Generate API documentation with:

```bash
vendor/bin/phpdoc
```

## Installation

```bash
composer require slothsoft/farah
```

## Requirements

See `composer.json` for required PHP extensions and optional development extensions.
