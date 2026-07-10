<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Subtitle;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Support\Target;

final class SubDriver extends AbstractSubtitleDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'sub',
            extensions: ['sub'],
            mimeTypes: ['text/plain'],
        );
    }

    protected function parseBlocks(string $content): array
    {
        $lineEnding = $this->detectLineEnding($content);
        $normalized = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $normalized);
        $blocks = [];

        foreach ($lines as $index => $line) {
            if (trim($line) === '') {
                continue;
            }

            if (preg_match('/^\{(\d+)\}\{(\d+)\}(.*)$/u', $line, $matches)) {
                $blocks[] = [
                    'format' => 'microdvd',
                    'start' => $matches[1],
                    'end' => $matches[2],
                    'text' => $matches[3],
                    'line_index' => $index,
                ];

                continue;
            }

            if (preg_match('/^\[(\d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                $blocks[] = [
                    'format' => 'subviewer',
                    'timestamp' => $matches[1],
                    'text' => trim(substr($line, strlen($matches[0]))),
                    'line_index' => $index,
                ];
            }
        }

        return ['blocks' => $blocks, 'line_ending' => $lineEnding, 'lines' => $lines];
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
        $lines = $document->state()['lines'] ?? [];

        foreach ($document->nodes() as $node) {
            $index = (int) $node->contextValue('block_index', -1);

            if ($index >= 0 && isset($blocks[$index])) {
                $blocks[$index]['text'] = $node->text();
            }
        }

        foreach ($blocks as $block) {
            $lineIndex = (int) $block['line_index'];

            if (($block['format'] ?? '') === 'microdvd') {
                $lines[$lineIndex] = '{'.$block['start'].'}{'.$block['end'].'}'.$block['text'];
            }

            if (($block['format'] ?? '') === 'subviewer') {
                $lines[$lineIndex] = '['.$block['timestamp'].'] '.$block['text'];
            }
        }

        $target->write(implode($lineEnding, $lines).$lineEnding);
    }
}
