<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Subtitle;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Support\Target;

final class AssDriver extends AbstractSubtitleDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'ass',
            extensions: ['ass', 'ssa'],
            mimeTypes: ['text/plain'],
        );
    }

    protected function parseBlocks(string $content): array
    {
        $lineEnding = $this->detectLineEnding($content);
        $normalized = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $normalized);
        $blocks = [];

        foreach ($lines as $lineNumber => $line) {
            if (! str_starts_with($line, 'Dialogue:')) {
                continue;
            }

            $parts = explode(',', $line, 10);
            $prefix = implode(',', array_slice($parts, 0, 9));
            $text = $parts[9] ?? '';

            $blocks[] = [
                'line_number' => $lineNumber,
                'prefix' => $prefix,
                'text' => $text,
            ];
        }

        return [
            'blocks' => $blocks,
            'line_ending' => $lineEnding,
            'lines' => $lines,
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
        $lines = $document->state()['lines'] ?? [];

        foreach ($document->nodes() as $node) {
            $index = (int) $node->contextValue('block_index', -1);

            if ($index >= 0 && isset($blocks[$index])) {
                $blocks[$index]['text'] = $node->text();
            }
        }

        foreach ($blocks as $block) {
            $lines[(int) $block['line_number']] = $block['prefix'].','.$block['text'];
        }

        $target->write(implode($lineEnding, $lines).$lineEnding);
    }
}
