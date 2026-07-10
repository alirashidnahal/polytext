# Polytext

Framework-agnostic PHP library for parsing, reading, and writing text across many file formats.

## Requirements

- PHP 8.3+
- Extensions: `dom`, `fileinfo`, `json`, `mbstring`, `zip`

## Installation

```bash
composer require alirashidnahal/polytext
```

## Quick Start

```php
use Alirashidnahal\Polytext\Polytext;

$polytext = new Polytext();
$document = $polytext->open('report.docx');

foreach ($document->nodes() as $node) {
    $node->setText(strtoupper($node->text()));
}

$document->save('report-modified.docx');
```

## Supported Formats

| Category | Extensions |
| --- | --- |
| Text | txt, text, log, md, markdown |
| Subtitles | srt, vtt, ass, ssa, sub |
| Web | html, htm, xml |
| Data | json, csv, yaml, yml |
| Office | docx, odt, rtf, xlsx, xls, ods, pptx, ppt |
| PDF | pdf |

## Laravel

Auto-discovery registers `PolytextServiceProvider` and the `Polytext` facade.

```php
use Alirashidnahal\Polytext\Laravel\Facades\Polytext;

$document = Polytext::open(storage_path('app/file.docx'));
```

Publish config:

```bash
php artisan vendor:publish --tag=polytext-config
```

## Development

```bash
composer install
composer test
composer analyse
composer format
```

## License

MIT © Ali Rashidnahal
