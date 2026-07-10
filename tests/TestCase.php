<?php

declare(strict_types=1);

namespace Alirashidnahal\Polytext\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function fixturePath(string $filename): string
    {
        return __DIR__.'/Fixtures/'.$filename;
    }

    protected function tempFilePath(string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'polytext_test_');

        if ($path === false) {
            self::fail('Unable to create temporary file.');
        }

        $target = $path.'.'.$extension;
        rename($path, $target);

        return $target;
    }
}
