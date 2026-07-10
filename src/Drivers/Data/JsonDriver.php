<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Data;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Drivers\AbstractDriver;
use Alirashidnahal\Polytext\Drivers\Concerns\NormalizesEncoding;
use Alirashidnahal\Polytext\Exceptions\CorruptFileException;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;

final class JsonDriver extends AbstractDriver
{
    use NormalizesEncoding;

    public function __construct()
    {
        parent::__construct(
            driverName: 'json',
            extensions: ['json'],
            mimeTypes: ['application/json'],
        );
    }

    public function read(Source $source): Document
    {
        $content = $this->normalizeToUtf8($source->readContents());
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new CorruptFileException('Invalid JSON document.');
        }

        $paths = [];
        $nodes = new NodeCollection;
        $this->walk($decoded, '', $nodes, $paths);

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromString($content, 'json'),
            metadata: ['format' => 'json'],
            state: ['data' => $decoded, 'paths' => $paths],
        );
    }

    public function write(Document $document, Target $target): void
    {
        /** @var array<string, mixed> $data */
        $data = $document->state()['data'] ?? [];

        foreach ($document->nodes() as $node) {
            $path = (string) $node->contextValue('path', '');
            $this->setByPath($data, $path, $node->text());
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new CorruptFileException('Unable to encode JSON document.');
        }

        $target->write($encoded);
    }

    /**
     * @param  array<string, string>  $paths
     */
    private function walk(mixed $data, string $prefix, NodeCollection $nodes, array &$paths): void
    {
        if (is_string($data)) {
            $id = 'path:'.($prefix === '' ? 'root' : $prefix);
            $nodes->add(new TextNode($id, $data, ['path' => $prefix]));
            $paths[$id] = $prefix;

            return;
        }

        if (! is_array($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $this->walk($value, $path, $nodes, $paths);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function setByPath(array &$data, string $path, string $value): void
    {
        if ($path === '' || $path === 'root') {
            return;
        }

        $segments = explode('.', $path);
        $current = &$data;

        foreach ($segments as $index => $segment) {
            if ($index === count($segments) - 1) {
                $current[$segment] = $value;
            } else {
                if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                    return;
                }

                $current = &$current[$segment];
            }
        }
    }
}
