<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers\Web;

use Alirashidnahal\Polytext\Contracts\FormatDriver;
use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Exceptions\CorruptFileException;
use Alirashidnahal\Polytext\Node\NodeCollection;
use Alirashidnahal\Polytext\Node\TextNode;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Target;
use DOMDocument;
use DOMNode;

final class DomDocumentHelper
{
    /**
     * @param  list<string>  $skippedElements
     * @param  callable(DOMDocument, string): bool  $loader
     * @param  callable(DOMDocument): string  $serializer
     */
    public static function read(
        FormatDriver $driver,
        string $content,
        FileInfo $fileInfo,
        callable $loader,
        array $skippedElements,
        callable $serializer,
    ): Document {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        $loaded = $loader($dom, $content);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($loaded === false) {
            throw new CorruptFileException('Unable to parse document.');
        }

        $nodes = new NodeCollection;
        $nodeMap = [];
        $index = 0;

        $walker = function (DOMNode $node) use (&$walker, $skippedElements, &$nodes, &$nodeMap, &$index): void {
            if ($node->nodeType === XML_TEXT_NODE) {
                $parent = $node->parentNode;

                if ($parent !== null && in_array(strtolower($parent->nodeName), $skippedElements, true)) {
                    return;
                }

                if (trim($node->nodeValue ?? '') !== '') {
                    $id = "node:{$index}";
                    $nodes->add(new TextNode($id, $node->nodeValue ?? '', ['dom_index' => $index]));
                    $nodeMap[$id] = $node;
                    $index++;
                }

                return;
            }

            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $walker($child);
                }
            }
        };

        if ($dom->documentElement !== null) {
            $walker($dom->documentElement);
        }

        return new Document(
            driver: $driver,
            nodes: $nodes,
            fileInfo: $fileInfo,
            metadata: ['format' => $driver->name()],
            state: ['dom' => $dom, 'node_map' => $nodeMap, 'serializer' => $serializer],
        );
    }

    /**
     * @param  callable(DOMDocument): string  $serializer
     */
    public static function write(Document $document, Target $target, callable $serializer): void
    {
        /** @var array<string, DOMNode> $nodeMap */
        $nodeMap = $document->state()['node_map'] ?? [];

        foreach ($document->nodes() as $node) {
            $domNode = $nodeMap[$node->id()] ?? null;

            if ($domNode !== null) {
                $domNode->nodeValue = $node->text();
            }
        }

        /** @var DOMDocument $dom */
        $dom = $document->state()['dom'];
        $target->write($serializer($dom));
    }
}
