<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Drivers;

use Alirashidnahal\Polytext\Contracts\FormatDriver;
use Alirashidnahal\Polytext\Exceptions\MissingDependencyException;
use Alirashidnahal\Polytext\Support\FileInfo;

abstract class AbstractDriver implements FormatDriver
{
    /**
     * @param  list<non-empty-string>  $extensions
     * @param  list<non-empty-string>  $mimeTypes
     */
    public function __construct(
        protected readonly string $driverName,
        protected readonly array $extensions,
        protected readonly array $mimeTypes = [],
    ) {}

    public function name(): string
    {
        /** @var non-empty-string */
        return $this->driverName;
    }

    /**
     * @return list<non-empty-string>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return list<non-empty-string>
     */
    public function mimeTypes(): array
    {
        return $this->mimeTypes;
    }

    public function supports(FileInfo $file): bool
    {
        if ($file->extension !== null && in_array($file->extension, $this->extensions, true)) {
            return true;
        }

        if ($file->mimeType !== null && in_array($file->mimeType, $this->mimeTypes, true)) {
            return true;
        }

        return false;
    }

    protected function ensureClassExists(string $class, string $package): void
    {
        if (! class_exists($class)) {
            throw new MissingDependencyException(
                "Missing dependency [{$package}]. Install it with: composer require {$package}"
            );
        }
    }
}
