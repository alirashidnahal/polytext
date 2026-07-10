<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Text;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Drivers\Concerns\NormalizesEncoding;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;

final class PlainTextDriver extends AbstractDriver
{
    use NormalizesEncoding;

    public function __construct()
    {
        parent::__construct(
            driverName: 'plain-text',
            extensions: ['txt', 'text', 'log', 'md', 'markdown'],
            mimeTypes: ['text/plain', 'text/markdown'],
        );
    }

    public function read(Source $source): Document
    {
        $raw = $source->readContents();
        $content = $this->normalizeToUtf8($raw);
        $lineEnding = $this->detectLineEnding($content);
        $lines = preg_split('/\R/u', $content) ?: [];

        $nodes = new NodeCollection;

        foreach ($lines as $index => $line) {
            $nodes->add(new TextNode(
                id: "line:{$index}",
                text: $line,
                context: ['index' => $index, 'line_ending' => $lineEnding],
            ));
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromString($content, 'txt'),
            metadata: ['line_ending' => $lineEnding, 'line_count' => count($lines)],
            state: ['original' => $content],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $lineEnding = (string) ($document->metadata()['line_ending'] ?? "\n");
        $lines = [];

        foreach ($document->nodes() as $node) {
            $lines[] = $node->text();
        }

        $target->write(implode($lineEnding, $lines));
    }
}
