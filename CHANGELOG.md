# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

This history was reconstructed from release tags and the commits between them.

## [Unreleased]

### Fixed

- Generated phpinfo XHTML responses now use the `application/xhtml+xml` media type.

## [1.29.2] - 2026-09-04

### Fixed

- Manifest processing now caches absolute asset paths.
- Farah URLs are extracted correctly.

## [1.29.1] - 2026-09-04

### Fixed

- Manifest caches are invalidated when source timestamps change.
- The kernel returns HTTP 500 when page construction fails.

## [1.29.0] - 2026-09-04

### Added

- Page lookup can select HTML5 as the default response representation.

### Fixed

- HTTPS page requests and generated sitemap URLs are handled correctly.
- The DDEV setup works on ARM64 hosts.

## [1.28.4] - 2026-07-04

### Changed

- Public classes, return types, and API documentation were tightened and completed.

### Removed

- The obsolete LookupRouteStrategy and languageInfo code paths were retired.

## [1.28.3] - 2026-05-13

### Changed

- Project and test tooling were refreshed.

### Fixed

- XHTML output and compatibility cleanup issues were corrected.

## [1.28.2] - 2026-04-02

### Changed

- The minimum supported PHP version was adjusted from 8.3 to 8.2.
- Development dependencies and the PHPUnit baseline were updated.

## [1.28.1] - 2026-04-02

### Changed

- The minimum supported PHP version was raised to 8.3.

## [1.28.0] - 2026-04-02

### Added

- Compatibility updates for PHP 8.5.

### Changed

- The minimum supported PHP version was raised from 7.4 to 8.0.

## [1.27.1] - 2026-03-13

### Fixed

- Nullable declarations and the slothsoft/core dependency were corrected.

## [1.27.0] - 2026-03-12

### Added

- Compatibility with PHP 8.4.

### Fixed

- HTTPS handling was corrected.

## [1.26.32] - 2026-02-08

### Fixed

- Line-based file loading now uses FILE_IGNORE_NEW_LINES correctly.

## [1.26.31] - 2026-02-08

### Fixed

- A deprecated constant was replaced.

## [1.26.30] - 2026-02-08

### Added

- Default lookup behavior for missing values.

## [1.26.29] - 2026-01-25

### Fixed

- Empty parameter values now produce the intended exception.

## [1.26.28] - 2026-01-24

### Fixed

- SplFileInfo handling was corrected.

## [1.26.27] - 2026-01-24

### Fixed

- Request processing was corrected.

## [1.26.26] - 2026-01-20

### Added

- A font-face generator and deterministic sorting.

### Fixed

- FontFaceBuilder output was corrected.

## [1.26.25] - 2026-01-15

### Removed

- DOMWriterFileCacheByUrl was retired in favor of the dependency-aware cache.

## [1.26.24] - 2026-01-15

### Added

- DOMWriterFileCacheWithDependencies.

## [1.26.23] - 2026-01-14

### Fixed

- Sitemap builder caching was corrected.

## [1.26.22] - 2026-01-14

### Fixed

- DOM writer cache filenames are sanitized correctly.

## [1.26.21] - 2026-01-14

### Changed

- Chunk, DOM, file, stream, and string builders now use native writers.

### Fixed

- Sitemap JSON exports were corrected.

## [1.26.20] - 2026-01-14

### Added

- TransformationDOMWriterByUrls and DOMWriterFileCacheByUrl.

### Changed

- Farah URL caching was improved.

### Fixed

- Farah URL fragment access was corrected.

## [1.26.19] - 2026-01-09

### Changed

- HTTP coding was rewritten and chunked transfer encoding was disabled.

## [1.26.18] - 2026-01-06

### Changed

- Stream handling was updated to the newer slothsoft/core APIs.

## [1.26.17] - 2026-01-01

### Fixed

- Only bufferable streams are cached, including chunk-writer results.

## [1.26.16] - 2026-01-01

### Added

- Current-request manifest support.
- FarahUrl can optionally overwrite existing components.

## [1.26.15] - 2025-12-31

### Added

- A constructor for LookupPageStrategy.

### Changed

- Page lookup was moved from Domain into dedicated strategies.

### Fixed

- Page-node and document lookup edge cases were corrected.

## [1.26.14] - 2025-12-30

### Fixed

- ChunkWriterStreamBuilder no longer reads a result twice.

## [1.26.13] - 2025-12-28

### Fixed

- File, chunk, DOM, and file-info stream builders consistently use writers rather than contexts.

## [1.26.12] - 2025-12-18

### Fixed

- FarahUrlArguments correctly handles multidimensional properties.

## [1.26.11] - 2025-12-04

### Added

- Dictionary values can be looked up directly.

## [1.26.10] - 2025-11-30

### Fixed

- fragment-info children are generated correctly.

## [1.26.9] - 2025-11-30

### Added

- A uniqueness constraint to module manifests.

## [1.26.8] - 2025-11-29

### Added

- An initial Bootstrap helper.

