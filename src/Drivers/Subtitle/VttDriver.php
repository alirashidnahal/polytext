<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Subtitle;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;

final class VttDriver extends AbstractSubtitleDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'vtt',
            extensions: ['vtt'],
            mimeTypes: ['text/vtt'],
        );
    }

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
            state: [
                'blocks' => $parsed['blocks'],
                'header' => $parsed['header'],
            ],
        );
    }

    /**
     * @return array{blocks: list<array<string, mixed>>, line_ending: string, header: string}
     */
    protected function parseBlocks(string $content): array
    {
        $lineEnding = $this->detectLineEnding($content);
        $normalized = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $normalized);
        $blocks = [];
        $headerLines = [];
        $current = null;
        $inHeader = true;

        foreach ($lines as $line) {
            if ($inHeader) {
                $headerLines[] = $line;

                if (trim($line) === '' && count($headerLines) > 1) {
                    $inHeader = false;
                }

                continue;
            }

            if (trim($line) === '') {
                if ($current !== null) {
                    $current['text'] = implode("\n", $current['dialogue_lines']);
                    unset($current['dialogue_lines']);
                    $blocks[] = $current;
                    $current = null;
                }

                continue;
            }

            if ($current === null) {
                $current = ['identifier' => null, 'timing' => null, 'settings' => null, 'dialogue_lines' => []];

                if (! str_contains($line, '-->')) {
                    $current['identifier'] = $line;

                    continue;
                }
            }

            if ($current['timing'] === null && str_contains($line, '-->')) {
                [$timing, $settings] = array_pad(explode(' ', trim($line), 2), 2, null);
                $current['timing'] = $timing;
                $current['settings'] = $settings;

                continue;
            }

            if ($current['timing'] !== null) {
                $current['dialogue_lines'][] = $line;

                continue;
            }
        }

        if ($current !== null) {
            $current['text'] = implode("\n", $current['dialogue_lines']);
            unset($current['dialogue_lines']);
            $blocks[] = $current;
        }

        return [
            'blocks' => $blocks,
            'line_ending' => $lineEnding,
            'header' => implode("\n", $headerLines),
        ];
    }

    protected function serializeBlocks(array $blocks, string $lineEnding): string
    {
        return '';
    }

    public function write(Document $document, Target $target): void
    {
        /** @var list<array<string, mixed>> $blocks */
        $blocks = $document->state()['blocks'] ?? [];
        $lineEnding = (string) ($document->metadata()['line_ending'] ?? "\r\n");
        $header = (string) ($document->state()['header'] ?? 'WEBVTT');

        foreach ($document->nodes() as $node) {
            $index = (int) $node->contextValue('block_index', -1);

            if ($index >= 0 && isset($blocks[$index])) {
                $blocks[$index]['text'] = $node->text();
            }
        }

        $parts = [rtrim($header), ''];

        foreach ($blocks as $block) {
            if (! empty($block['identifier'])) {
                $parts[] = (string) $block['identifier'];
            }

            $timing = (string) ($block['timing'] ?? '');
            $settings = $block['settings'] ?? null;
            $parts[] = $settings ? "{$timing} {$settings}" : $timing;
            $parts[] = (string) ($block['text'] ?? '');
            $parts[] = '';
        }

        $target->write(rtrim(implode($lineEnding, $parts)).$lineEnding);
    }
}
