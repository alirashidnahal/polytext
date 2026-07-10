<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Subtitle;

final class SrtDriver extends AbstractSubtitleDriver
{
    public function __construct()
    {
        parent::__construct(
            driverName: 'srt',
            extensions: ['srt'],
            mimeTypes: ['application/x-subrip', 'text/plain'],
        );
    }

    protected function parseBlocks(string $content): array
    {
        $lineEnding = $this->detectLineEnding($content);
        $normalized = str_replace(["\r\n", "\r"], "\n", $content);
        $chunks = preg_split("/\n{2,}/", trim($normalized)) ?: [];
        $blocks = [];

        foreach ($chunks as $chunk) {
            $lines = explode("\n", trim($chunk));

            if (count($lines) < 3) {
                continue;
            }

            $index = array_shift($lines);
            $timing = array_shift($lines);

            $blocks[] = [
                'index' => $index,
                'timing' => $timing,
                'text' => implode("\n", $lines),
            ];
        }

        return ['blocks' => $blocks, 'line_ending' => $lineEnding];
    }

    protected function serializeBlocks(array $blocks, string $lineEnding): string
    {
        $parts = [];

        foreach ($blocks as $block) {
            $parts[] = implode($lineEnding, [
                (string) ($block['index'] ?? ''),
                (string) ($block['timing'] ?? ''),
                (string) ($block['text'] ?? ''),
            ]);
        }

        return implode($lineEnding.$lineEnding, $parts).$lineEnding;
    }
}