### Fixed

- Linked ES modules are marked asynchronous correctly.

## [1.26.7] - 2025-11-24

### Fixed

- Dictionary keys are validated as XML NCNames.

## [1.26.6] - 2025-11-24

### Fixed

- XML schema-location generation was corrected.

## [1.26.5] - 2025-11-23

### Changed

- DOM XPath queries and iterator conversion were optimized.

### Fixed

- The sfs XPath namespace is resolved correctly.

## [1.26.4] - 2025-11-23

### Added

- The sfd:lookup dictionary extension.

## [1.26.3] - 2025-11-23

### Added

- Dictionary key sanitization through xsltSanitizeKey and sfd:sanitize-key.

## [1.26.2] - 2025-11-19

### Fixed

- Stray logging was removed.

## [1.26.1] - 2025-11-19

### Changed

- xsltLookupText now uses dictionary schema version 1.

## [1.26.0] - 2025-11-19

### Added

- Dictionary schema 0.2, dictionary schema version 1, and link-dictionary module instructions.

## [1.25.6] - 2025-11-16

### Added

- Conditional translation when an applicable dictionary exists.

### Fixed

- Dictionary namespace/language selection and exception URLs were corrected.

## [1.25.5] - 2025-11-15

### Fixed

- XSLT include processing was corrected.

## [1.25.4] - 2025-11-13

### Changed

- DOM and SVG template construction no longer relies on appendXML.

### Fixed

- Template namespace output was corrected.

## [1.25.3] - 2025-11-13

### Added

- The includes=embed transformation option.

### Fixed

- XSLT imports and includes are selected correctly.

## [1.25.2] - 2025-11-13

### Added

- Optional transformation decoration/caching and FarahUrlPath::withLastSegment.

### Changed

- Instruction caching moved to executable construction.

### Fixed

- Link extraction and root-only decoration were corrected.

## [1.25.1] - 2025-11-12

### Added

- XSLT templates can accept DOM Node values.

## [1.25.0] - 2025-11-12

### Added

- link-content and completed link-template module instructions.

### Fixed

- BlobDatabase insertions were corrected.

## [1.24.5] - 2025-11-04

### Added

- The sfx:base64-encode XSLT extension.

## [1.24.4] - 2025-11-04

### Added

- IndexedDatabase, ImageDatabase, and BlobDatabase JavaScript modules.

### Fixed

- Filesystem filename detection and CDATA handling were corrected.

## [1.24.3] - 2025-10-30

### Fixed

- UTF-8 content handling was corrected.

## [1.24.2] - 2025-10-30

### Added

- Resource-directory output can include directories.

## [1.24.1] - 2025-10-24

### Fixed

- xsltLookupText uses the correct default module.

## [1.24.0] - 2025-10-24

### Added

- HTML helpers, Farah URL helpers, DOM evaluation, XSLT imports, and asynchronous fragment transformation.

### Changed

- DOM and XSLT JavaScript helpers were reorganized as modules.

### Fixed

- HTML loading, fragment transformation, and asynchronous waiting were corrected.

## [1.23.0] - 2025-10-21

### Added

- Additional XML schema support.

### Changed

- Updated integration with slothsoft/core 1.14 and farah-testing.

## [1.22.2] - 2025-10-18

### Fixed

- Manifest caching was restored.

## [1.22.1] - 2025-10-18

### Added

- Translations are applied only for supported languages.

### Fixed

- XML file loading was corrected.

## [1.22.0] - 2025-10-17

### Added

- Detection of values that differ from their defaults.

## [1.21.29] - 2025-10-16

### Added

- Dictionary XSLT support with key, module, and language selection.

## [1.21.28] - 2025-10-05

### Fixed

- EmptyTransformationException handling was corrected for another transformation path.

## [1.21.27] - 2025-10-05

### Fixed

- EmptyTransformationException handling was corrected.

## [1.21.26] - 2025-10-05

### Fixed

- Filesystem stat warnings are suppressed and handled correctly.

## [1.21.25] - 2025-10-05

### Changed

- Asset-resolution error messages now provide more context.

## [1.21.24] - 2025-10-04

### Fixed

- Manifest cache versioning was corrected.

## [1.21.23] - 2025-10-04

### Added

- id, set-id, and set-href manifest attributes.

## [1.21.22] - 2025-10-03

### Fixed

- Referenced asset names are no longer imported unexpectedly.

## [1.21.21] - 2025-10-03

### Added

- Result::createRealUrl.

## [1.21.20] - 2025-10-03

### Fixed

- Use-instruction naming is normalized correctly.

## [1.21.19] - 2025-10-03

### Added

- Reference normalization and real-URL construction.

### Fixed

- createRealUrl call sites were corrected.

## [1.21.18] - 2025-10-01

### Fixed

- Argument normalization and cascading were corrected.

## [1.21.17] - 2025-09-29

### Added

- The domain-fragment asset.

### Changed

- Stronger internal typing.

## [1.21.16] - 2025-09-29

### Fixed

