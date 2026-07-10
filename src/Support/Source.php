<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Support;

use Alirashidnahal\Polytext\Exceptions\CorruptFileException;

final class Source
{
    private function __construct(
        public readonly ?string $path,
        public readonly ?string $content,
        /** @var resource|null */
        public readonly mixed $stream,
        public readonly ?FileInfo $fileInfo,
    ) {}

    public static function fromPath(string $path): self
    {
        if (! is_readable($path)) {
            throw new CorruptFileException("Source file is not readable: {$path}");
        }

        return new self(
            path: $path,
            content: null,
            stream: null,
            fileInfo: FileInfo::fromPath($path),
        );
    }

    public static function fromString(string $content, ?string $extension = null, ?string $mimeType = null): self
    {
        return new self(
            path: null,
            content: $content,
            stream: null,
            fileInfo: FileInfo::fromString(
                $content,
                $extension,
                $mimeType ?? MimeDetector::detectFromContent($content),
            ),
        );
    }

    /**
     * @param  resource  $stream
     */
    public static function fromStream(mixed $stream, ?string $extension = null, ?string $mimeType = null): self
    {
        if (! is_resource($stream)) {
            throw new CorruptFileException('Source stream must be a valid resource.');
        }

        return new self(
            path: null,
            content: null,
            stream: $stream,
            fileInfo: FileInfo::fromString('', $extension, $mimeType),
        );
    }

    public function readContents(): string
    {
        if ($this->content !== null) {
            return $this->content;
        }

        if ($this->path !== null) {
            $contents = file_get_contents($this->path);

            if ($contents === false) {
                throw new CorruptFileException("Unable to read source file: {$this->path}");
            }

            return $contents;
        }

        if ($this->stream !== null) {
            $contents = stream_get_contents($this->stream);

            if ($contents === false) {
                throw new CorruptFileException('Unable to read source stream.');
            }

            return $contents;
        }

        throw new CorruptFileException('Source has no readable payload.');
    }

    public function createTempPath(): string
    {
        if ($this->path !== null) {
            return $this->path;
        }

        $extension = $this->fileInfo->extension ?? 'tmp';
        $tempPath = tempnam(sys_get_temp_dir(), 'polytext_');

        if ($tempPath === false) {
            throw new CorruptFileException('Unable to create temporary file.');
        }

        $targetPath = $tempPath.'.'.$extension;
        rename($tempPath, $targetPath);

        file_put_contents($targetPath, $this->readContents());

        return $targetPath;
    }
}
