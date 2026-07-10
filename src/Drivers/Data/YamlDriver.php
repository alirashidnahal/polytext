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
use Symfony\Component\Yaml\Yaml;

final class YamlDriver extends AbstractDriver
{
    use NormalizesEncoding;

    public function __construct()
    {
        parent::__construct(
            driverName: 'yaml',
            extensions: ['yaml', 'yml'],
            mimeTypes: ['application/x-yaml', 'text/yaml'],
        );
    }

    public function read(Source $source): Document
    {
        $this->ensureClassExists(Yaml::class, 'symfony/yaml');

        $content = $this->normalizeToUtf8($source->readContents());

        try {
            $data = Yaml::parse($content);
        } catch (\Throwable $throwable) {
            throw new CorruptFileException('Invalid YAML document: '.$throwable->getMessage(), previous: $throwable);
        }

        if (! is_array($data)) {
            throw new CorruptFileException('YAML root must be a mapping or sequence.');
        }

        $nodes = new NodeCollection;
        $this->walk($data, '', $nodes);

        return new Document(
            driver: $this,
            nodes: $nodes,
            fileInfo: $source->fileInfo ?? FileInfo::fromString($content, 'yaml'),
            metadata: ['format' => 'yaml'],
            state: ['data' => $data],
        );
    }

    public function write(Document $document, Target $target): void
    {
        $this->ensureClassExists(Yaml::class, 'symfony/yaml');

        /** @var array<string, mixed> $data */
        $data = $document->state()['data'] ?? [];

        foreach ($document->nodes() as $node) {
            $path = (string) $node->contextValue('path', '');
            $this->setByPath($data, $path, $node->text());
        }

        $target->write(Yaml::dump($data, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }

    private function walk(mixed $data, string $prefix, NodeCollection $nodes): void
    {
        if (is_string($data)) {
            $nodes->add(new TextNode('path:'.$prefix, $data, ['path' => $prefix]));

            return;
        }

        if (! is_array($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $this->walk($value, $path, $nodes);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function setByPath(array &$data, string $path, string $value): void
    {
        if ($path === '') {
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
