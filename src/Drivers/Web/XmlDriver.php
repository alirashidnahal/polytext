<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Web;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Drivers\Concerns\NormalizesEncoding;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;
use DOMDocument;

final class XmlDriver extends AbstractDriver
{
    use NormalizesEncoding;

    public function __construct()
    {
        parent::__construct(
            driverName: 'xml',
            extensions: ['xml'],
            mimeTypes: ['application/xml', 'text/xml'],
        );
    }

    public function read(Source $source): Document
    {
        $content = $this->normalizeToUtf8($source->readContents());

        return DomDocumentHelper::read(
            driver: $this,
            content: $content,
            fileInfo: $source->fileInfo ?? FileInfo::fromString($content, 'xml'),
            loader: static function (DOMDocument $dom, string $payload): bool {
                return $dom->loadXML($payload, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
            },
            skippedElements: [],
            serializer: static fn (DOMDocument $dom): string => $dom->saveXML() ?: '',
        );
    }

    public function write(Document $document, Target $target): void
    {
        DomDocumentHelper::write($document, $target, static fn (DOMDocument $dom): string => $dom->saveXML() ?: '');
    }
}