- Manifest state no longer bleeds between resolutions.

## [1.21.15] - 2025-09-28

### Fixed

- Optional manifest parameters are handled correctly.

## [1.21.14] - 2025-09-28

### Fixed

- HttpDownloadException is supported throughout download resolution.

## [1.21.13] - 2025-09-28

### Changed

- Result-builder typing was strengthened.

### Fixed

- Transformation and proxy result stream handling was corrected.

## [1.21.12] - 2025-09-28

### Fixed

- JSON text-file result handling was corrected.

## [1.21.11] - 2025-09-28

### Changed

- MIME type lookup now uses MimeTypeDictionary.

## [1.21.10] - 2025-09-28

### Added

- SchemaLocator and text-file handling for text and JSON media types.

## [1.21.9] - 2025-09-25

### Fixed

- Referenced URLs are resolved correctly.

## [1.21.8] - 2025-09-25

### Fixed

- Parameters remain available to use and link instructions.

## [1.21.7] - 2025-09-25

### Added

- MapResultBuilder and manifest asset-use support.

### Changed

- Use and link instruction parameter handling was reworked.

## [1.21.6] - 2025-09-24

### Added

- The sfx:id XSLT extension.

## [1.21.5] - 2025-09-23

### Fixed

- HTML link validation only runs for HTML results.

## [1.21.4] - 2025-09-23

### Fixed

- data-dict-replace failures now produce the intended exception.

## [1.21.3] - 2025-09-23

### Fixed

- Relative and Farah URL resolution was corrected.

## [1.21.2] - 2025-09-23

### Added

- Asset-link validation support.

### Changed

- Manifest tag handling was refactored.

## [1.21.1] - 2025-09-22

### Added

- FarahUrlPath::getSegments and FarahUrlPath::createFromSegments.

## [1.21.0] - 2025-09-22

### Added

- Manifest-directory assets, manifest access, and a dedicated FileNotFoundException.

## [1.20.1] - 2025-09-21

### Added

- The graph.xsl transformation asset.

### Changed

- Source formatting was normalized.

## [1.20.0] - 2025-09-20

### Changed

- Selected runtime paths were decoupled from obsolete slothsoft/core APIs.

### Fixed

- Missing-asset warnings were corrected.

## [1.19.5] - 2025-09-19

### Added

- Cache and current-page clearing APIs.

### Fixed

- Page-link validation now clears cached assets and page state correctly.

## [1.19.4] - 2025-09-19

### Added

- Embedded-link assertions.

## [1.19.3] - 2025-09-18

### Fixed

- mailto links are detected correctly.

## [1.19.2] - 2025-09-18

### Deprecated

- The /sites asset was restored as a deprecated alias.

## [1.19.1] - 2025-09-18

### Fixed

- Page-strategy tests and fixtures were corrected.

## [1.19.0] - 2025-09-18

### Added

- SitemapBuilder and current-page node accessors.

### Changed

- The current sitemap asset was renamed.

### Fixed

- Current-page node assignment was corrected.

## [1.18.3] - 2025-09-18

### Changed

- Deprecated pthreads code was moved to its own source tree.

### Fixed

- Pthreads namespaces, templates, and build paths were corrected.

## [1.18.2] - 2025-09-17

### Fixed

- Stream stat operations handle false results correctly.

## [1.18.1] - 2025-09-17

### Added

- XInclude processing.

### Fixed

- Asset loading now handles empty transformations and lookup exceptions.

## [1.18.0] - 2025-09-17

### Added

- DOMDocumentDOMWriter.

### Fixed

- The Farah stream wrapper implements file_exists correctly.

## [1.17.0] - 2025-09-13

### Added

- A reusable XML schema asset.

## [1.16.13] - 2025-09-13

### Fixed

- data-* attributes are handled correctly during link validation.

## [1.16.12] - 2025-09-13

### Fixed

- mailto link matching was corrected.

## [1.16.11] - 2025-09-13

### Fixed

- Required manifest fields are detected correctly.

## [1.16.10] - 2025-09-13

### Added

- Validation for more link types and fragment targets.

## [1.16.9] - 2025-09-13

### Added

- Link href validation.

### Changed

- Redundant link checks were removed.

## [1.16.8] - 2025-09-12

### Fixed

- mailto links and URL resolution are handled correctly.

## [1.16.7] - 2025-09-12

### Added

- Domain-aware test caching and clearer lookup diagnostics.

### Changed

- Lookup and page-link validation now share the cache implementation.

### Fixed

- HTTP status, cache-key, and URL decoding issues were corrected.

## [1.16.6] - 2025-09-04

### Added

- Module base-URL lookup and current-sitemap support.

### Changed

- The farah-asset CLI resolves relative references against the module base URL.

## [1.16.5] - 2025-09-04

### Added

- Include trace information to exception output.

## [1.16.4] - 2025-09-03

### Changed

- Project build paths and generated configuration were refreshed.

## [1.16.3] - 2025-09-01

### Changed

