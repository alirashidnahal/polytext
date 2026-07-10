<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Node;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, TextNode>
 */
final class NodeCollection implements Countable, IteratorAggregate
{
    /** @var array<int, TextNode> */
    private array $nodes = [];

    /**
     * @param  array<int, TextNode>  $nodes
     */
    public function __construct(array $nodes = [])
    {
        foreach ($nodes as $node) {
            $this->add($node);
        }
    }

    public function add(TextNode $node): void
    {
        $this->nodes[] = $node;
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * @return Traversable<int, TextNode>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->nodes);
    }

    /**
     * @return array<int, TextNode>
     */
    public function all(): array
    {
        return $this->nodes;
    }

    public function find(string $id): ?TextNode
    {
        foreach ($this->nodes as $node) {
            if ($node->id() === $id) {
                return $node;
            }
        }

        return null;
    }

    public function text(): string
    {
        return implode('', array_map(
            static fn (TextNode $node): string => $node->text(),
            $this->nodes,
        ));
    }
}
