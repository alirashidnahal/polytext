<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Contracts;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Support\FileInfo;
use Alirashidnahal\Polytext\Support\Source;
use Alirashidnahal\Polytext\Support\Target;

interface FormatDriver
{
    /**
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * @return list<non-empty-string>
     */
    public function extensions(): array;

    /**
     * @return list<non-empty-string>
     */
    public function mimeTypes(): array;

    public function supports(FileInfo $file): bool;

    public function read(Source $source): Document;

    public function write(Document $document, Target $target): void;
}