- Source formatting and lint cleanup.

## [1.16.2] - 2025-08-15

### Changed

- Public and internal method signatures were cleaned up.

## [1.16.1] - 2025-07-15

### Added

- A Jenkins build pipeline.

### Changed

- Development and generated project files were refreshed.

## [1.16.0] - 2025-07-05

### Added

- XML xsd:token support and fragment-info parameters.

### Changed

- Manifest info elements were cleaned up.

## [1.15.1] - 2025-07-05

### Fixed

- Use and link instructions accept parameters correctly.

## [1.15.0] - 2025-07-05

### Added

- FarahUrlArguments::withoutArgument, use-manifest instructions, and child-only manifest loading.

### Fixed

- Compatibility with ext-ds 1.7.

## [1.14.4] - 2025-07-04

### Changed

- Documentation builds now run only on the main branch.

### Fixed

- Pthreads polyfill and sockets requirements for the PHP 7.2 build.

## [1.5.3] - 2025-07-04

### Fixed

- The PHP 7.2 maintenance line now declares its sockets requirement.

## [1.5.2] - 2025-07-04

### Fixed

- The PHP 7.2 maintenance line uses the pthreads polyfill correctly.

## [1.14.3] - 2025-07-04

### Changed

- Generated package and project files were refreshed.

## [1.14.2] - 2025-06-23

### Changed

- Updated integration with slothsoft/core.

### Fixed

- Manifest caches use the real path of manifest.xml.

## [1.14.1] - 2025-06-22

### Fixed

- Inline asset detection was corrected.

## [1.14.0] - 2025-06-22

### Added

- Inline asset detection.

## [1.13.1] - 2025-03-22

### Fixed

- Fragment lookup accepts and returns the intended DOMNode types.

## [1.13.0] - 2025-01-15

### Added

- Page redirects can target external hosts.
- Manifest schema support for the 1.13 release line.

### Fixed

- Schema imports and dependency compatibility were corrected.

## [1.12.7] - 2024-10-30

### Fixed

- Cached values are unserialized safely.

## [1.12.6] - 2024-10-30

### Added

- Dedicated exceptions for newly distinguished failure cases.

## [1.12.5] - 2024-10-30

### Fixed

- Asset initialization and manifest/result caches were corrected.

## [1.12.4] - 2024-10-29

### Added

- PHP 8.1 compatibility.

### Removed

- Pthreads testing was removed from the main test matrix.

### Fixed

- Traversable handling, configuration fields, polyfills, and core constraints were corrected.

## [1.12.3] - 2024-10-05

### Changed

- Source lint cleanup.

### Fixed

- Package metadata and generated files were updated consistently.

## [1.12.2] - 2024-09-29

### Changed

- Generated package and project files were refreshed.

## [1.12.1] - 2024-09-28

### Changed

- The temporary index entry point was removed and the build pipeline was updated.

### Fixed

- CLI warnings were corrected.

## [1.12.0] - 2024-09-28

### Added

- An index entry point for installed deployments.

## [1.11.4] - 2024-09-24

### Fixed

- Tracking and request logging were corrected.

## [1.11.3] - 2024-09-23

### Changed

- Build files, lock data, and artifact-download automation were updated.

## [1.11.2] - 2024-04-01

### Changed

- Dependency lock data and generated API documentation were refreshed.

## [1.11.1] - 2024-04-01

### Changed

- Dependencies, PHPUnit configuration, and project metadata were refreshed.

## [1.11.0] - 2023-08-28

### Added

- PHP 8.2 compatibility and a compatibility alias.

### Changed

- UTF-8 defaults and dependencies were updated.

## [1.10.0] - 2023-04-02

### Fixed

- Dictionary loading no longer recurses indefinitely.

## [1.9.2] - 2023-02-17

### Changed

- Page coverage, dependencies, and project settings were expanded.

## [1.9.1] - 2022-08-27

### Fixed

- Farah URLs with empty array arguments are handled correctly.

## [1.9.0] - 2022-07-15

### Added

- An authority option to FarahUrl::createFromComponents.

### Changed

- Dependencies and API documentation were updated.

## [1.8.0] - 2022-07-11

### Changed

- The farah-asset and farah-page commands are distributed as executable Composer binaries.

### Fixed

- CLI working-directory, PHP binary, shebang, and error-output handling were corrected.

## [1.7.1] - 2022-07-10

### Fixed

- The configured cache duration is now actually applied.

## [1.7.0] - 2022-07-10

### Changed

- Default HTTP cache durations were reduced and centralized.
- The project license changed to MIT and dependencies were updated.

### Fixed

- CLI PHP-binary lookup and cache behavior were corrected.

## [1.6.1] - 2022-01-21

### Fixed

- Error-domain output conforms to the sitemap schema.

## [1.6.0] - 2022-01-21

### Changed

- The minimum supported PHP version was raised to 7.4.

### Security

- Updated Laminas Router to address GHSA-xx8f-qf9f-5fgw.

## [1.5.1] - 2022-01-21

