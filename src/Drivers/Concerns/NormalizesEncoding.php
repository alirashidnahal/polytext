<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Concerns;

trait NormalizesEncoding
{
    protected function normalizeToUtf8(string $content): string
    {
        $encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding === false || $encoding === 'UTF-8') {
            return $content;
        }

        $converted = mb_convert_encoding($content, 'UTF-8', $encoding);

        return $converted !== false ? $converted : $content;
    }

    protected function detectLineEnding(string $content): string
    {
        if (str_contains($content, "\r\n")) {
            return "\r\n";
        }

        if (str_contains($content, "\r")) {
            return "\r";
        }

        return "\n";
    }
}
