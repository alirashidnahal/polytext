# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-11-09

### Changed

- Raised `phpoffice/phpspreadsheet` to `^5.10` so Composer can install a release that is not blocked by Packagist security advisories on the 4.x line.
- Raised `phpoffice/phppresentation` to `dev-master` (aliased as `1.3.0`) because the latest stable 1.2.0 release still requires PhpSpreadsheet 4.x and cannot be installed together with 5.10+.
- Updated other production and development Composer constraints (`league/csv` `^9.27`, `phpoffice/phpword` `^1.4`, `smalot/pdfparser` `^2.13`, Pint, PHPStan) and refreshed `composer.lock`.
- Switched CSV read/write to `League\Csv` `fromString()` after `createFromString()` was deprecated in 9.27.

### Removed

- Support for PhpSpreadsheet 4.x as a required dependency of this library.

## [1.0.0] - 2026-07-10

### Added

- Core driver contract, domain exceptions, and driver registry.
- Polytext manager for reading, writing, and converting documents.
- Document model with text node collection.
- Abstract driver base with encoding normalization.
- File detection and I/O support utilities.
- Format drivers for plain text, JSON, CSV, YAML, HTML, XML, SRT, VTT, ASS, SUB, DOCX, XLSX, XLS, ODS, PPTX, and PDF.
- Optional Laravel bridge with service provider, facade, and config publishing.
- PHPUnit test suite with sample fixtures and round-trip coverage for core drivers.
- PHPStan static analysis and Laravel Pint code style tooling.
- GitHub Actions workflows for continuous integration, code style, and releases.
- Dependabot configuration for Composer and GitHub Actions updates.
- `VERSION` file for semantic versioning in the release workflow.
- `composer.lock` for reproducible dependency installs in CI.
- Project documentation: README, contribution guidelines, security policy, and Code of Conduct.

### Changed

- Release workflow now reads the package version from `VERSION` instead of `composer.json`.
- GitHub Actions workflows updated to their latest action versions.

### Removed

- `version` field from `composer.json` to follow Packagist publishing conventions.
- Outdated Packagist release checklist.