### Changed

- The sockets extension is now an optional Composer suggestion.

## [1.5.0] - 2022-01-21

### Changed

- Composer dependencies, lock data, and documentation were updated.

### Fixed

- Asset tests and cache-key generation were corrected.

## [1.4.2] - 2022-01-05

### Fixed

- XML schema validation is less greedy.

## [1.4.1] - 2022-01-04

### Added

- PHP 8.0 support, OPTIONS request handling, sockets support, and Process-based command execution.

### Changed

- CI moved from Travis CI to GitHub Actions.

### Fixed

- Output buffers are closed correctly.

## [1.4.0] - 2021-01-03

### Added

- Farah Module Manifest 1.1, Farah Sitemap 1.1, sitemap file assets, and Composer CLI binaries.

### Changed

- Bundled assets were migrated to the versioned schemas.

### Removed

- The daemon CLI script was removed.

### Fixed

- Schema hierarchy and CLI integration were corrected.

## [1.3.1] - 2020-12-30

### Changed

- Obsolete response-length and response-input tracking fields and the legacy log file were removed.

### Fixed

- Content buffering was corrected.

## [1.3.0] - 2020-12-27

### Added

- An isBufferable option for chunk writers.

## [1.2.1] - 2020-12-27

### Fixed

- The farah-asset command works across supported environments.

## [1.2.0] - 2020-12-26

### Added

- The farah-asset and farah-page commands, response output, and an example domain.

## [1.1.3] - 2020-12-26

### Added

- PHP 7.3 and PHP 7.4 support and the farah-url command.

### Fixed

- Invalid HTML entity handling and CLI help output were corrected.

## [1.1.2] - 2020-12-26

### Fixed

- The sitemap template was corrected.

## [1.1.1] - 2020-12-25

### Fixed

- The sitemap template URL was corrected.

## [1.1.0] - 2020-12-25

### Added

- PHP 7.2 support, PHPUnit 8, PSR-4 autoloading, and Travis CI.

### Changed

- Zend Router was replaced with Laminas Router.

### Removed

- The allowed-host mechanism.

### Fixed

- Tracking log backup and key formatting were corrected.

## [1.0.2] - 2019-11-17

### Added

- Request server variables and lookup strategies to tracking data.

### Fixed

- Physical asset validation was tightened.

## [1.0.1] - 2018-10-22

### Added

- A dedicated Farah XSLT XML namespace.

### Changed

- XML result builders now honor Farah URLs and the legacy getAsset.php endpoint was removed.
- The project was relicensed under the WTFPL.

### Fixed

- Document resolution and string typing were corrected.

## [1.0.0] - 2018-08-10

### Added

- Initial stable release of the manifest-driven Farah asset, transformation, response, stream, sitemap, and CLI APIs.

