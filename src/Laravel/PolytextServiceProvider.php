<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Laravel;

use Alirashidnahal\Polytext\Contracts\FormatDriver;
use Alirashidnahal\Polytext\Polytext;
use Illuminate\Support\ServiceProvider;

final class PolytextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/polytext.php', 'polytext');

        $this->app->singleton(Polytext::class, function (): Polytext {
            $polytext = new Polytext;

            foreach ((array) config('polytext.drivers', []) as $driverClass) {
                if (is_string($driverClass) && is_subclass_of($driverClass, FormatDriver::class)) {
                    /** @var FormatDriver $driver */
                    $driver = new $driverClass;
                    $polytext->registerDriver($driver);
                }
            }

            return $polytext;
        });

        $this->app->alias(Polytext::class, 'polytext');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/polytext.php' => config_path('polytext.php'),
            ], 'polytext-config');
        }
    }
}
