<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Support;

use Alirashidnahal\Polytext\Exceptions\CorruptFileException;

final class Target
{
    private string $stringBuffer = '';

    private function __construct(
        public readonly ?string $path,
        /** @var resource|null */
        public readonly mixed $stream,
        private readonly bool $stringTarget,
    ) {}

    public static function fromPath(string $path): self
    {
        return new self(path: $path, stream: null, stringTarget: false);
    }

    public static function toString(): self
    {
        return new self(path: null, stream: null, stringTarget: true);
    }

    /**
     * @param  resource  $stream
     */
    public static function toStream(mixed $stream): self
    {
        if (! is_resource($stream)) {
            throw new CorruptFileException('Target stream must be a valid resource.');
        }

        return new self(path: null, stream: $stream, stringTarget: false);
    }

    public function write(string $contents): void
    {
        if ($this->stringTarget) {
            $this->stringBuffer = $contents;

            return;
        }

        if ($this->path !== null) {
            $directory = dirname($this->path);

            if ($directory !== '' && ! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if (file_put_contents($this->path, $contents) === false) {
                throw new CorruptFileException("Unable to write target file: {$this->path}");
            }

            return;
        }

        if ($this->stream !== null) {
            rewind($this->stream);
            fwrite($this->stream, $contents);

            return;
        }

        throw new CorruptFileException('Target is not writable.');
    }

    public function contents(): string
    {
        return $this->stringBuffer;
    }
}