[Unreleased]: https://github.com/Faulo/slothsoft-farah/compare/1.29.2...HEAD
[1.29.2]: https://github.com/Faulo/slothsoft-farah/compare/1.29.1...1.29.2
[1.29.1]: https://github.com/Faulo/slothsoft-farah/compare/1.29.0...1.29.1
[1.29.0]: https://github.com/Faulo/slothsoft-farah/compare/1.28.4...1.29.0
[1.28.4]: https://github.com/Faulo/slothsoft-farah/compare/1.28.3...1.28.4
[1.28.3]: https://github.com/Faulo/slothsoft-farah/compare/1.28.2...1.28.3
[1.28.2]: https://github.com/Faulo/slothsoft-farah/compare/1.28.1...1.28.2
[1.28.1]: https://github.com/Faulo/slothsoft-farah/compare/1.28.0...1.28.1
[1.28.0]: https://github.com/Faulo/slothsoft-farah/compare/1.27.1...1.28.0
[1.27.1]: https://github.com/Faulo/slothsoft-farah/compare/1.27.0...1.27.1
[1.27.0]: https://github.com/Faulo/slothsoft-farah/compare/1.26.32...1.27.0
[1.26.32]: https://github.com/Faulo/slothsoft-farah/compare/1.26.31...1.26.32
[1.26.31]: https://github.com/Faulo/slothsoft-farah/compare/1.26.30...1.26.31
[1.26.30]: https://github.com/Faulo/slothsoft-farah/compare/1.26.29...1.26.30
[1.26.29]: https://github.com/Faulo/slothsoft-farah/compare/1.26.28...1.26.29
[1.26.28]: https://github.com/Faulo/slothsoft-farah/compare/1.26.27...1.26.28
[1.26.27]: https://github.com/Faulo/slothsoft-farah/compare/1.26.26...1.26.27
[1.26.26]: https://github.com/Faulo/slothsoft-farah/compare/1.26.25...1.26.26
[1.26.25]: https://github.com/Faulo/slothsoft-farah/compare/1.26.24...1.26.25
[1.26.24]: https://github.com/Faulo/slothsoft-farah/compare/1.26.23...1.26.24
[1.26.23]: https://github.com/Faulo/slothsoft-farah/compare/1.26.22...1.26.23
[1.26.22]: https://github.com/Faulo/slothsoft-farah/compare/1.26.21...1.26.22
[1.26.21]: https://github.com/Faulo/slothsoft-farah/compare/1.26.20...1.26.21
[1.26.20]: https://github.com/Faulo/slothsoft-farah/compare/1.26.19...1.26.20
[1.26.19]: https://github.com/Faulo/slothsoft-farah/compare/1.26.18...1.26.19
[1.26.18]: https://github.com/Faulo/slothsoft-farah/compare/1.26.17...1.26.18
[1.26.17]: https://github.com/Faulo/slothsoft-farah/compare/1.26.16...1.26.17
[1.26.16]: https://github.com/Faulo/slothsoft-farah/compare/1.26.15...1.26.16
[1.26.15]: https://github.com/Faulo/slothsoft-farah/compare/1.26.14...1.26.15
[1.26.14]: https://github.com/Faulo/slothsoft-farah/compare/1.26.13...1.26.14
[1.26.13]: https://github.com/Faulo/slothsoft-farah/compare/1.26.12...1.26.13
[1.26.12]: https://github.com/Faulo/slothsoft-farah/compare/1.26.11...1.26.12
[1.26.11]: https://github.com/Faulo/slothsoft-farah/compare/1.26.10...1.26.11
[1.26.10]: https://github.com/Faulo/slothsoft-farah/compare/1.26.9...1.26.10
[1.26.9]: https://github.com/Faulo/slothsoft-farah/compare/1.26.8...1.26.9
[1.26.8]: https://github.com/Faulo/slothsoft-farah/compare/1.26.7...1.26.8
[1.26.7]: https://github.com/Faulo/slothsoft-farah/compare/1.26.6...1.26.7
[1.26.6]: https://github.com/Faulo/slothsoft-farah/compare/1.26.5...1.26.6
[1.26.5]: https://github.com/Faulo/slothsoft-farah/compare/1.26.4...1.26.5
[1.26.4]: https://github.com/Faulo/slothsoft-farah/compare/1.26.3...1.26.4
[1.26.3]: https://github.com/Faulo/slothsoft-farah/compare/1.26.2...1.26.3
[1.26.2]: https://github.com/Faulo/slothsoft-farah/compare/1.26.1...1.26.2
[1.26.1]: https://github.com/Faulo/slothsoft-farah/compare/1.26.0...1.26.1
[1.26.0]: https://github.com/Faulo/slothsoft-farah/compare/1.25.6...1.26.0
[1.25.6]: https://github.com/Faulo/slothsoft-farah/compare/1.25.5...1.25.6
[1.25.5]: https://github.com/Faulo/slothsoft-farah/compare/1.25.4...1.25.5
[1.25.4]: https://github.com/Faulo/slothsoft-farah/compare/1.25.3...1.25.4
[1.25.3]: https://github.com/Faulo/slothsoft-farah/compare/1.25.2...1.25.3
[1.25.2]: https://github.com/Faulo/slothsoft-farah/compare/1.25.1...1.25.2
[1.25.1]: https://github.com/Faulo/slothsoft-farah/compare/1.25.0...1.25.1
[1.25.0]: https://github.com/Faulo/slothsoft-farah/compare/1.24.5...1.25.0
[1.24.5]: https://github.com/Faulo/slothsoft-farah/compare/1.24.4...1.24.5
[1.24.4]: https://github.com/Faulo/slothsoft-farah/compare/1.24.3...1.24.4
[1.24.3]: https://github.com/Faulo/slothsoft-farah/compare/1.24.2...1.24.3
[1.24.2]: https://github.com/Faulo/slothsoft-farah/compare/1.24.1...1.24.2
[1.24.1]: https://github.com/Faulo/slothsoft-farah/compare/1.24.0...1.24.1
[1.24.0]: https://github.com/Faulo/slothsoft-farah/compare/1.23.0...1.24.0
[1.23.0]: https://github.com/Faulo/slothsoft-farah/compare/1.22.2...1.23.0
[1.22.2]: https://github.com/Faulo/slothsoft-farah/compare/1.22.1...1.22.2
[1.22.1]: https://github.com/Faulo/slothsoft-farah/compare/1.22.0...1.22.1
[1.22.0]: https://github.com/Faulo/slothsoft-farah/compare/1.21.29...1.22.0
[1.21.29]: https://github.com/Faulo/slothsoft-farah/compare/1.21.28...1.21.29
[1.21.28]: https://github.com/Faulo/slothsoft-farah/compare/1.21.27...1.21.28
[1.21.27]: https://github.com/Faulo/slothsoft-farah/compare/1.21.26...1.21.27
[1.21.26]: https://github.com/Faulo/slothsoft-farah/compare/1.21.25...1.21.26
[1.21.25]: https://github.com/Faulo/slothsoft-farah/compare/1.21.24...1.21.25
[1.21.24]: https://github.com/Faulo/slothsoft-farah/compare/1.21.23...1.21.24
[1.21.23]: https://github.com/Faulo/slothsoft-farah/compare/1.21.22...1.21.23
[1.21.22]: https://github.com/Faulo/slothsoft-farah/compare/1.21.21...1.21.22
[1.21.21]: https://github.com/Faulo/slothsoft-farah/compare/1.21.20...1.21.21
[1.21.20]: https://github.com/Faulo/slothsoft-farah/compare/1.21.19...1.21.20
[1.21.19]: https://github.com/Faulo/slothsoft-farah/compare/1.21.18...1.21.19
[1.21.18]: https://github.com/Faulo/slothsoft-farah/compare/1.21.17...1.21.18
[1.21.17]: https://github.com/Faulo/slothsoft-farah/compare/1.21.16...1.21.17
[1.21.16]: https://github.com/Faulo/slothsoft-farah/compare/1.21.15...1.21.16
[1.21.15]: https://github.com/Faulo/slothsoft-farah/compare/1.21.14...1.21.15
[1.21.14]: https://github.com/Faulo/slothsoft-farah/compare/1.21.13...1.21.14
[1.21.13]: https://github.com/Faulo/slothsoft-farah/compare/1.21.12...1.21.13
[1.21.12]: https://github.com/Faulo/slothsoft-farah/compare/1.21.11...1.21.12
[1.21.11]: https://github.com/Faulo/slothsoft-farah/compare/1.21.10...1.21.11
[1.21.10]: https://github.com/Faulo/slothsoft-farah/compare/1.21.9...1.21.10
[1.21.9]: https://github.com/Faulo/slothsoft-farah/compare/1.21.8...1.21.9
[1.21.8]: https://github.com/Faulo/slothsoft-farah/compare/1.21.7...1.21.8
[1.21.7]: https://github.com/Faulo/slothsoft-farah/compare/1.21.6...1.21.7
[1.21.6]: https://github.com/Faulo/slothsoft-farah/compare/1.21.5...1.21.6
[1.21.5]: https://github.com/Faulo/slothsoft-farah/compare/1.21.4...1.21.5
[1.21.4]: https://github.com/Faulo/slothsoft-farah/compare/1.21.3...1.21.4
[1.21.3]: https://github.com/Faulo/slothsoft-farah/compare/1.21.2...1.21.3
[1.21.2]: https://github.com/Faulo/slothsoft-farah/compare/1.21.1...1.21.2
[1.21.1]: https://github.com/Faulo/slothsoft-farah/compare/1.21.0...1.21.1
[1.21.0]: https://github.com/Faulo/slothsoft-farah/compare/1.20.1...1.21.0
[1.20.1]: https://github.com/Faulo/slothsoft-farah/compare/1.20.0...1.20.1
[1.20.0]: https://github.com/Faulo/slothsoft-farah/compare/1.19.5...1.20.0
[1.19.5]: https://github.com/Faulo/slothsoft-farah/compare/1.19.4...1.19.5
[1.19.4]: https://github.com/Faulo/slothsoft-farah/compare/1.19.3...1.19.4
[1.19.3]: https://github.com/Faulo/slothsoft-farah/compare/1.19.2...1.19.3
[1.19.2]: https://github.com/Faulo/slothsoft-farah/compare/1.19.1...1.19.2
[1.19.1]: https://github.com/Faulo/slothsoft-farah/compare/1.19.0...1.19.1
[1.19.0]: https://github.com/Faulo/slothsoft-farah/compare/1.18.3...1.19.0
[1.18.3]: https://github.com/Faulo/slothsoft-farah/compare/1.18.2...1.18.3
[1.18.2]: https://github.com/Faulo/slothsoft-farah/compare/1.18.1...1.18.2
[1.18.1]: https://github.com/Faulo/slothsoft-farah/compare/1.18.0...1.18.1
[1.18.0]: https://github.com/Faulo/slothsoft-farah/compare/1.17.0...1.18.0
[1.17.0]: https://github.com/Faulo/slothsoft-farah/compare/1.16.13...1.17.0
[1.16.13]: https://github.com/Faulo/slothsoft-farah/compare/1.16.12...1.16.13
[1.16.12]: https://github.com/Faulo/slothsoft-farah/compare/1.16.11...1.16.12
[1.16.11]: https://github.com/Faulo/slothsoft-farah/compare/1.16.10...1.16.11
[1.16.10]: https://github.com/Faulo/slothsoft-farah/compare/1.16.9...1.16.10
[1.16.9]: https://github.com/Faulo/slothsoft-farah/compare/1.16.8...1.16.9
[1.16.8]: https://github.com/Faulo/slothsoft-farah/compare/1.16.7...1.16.8
[1.16.7]: https://github.com/Faulo/slothsoft-farah/compare/1.16.6...1.16.7
[1.16.6]: https://github.com/Faulo/slothsoft-farah/compare/1.16.5...1.16.6
[1.16.5]: https://github.com/Faulo/slothsoft-farah/compare/1.16.4...1.16.5
[1.16.4]: https://github.com/Faulo/slothsoft-farah/compare/1.16.3...1.16.4
[1.16.3]: https://github.com/Faulo/slothsoft-farah/compare/1.16.2...1.16.3
[1.16.2]: https://github.com/Faulo/slothsoft-farah/compare/1.16.1...1.16.2
[1.16.1]: https://github.com/Faulo/slothsoft-farah/compare/1.16.0...1.16.1
[1.16.0]: https://github.com/Faulo/slothsoft-farah/compare/1.15.1...1.16.0
[1.15.1]: https://github.com/Faulo/slothsoft-farah/compare/1.15.0...1.15.1
[1.15.0]: https://github.com/Faulo/slothsoft-farah/compare/1.14.4...1.15.0
[1.14.4]: https://github.com/Faulo/slothsoft-farah/compare/1.14.3...1.14.4
[1.14.3]: https://github.com/Faulo/slothsoft-farah/compare/1.14.2...1.14.3
[1.14.2]: https://github.com/Faulo/slothsoft-farah/compare/1.14.1...1.14.2
[1.14.1]: https://github.com/Faulo/slothsoft-farah/compare/1.14.0...1.14.1
[1.14.0]: https://github.com/Faulo/slothsoft-farah/compare/1.13.1...1.14.0
[1.13.1]: https://github.com/Faulo/slothsoft-farah/compare/1.13.0...1.13.1
[1.13.0]: https://github.com/Faulo/slothsoft-farah/compare/1.12.7...1.13.0
[1.12.7]: https://github.com/Faulo/slothsoft-farah/compare/1.12.6...1.12.7
[1.12.6]: https://github.com/Faulo/slothsoft-farah/compare/1.12.5...1.12.6
[1.12.5]: https://github.com/Faulo/slothsoft-farah/compare/1.12.4...1.12.5
[1.12.4]: https://github.com/Faulo/slothsoft-farah/compare/1.12.3...1.12.4
[1.12.3]: https://github.com/Faulo/slothsoft-farah/compare/1.12.2...1.12.3
[1.12.2]: https://github.com/Faulo/slothsoft-farah/compare/1.12.1...1.12.2
[1.12.1]: https://github.com/Faulo/slothsoft-farah/compare/1.12.0...1.12.1
[1.12.0]: https://github.com/Faulo/slothsoft-farah/compare/1.11.4...1.12.0
[1.11.4]: https://github.com/Faulo/slothsoft-farah/compare/1.11.3...1.11.4
[1.11.3]: https://github.com/Faulo/slothsoft-farah/compare/1.11.2...1.11.3
[1.11.2]: https://github.com/Faulo/slothsoft-farah/compare/1.11.1...1.11.2
[1.11.1]: https://github.com/Faulo/slothsoft-farah/compare/1.11.0...1.11.1
[1.11.0]: https://github.com/Faulo/slothsoft-farah/compare/1.10.0...1.11.0
[1.10.0]: https://github.com/Faulo/slothsoft-farah/compare/1.9.2...1.10.0
[1.9.2]: https://github.com/Faulo/slothsoft-farah/compare/1.9.1...1.9.2
[1.9.1]: https://github.com/Faulo/slothsoft-farah/compare/1.9.0...1.9.1
[1.9.0]: https://github.com/Faulo/slothsoft-farah/compare/1.8.0...1.9.0
[1.8.0]: https://github.com/Faulo/slothsoft-farah/compare/1.7.1...1.8.0
[1.7.1]: https://github.com/Faulo/slothsoft-farah/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/Faulo/slothsoft-farah/compare/1.6.1...1.7.0
[1.6.1]: https://github.com/Faulo/slothsoft-farah/compare/1.6.0...1.6.1
[1.6.0]: https://github.com/Faulo/slothsoft-farah/compare/1.5.1...1.6.0
[1.5.3]: https://github.com/Faulo/slothsoft-farah/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/Faulo/slothsoft-farah/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/Faulo/slothsoft-farah/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/Faulo/slothsoft-farah/compare/1.4.2...1.5.0
[1.4.2]: https://github.com/Faulo/slothsoft-farah/compare/1.4.1...1.4.2
[1.4.1]: https://github.com/Faulo/slothsoft-farah/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/Faulo/slothsoft-farah/compare/1.3.1...1.4.0
[1.3.1]: https://github.com/Faulo/slothsoft-farah/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/Faulo/slothsoft-farah/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/Faulo/slothsoft-farah/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/Faulo/slothsoft-farah/compare/1.1.3...1.2.0
[1.1.3]: https://github.com/Faulo/slothsoft-farah/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/Faulo/slothsoft-farah/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/Faulo/slothsoft-farah/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/Faulo/slothsoft-farah/compare/1.0.2...1.1.0
[1.0.2]: https://github.com/Faulo/slothsoft-farah/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/Faulo/slothsoft-farah/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/Faulo/slothsoft-farah/releases/tag/1.0.0
