<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Support;

use finfo;

final class MimeDetector
{
    public static function detectFromPath(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        if (! class_exists(finfo::class)) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);

        return is_string($mime) && $mime !== '' ? $mime : null;
    }

    public static function detectFromContent(string $content): ?string
    {
        if (! class_exists(finfo::class)) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        return is_string($mime) && $mime !== '' ? $mime : null;
    }
}
