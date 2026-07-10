<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Support;

final class FileInfo
{
    /**
     * @param  list<non-empty-string>  $extensions
     * @param  list<non-empty-string>  $mimeTypes
     */
    public function __construct(
        public readonly ?string $path = null,
        public readonly ?string $extension = null,
        public readonly ?string $mimeType = null,
        public readonly array $extensions = [],
        public readonly array $mimeTypes = [],
    ) {}

    public static function fromPath(string $path): self
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return new self(
            path: $path,
            extension: $extension !== '' ? $extension : null,
            mimeType: MimeDetector::detectFromPath($path),
        );
    }

    public static function fromString(string $content, ?string $extension = null, ?string $mimeType = null): self
    {
        return new self(
            extension: $extension !== null && $extension !== '' ? strtolower($extension) : null,
            mimeType: $mimeType,
        );
    }
}
