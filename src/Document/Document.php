<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Document;

use Alirashidnahal\Polytext\Contracts\FormatDriver;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Target;

final class Document
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $state
     */
    public function __construct(
        private readonly FormatDriver $driver,
        private NodeCollection $nodes,
        private readonly FileInfo $fileInfo,
        private array $metadata = [],
        private array $state = [],
    ) {}

    public function fileInfo(): FileInfo
    {
        return $this->fileInfo;
    }

    public function driver(): FormatDriver
    {
        return $this->driver;
    }

    public function nodes(): NodeCollection
    {
        return $this->nodes;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function state(): array
    {
        return $this->state;
    }

    public function setState(string $key, mixed $value): void
    {
        $this->state[$key] = $value;
    }

    public function text(): string
    {
        return $this->nodes->text();
    }

    public function mapText(callable $callback): self
    {
        foreach ($this->nodes as $node) {
            $result = $callback($node);

            if (is_string($result)) {
                $node->setText($result);
            }
        }

        return $this;
    }

    public function replaceText(string $search, string $replace): self
    {
        return $this->mapText(static function (TextNode $node) use ($search, $replace): string {
            return str_replace($search, $replace, $node->text());
        });
    }

    public function save(string $path): void
    {
        $this->driver->write($this, Target::fromPath($path));
    }

    public function toString(): string
    {
        $target = Target::toString();
        $this->driver->write($this, $target);

        return $target->contents();
    }

    /**
     * @return resource
     */
    public function toStream()
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new \RuntimeException('Unable to open temporary stream.');
        }

        $this->driver->write($this, Target::toStream($stream));
        rewind($stream);

        return $stream;
    }

    public function withNodes(NodeCollection $nodes): self
    {
        $clone = clone $this;
        $clone->nodes = $nodes;

        return $clone;
    }
}
