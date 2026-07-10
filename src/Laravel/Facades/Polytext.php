<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Laravel\Facades;

use Alirashidnahal\Polytext\Document\Document;
use Alirashidnahal\Polytext\Polytext as PolytextManager;
use Alirashidnahal\Polytext\Support\Source;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Document open(string|Source $source, ?string $driverName = null)
 * @method static bool supports(string $path)
 * @method static void registerDriver(\Alirashidnahal\Polytext\Contracts\FormatDriver $driver)
 *
 * @see PolytextManager
 */
final class Polytext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'polytext';
    }
}
