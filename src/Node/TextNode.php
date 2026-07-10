<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Node;

final class TextNode
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        private readonly string $id,
        private string $text,
        private readonly array $context = [],
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    public function contextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
}
