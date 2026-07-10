<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Subtitle;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Drivers\Concerns\NormalizesEncoding;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;

abstract class AbstractSubtitleDriver extends AbstractDriver
{
    use NormalizesEncoding;

    /**
     * @return array{blocks: list<array<string, mixed>>, line_ending: string}
     */
    abstract protected function parseBlocks(string $content): array;

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    abstract protected function serializeBlocks(array $blocks, string $lineEnding): string;

    public function read(Source $source): Document
    {
        $raw = $source->readContents();
        $content = $this->normalizeToUtf8($raw);
        $parsed = $this->parseBlocks($content);
        $nodes = new NodeCollection;

        foreach ($parsed['blocks'] as $index => $block) {
            if (! isset($block['text']) || ! is_string($block['text'])) {
                continue;
            }

            $nodes->add(new TextNode(
                id: "cue:{$index}",
                text: $block['text'],
                context: ['block_index' => $index],
            ));
        }

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromString($content, $this->extensions[0]),
            metadata: ['line_ending' => $parsed['line_ending']],
            state: ['blocks' => $parsed['blocks']],
        );
    }

    public function write(Document $document, Target $target): void
    {
        /** @var list<array<string, mixed>> $blocks */
        $blocks = $document->state()['blocks'] ?? [];
        $lineEnding = (string) ($document->metadata()['line_ending'] ?? "\r\n");

        foreach ($document->nodes() as $node) {
            $index = (int) $node->contextValue('block_index', -1);

            if ($index >= 0 && isset($blocks[$index])) {
                $blocks[$index]['text'] = $node->text();
            }
        }

        $target->write($this->serializeBlocks($blocks, $lineEnding));
    }
}
